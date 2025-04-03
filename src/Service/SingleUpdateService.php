<?php

namespace App\Service;

use Exception;
use App\Models\Ads;
use App\Dto\XlsxDto;
use App\Models\AdsAdset;
use App\Models\AdsCampaign;
use Psr\Log\LoggerInterface;
use App\Request\XlsxValidator;

class SingleUpdateService
{
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
        foreach ($this->rows as $row) {

            $dataDto = XlsxDto::init($row);
            $errors = XlsxValidator::init()->validate($row);

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

    /**
     * @throws Exception
     */
    private function createOrUpdateAds(XlsxDto $data): void
    {
        $adsData = $data->getAdvertisements();

        self::createOrUpdateCompany($data);
        self::createOrUpdateGroup($data);

        if ($ads = Ads::findOneBy(['id' => $adsData['id'], 'updated_at' => $adsData['updated_at']])) {
            Ads::updateAds($adsData);

            $this->logger->info('Обновили объявление с ID ' . $ads->id);
            return;
        }

        Ads::updateMultiple($adsData);

        $this->logger->info('Создали объявление с ID ' . $adsData['id']);
    }

    private function createOrUpdateCompany(XlsxDto $data): void
    {
        $companyData = $data->getCompany();
        if ($company = AdsCampaign::findOne($companyData['id'])) {

            AdsCampaign::updateCompany($companyData);
            $this->logger->info(sprintf('Обновили компанию с ID: %s', $company->id));

            return;
        }

        $newCompany = AdsCampaign::createCompany($companyData);

        $this->logger->info(sprintf('Создали компанию с ID: %s', $newCompany->id));
    }


    private function createOrUpdateGroup(XlsxDto $data): void
    {
        $groupData = $data->getGroup();
        if ($group = AdsAdset::findOne($groupData['id'])) {

            /* для меня логичнее так: $group->update($groupData) */
            AdsAdset::updateGroup($groupData);
            $this->logger->info(sprintf('Обновили группу с ID: %s', $group->id));

            return;
        }

        $newGroup = AdsAdset::createGroup($groupData);

        $this->logger->info(sprintf('Создали группу с ID: %s', $newGroup->id));
    }
}