<?php

namespace App\Console;

use App\Service\ClearOldData;
use Exception;
use App\Models\Ads;
use Psr\Log\LoggerInterface;
use App\Service\XlsFileManager;
use App\Service\SingleUpdateService;
use App\Service\MultiplyUpdateService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCsvAds extends Command
{
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

        self::showCommandTitle($io, $mode);

        $rows = XlsFileManager::init($this->logger)->getRows();

        if (!empty($rows['error'])) {
            $io->error('Ошибка загрузки файла.');
            return Command::FAILURE;
        }

        $success = $mode === 'true'
            ? MultiplyUpdateService::init($this->logger, $rows)->execute()
            : SingleUpdateService::init($this->logger, $rows)->execute();

        ClearOldData::init($this->logger, $rows)->clearOldData();

        return $success
            ? ($io->success('Похоже на то, что все OK!') && Command::SUCCESS)
            : ($io->error('Самое время посмотреть логи!') && Command::FAILURE);
    }


    public static function showCommandTitle(SymfonyStyle $io, string $mode): void
    {
        $type = $mode === 'true'
            ? 'Множественная вставка'
            : 'Последовательная вставка';

        $io->title(sprintf('%s', $type));
    }
}