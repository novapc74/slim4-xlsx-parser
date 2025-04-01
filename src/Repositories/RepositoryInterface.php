<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Collection;

interface RepositoryInterface
{
    public function find(int $id): ?Model;
    public function findOneBy(array $criteria): ?Model;
    public function findBy(array $criteria, ?array $orderBy = []): Collection;
    public function findAll(): ?Collection;

}