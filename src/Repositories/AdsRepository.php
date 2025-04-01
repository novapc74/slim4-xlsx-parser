<?php

namespace App\Repositories;

use App\Models\Ads;

class AdsRepository extends BaseRepository
{
    public function __construct()
    {
        parent::__construct(Ads::class);
    }
}