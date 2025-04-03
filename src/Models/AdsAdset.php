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

    public static function findOne(int $id): Model|null
    {
        return self::query()->find($id);
    }

    public static function createGroup(array $data): Model
    {
        return self::create($data);
    }

    public function updateGroup(array $data): bool
    {
        return $this->update($data);
    }

    public static function updateMultiple(array $collection): bool
    {
        return self::upsert($collection, ['id'], ['name']);
    }
}