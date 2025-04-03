<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

/**
 * @mixin Builder
 */
class Ads extends Model
{
    protected $table = 'ads';
    public $incrementing = false;
    public $timestamps = false;
    public $fillable = [
        'id',
        'updated_at',
        'credit',
        'name',
        'display_count',
        'click_count',
        'company_id',
        'group_id',
    ];

    public static function findOneBy(array $criteria): Model|null
    {
        foreach ($criteria as $key => $value) {
            self::query()->where($key, $value);
        }

        return self::query()->get()->first();
    }

    public static function deleteAds(int|array $id): bool
    {
        if (!is_array($id)) {
            $id = [$id];
        }

        return self::destroy($id);
    }

    public static function updateMultiple(array $collection): bool
    {
        return self::upsert($collection, ['id', 'updated_at'], [
                'updated_at',
                'credit',
                'name',
                'display_count',
                'click_count',
                'company_id',
                'group_id'
            ]);
    }

    public static function updateAds(array $data): bool
    {
        return self::updateMultiple([$data]);
    }
}
