<?php

namespace App\Request;

use DateTimeImmutable;

class XlsxValidator
{
    public static function init(): self
    {
        return new self();
    }

    public function validate(array $row): array
    {
        $error = [];

        $data = $row['Дата'] ?? null;  //ожидается объект даты DateTime

        $isDateTimeObject = $data instanceof DateTimeImmutable;

        if (!$isDateTimeObject) {
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
}