<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdsAdset extends Model
{
    protected $table = 'ads_ad_sets';
    public $timestamps = false;
    public $incrementing = false;
    public $fillable = [
        'id',
        'name',
    ];


    public static function createGroup(array $data): Model
    {
        return self::create($data);
    }

    public static function deleteCollection(int|array $id): bool
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        return self::destroy($id);
    }

    public static function updateMultiple(array $collection): bool
    {
        return self::upsert($collection, ['id'], ['name']);
    }

    public static function findOne(int $id): Model|null
    {
        return self::query()->find($id);
    }

    public static function updateGroup(array $data): bool
    {
        return self::updateMultiple($data);
    }
}