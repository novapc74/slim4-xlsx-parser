<?php

namespace App\Service;

use Exception;
use App\Models\Ads;
use App\Dto\XlsxDto;
use App\Models\AdsAdset;
use App\Models\AdsCampaign;
use Psr\Log\LoggerInterface;
use App\Request\XlsxValidator;
use Illuminate\Database\Eloquent\Model;

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