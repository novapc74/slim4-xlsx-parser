<?php

namespace App\Dto;

use Exception;

class XlsxDto
{
    public function __construct(private array $xlsxRow)
    {
    }

    public static function init(array $xlsxRow): self
    {
        return new self($xlsxRow);
    }

    /**
     * @throws Exception
     */
    public function toArray(): array
    {
        return [
            'advertisements' => self::getAdvertisements(),
            'company' => self::getCompany(),
            'group' => self::getGroup(),
        ];
    }

    /**
     * @throws Exception
     */
    public function getAdvertisements(): array
    {
        $row = $this->xlsxRow;

        return [
            'id' => (int)$row['ID объявления'],
            'updated_at' => $row['Дата']->format('Y-m-d'), // DatetimeImmutable
            'credit' => round($row['Расходы'], 2),
            'name' => $row['Название объявления'],
            'display_count' => $row['Показы'],
            'click_count' => $row['Клики'],
            'company_id' => self::getCompany()['id'],
            'group_id' => self::getGroup()['id'],
        ];
    }

    public function getCompany(): array
    {
        $row = $this->xlsxRow;

        return [
            'id' => (int)$row['ID кампании'],
            'name' => $row['Название кампании'],
        ];
    }

    public function getGroup(): array
    {
        $row = $this->xlsxRow;

        return [
            'id' => (int)$row['ID группы'],
            'name' => $row['Название группы'],
        ];
    }
}