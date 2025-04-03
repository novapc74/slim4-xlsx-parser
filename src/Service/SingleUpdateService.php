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
        $this->createOrUpdateEntity($data, AdsCampaign::class, 'getCompany', 'findOne', 'updateCompany', 'createCompany');
    }

    private function createOrUpdateGroup(XlsxDto $data): void
    {
        $this->createOrUpdateEntity($data, AdsAdset::class, 'getGroup', 'findOne', 'updateGroup', 'createGroup');
    }

    private function createOrUpdateEntity(XlsxDto $data, string $modelClass, string $dataMethod, string $findMethod, string $updateMethod, string $createMethod): void
    {
        $entityData = $data->$dataMethod();

        if ($entity = $modelClass::$findMethod($entityData['id'])) {
            $modelClass::$updateMethod($entityData);
            $this->logger->info(sprintf('Обновили %s с ID: %s', strtolower(class_basename($modelClass)), $entity->id));
            return;
        }

        $newEntity = $modelClass::$createMethod($entityData);
        $this->logger->info(sprintf('Создали %s с ID: %s', strtolower(class_basename($modelClass)), $newEntity->id));
    }
}