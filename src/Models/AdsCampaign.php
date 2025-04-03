<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsCampaign extends Model
{
    protected $table = 'ads_campaigns';
    public $timestamps = false;
    public $incrementing = false;
    public $fillable = [
        'id',
        'name',
    ];

    public static function updateMultiple(array $collection): bool
    {
        return self::upsert($collection, ['id'], ['name']);
    }
    public static function deleteCollection(int|array $id): bool
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        return self::destroy($id);
    }


    public static function findOne(int $id): Model|null
    {
        return self::query()->find($id);
    }

    public static function createCompany(array $data): Model
    {
        return self::create($data);
    }

    public static function updateCompany(array $data): bool
    {
        return self::updateMultiple($data);
    }
}