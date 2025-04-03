<?php

namespace App\Service;

use App\Models\Ads;
use App\Models\AdsAdset;
use App\Models\AdsCampaign;
use Psr\Log\LoggerInterface;

/**
 * Удаляет данные с базы данных, которых нет во входящих данных (в таблице)
 */
class ClearOldData
{
    public function __construct(private LoggerInterface $logger, private array $rows)
    {
    }

    public static function init(LoggerInterface $logger, array $rows): self
    {
        return new self($logger, $rows);
    }

    /**
     * Удаляет объявления, которых нет в таблице
     */
    public function clearOldData(): void
    {
        $data = [];
        foreach ($this->rows as $row) {
            $data['ads_id'][] = $row['ID объявления'];
            $data['company_id'][] = $row['ID кампании'];
            $data['group_id'][] = $row['ID группы'];
        }

        foreach ($data as &$item) {
            $item = array_unique($item);
        }

        $this->removeAds($data);
        $this->removeCompanies($data);
        $this->removeGroups($data);
    }

    private function removeEntities($data, $model, $checkColumn): void
    {
        $ids = $model::pluck('id')->toArray();
        $idsDelete = array_diff($ids, $data['ads_id']);

        $noDelete = [];
        foreach ($idsDelete as $id) {
            if (Ads::where($checkColumn, $id)->exists()) {
                $noDelete[] = $id;
            }
        }

        $idsDelete = array_diff($idsDelete, $noDelete);

        if (!empty($idsDelete)) {
            $this->logger->info('Чистим ' . strtolower(class_basename($model)));
            $model::deleteCollection($idsDelete);
        }
    }

    private function removeGroups($data): void
    {
        $this->removeEntities($data, AdsAdset::class, 'group_id');
    }

    private function removeCompanies($data): void
    {
        $this->removeEntities($data, AdsCampaign::class, 'company_id');
    }

    private function removeAds($data): void
    {
        $adsIds = Ads::pluck('id')->toArray();
        $adsDeleteIds = array_diff($adsIds, $data['ads_id']);

        if ([] !== $adsDeleteIds) {
            $this->logger->info('Чистим Объявдения');
            Ads::deleteAds($adsDeleteIds);
        }
    }
}