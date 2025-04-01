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

    public static function createMultiple(array $collection): bool
    {
        return self::insert($collection);
    }

    public static function findOne(int $id): Model|null
    {
        return self::query()->find($id);
    }

    public static function createCompany(array $data): Model
    {
        return self::create($data);
    }

    public function updateCompany(array $data): bool
    {
        return $this->update($data);
    }
}