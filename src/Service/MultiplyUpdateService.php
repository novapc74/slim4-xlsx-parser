<?php

namespace App\Service;

use Exception;
use App\Models\Ads;
use App\Dto\XlsxDto;
use App\Models\AdsAdset;
use App\Models\AdsCampaign;
use Psr\Log\LoggerInterface;
use App\Request\XlsxValidator;

class MultiplyUpdateService
{
    private ?array $error = null;

    public function __construct(private LoggerInterface $logger, private array $rows)
    {
    }

    public static function init(LoggerInterface $logger, array $rows): self
    {
        return new self($logger, $rows);
    }

    /**
     * @throws Exception
     */
    public function execute(): bool
    {
        $data = [];

        foreach ($this->rows as $row) {

            $errors = XlsxValidator::init()->validate($row);

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
            $this->logger->info('Обновляем базу, согласно таблице.');

            AdsCampaign::updateMultiple($data['companies']);
            AdsAdset::updateMultiple($data['groups']);
            Ads::updateMultiple($data['ads']);

        } catch (Exception $exception) {
            $this->logger->error(sprintf('Ошибка множественной вставки/обновления: %s', $exception->getMessage()));
            return false;
        }

        return true;
    }

}