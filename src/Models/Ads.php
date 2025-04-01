<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
    ];

    public static function findOne(int $id): Model|null
    {
        return self::query()->find($id)?->getModel();
    }


    public static function createAd(array $data): Model
    {
        return self::create($data);
    }

    public static function createMultiple(array $collection): bool
    {
        return self::insert($collection);
    }

    public function updateAd(array $data): bool
    {
        return $this->update($data);
    }


    public function setCompany(Model $company): static
    {
        $this->company_id = $company->id;
        $this->save();

        return $this;
    }

    public function setGroup(Model $group): static
    {
        $this->group_id = $group->id;
        $this->save();

        return $this;
    }

    public function group(): BelongsTo
    {
        return $this->belongsTo(AdsAdset::class);
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(AdsCampaign::class);
    }
}
