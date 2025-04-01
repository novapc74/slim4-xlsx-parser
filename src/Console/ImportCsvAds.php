<?php

namespace App\Console;

use Exception;
use App\Models\Ads;
use App\Dto\XlsxDto;
use GuzzleHttp\Client;
use App\Models\AdsAdset;
use App\Models\AdsCampaign;
use Psr\Log\LoggerInterface;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Exception\GuzzleException;
use Spatie\SimpleExcel\SimpleExcelReader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCsvAds extends Command
{
    private const TEMP_FILE_NAME = __DIR__ . '/../../var/temp.xlsx';
    private const URI = 'http://172.29.0.1:8080/api/xlsx-file';

    public function __construct(private LoggerInterface $logger)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        parent::configure();

        $this
            ->setName('import-csv-ads')
            ->setDescription('import-csv-ads command')
            ->addArgument('fileProcessingMode', InputArgument::OPTIONAL, 'Метод обновления моделей.');
    }

    /**
     * @throws GuzzleException
     * @throws Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $mode = $input->getArgument('fileProcessingMode') ?? 'false';

        if (!in_array($mode, ['true', 'false'])) {
            $io->error('Допустимый параметр true/false? передан: ' . $mode);
            return Command::FAILURE;
        }

        $isMultiplyMode = $mode === 'true';

        $type = $isMultiplyMode
            ? 'Множественная вставка'
            : 'Последовательная вставка';

        $io->title(sprintf('%s', $type));

        $client = new Client();
        $response = $client->request('GET', self::URI);

        $responseCode = $response->getStatusCode();
        if (200 !== $responseCode) {
            $io->error(sprintf('Не удалось получить данные. Код ответа сервера: %s', $responseCode));

            return Command::FAILURE;
        }

        self::saveTempFile($response);

        $success = $isMultiplyMode
            ? self::multiplyUpdate($io)
            : self::updateData();

        if ($success) {

            $io->success('Похоже на то, что все OK!');
            return Command::SUCCESS;
        }

        $io->error('Самое время посмотреть логи!');

        return Command::FAILURE;


    }

    private static function saveTempFile($response): void
    {
        $fileContent = $response->getBody()->getContents();

        file_put_contents(self::TEMP_FILE_NAME, $fileContent);
    }

    private function validateRow(array $row): array
    {
        $error = [];

        $data = $row['Дата'] ?? null;  //ожидается объект даты DateTime
        if ($data instanceof \DateTimeImmutable) {

        } else {
            $error[] = 'Дата не в формате DateTimeImmutable';
        }

        if (!is_numeric($row['Расходы'])) {
            $error[] = 'Расходы не в формате числа';
        }

        if (!is_numeric($row['ID объявления'])) {
            $error[] = 'ID объявления не в формате числа';
        }

        if (!is_string($row['Название объявления']) && strlen($row['Название объявления']) >= 255) {
            $error[] = 'Название объявления не в формате строки';
        }

        if (!is_numeric($row['ID кампании'])) {
            $error[] = 'ID кампании не в формате числа';
        }

        if (!is_string($row['Название кампании']) && strlen($row['Название кампании']) >= 255) {
            $error[] = 'Название кампании не в формате строки';
        }

        if ($row['ID группы'] ?? null) {
            if (!is_numeric($row['ID группы'])) {
                $error[] = 'ID группы не в формате числа';
            }
        }

        if (!is_string($row['Название группы']) && strlen($row['Название группы']) >= 255) {
            $error[] = 'Название группы не в формате строки';
        }

        if ($row['Показы'] ?? null) {
            if (!is_numeric($row['Показы'])) {
                $error[] = 'Показы не в формате числа';
            }
        }

        if ($row['Клики'] ?? null) {
            if (!is_numeric($row['Клики'])) {
                $error[] = 'Клики не в формате числа';
            }
        }

        return $error;
    }

    /**
     * @throws Exception
     */
    private function multiplyUpdate($io): bool
    {
        $data = [];

        foreach (self::getRows() as $row) {

            $errors = self::validateRow($row);

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger->error($error);
                }
                return false;
            }

            $dto = XlsxDto::init($row);
            $data['ads'][$row['ID объявления']] = $dto->getAdvertisements();
            $data['companies'][$row['ID кампании']] = $dto->getCompany();
            $data['groups'][$row['ID группы']] = $dto->getGroup();
        }

        try {
            $this->logger->info('Находим ID всех сущностей в таблице');

            $adsId = array_map(fn($ads) => $ads['id'], $data['ads']);
            $companyId = array_map(fn($company) => $company['id'], $data['companies']);
            $groupId = array_map(fn($group) => $group['id'], $data['groups']);

            $this->logger->info('Удаляем все сущности, с найденными ID');

            Ads::destroy($adsId);
            AdsCampaign::destroy($companyId);
            AdsAdset::destroy($groupId);

            $this->logger->info('Обновляем базу, согласно таблице.');

            AdsCampaign::createMultiple($data['companies']);
            AdsAdset::createMultiple($data['groups']);
            Ads::createMultiple($data['ads']);

        } catch (Exception $exception) {
            $this->logger->error(sprintf('Ошибка множественной вставки/обновления: %s', $exception->getMessage()));
            $io->note($exception->getMessage());
            return false;
        }

        return true;
    }

    /**
     * @throws Exception
     */
    private function updateData(): bool
    {
        foreach (self::getRows() as $row) {
            $dataDto = XlsxDto::init($row);

            $errors = self::validateRow($row);

            if (!empty($errors)) {
                foreach ($errors as $error) {
                    $this->logger->error($error);
                }
                return false;
            }

            self::createOrUpdateAds($dataDto);
        }

        return true;
    }

    private static function getRows(): array
    {
        $rows = SimpleExcelReader::create(self::TEMP_FILE_NAME)
            ->getRows()
            ->toArray();

        unlink(self::TEMP_FILE_NAME);

        return $rows;
    }

    /**
     * @throws Exception
     */
    private function createOrUpdateAds(XlsxDto $data): void
    {
        $adsData = $data->getAdvertisements();

        $company = self::getRelation(AdsCampaign::class, $data->getCompany());
        $group = self::getRelation(AdsAdset::class, $data->getGroup());

        if ($ads = Ads::findOne($adsData['id'])) {
            $ads->updateAd($adsData);
            $ads->setCompany($company);
            $ads->setGroup($group);

            $ads->save();

            $this->logger->info('Обновили объявление с ID ' . $ads->id);
            return;
        }

        $ads = Ads::createAd($adsData);
        $ads->setCompany($company);
        $ads->setGroup($group);

        $ads->save();

        $this->logger->info('Создали объявление с ID ' . $ads->id);
    }


    private function getRelation(string $class, array $data): Model
    {
        if (!$relation = $class::findOne($data['id'])) {
            $this->logger->info(sprintf('Создаем %s с ID: %s', $class, $data['id']));

            return $class::create($data);
        }

        $relation->update($data);

        $this->logger->info(sprintf('Обновили %s с ID: %s', $class, $relation->id));
        return $relation;
    }
}