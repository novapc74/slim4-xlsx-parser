<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
class BaseRepository implements RepositoryInterface
{
    private Builder $query;

    public function __construct($class)
    {
        $this->query = $class::query();
    }

    public function find(int $id): ?Model
    {
        return $this->query->find($id);
    }

    public function findAll(): Collection
    {
        return $this->query->get();
    }

    public function findOneBy(array $criteria): ?Model
    {
        foreach ($criteria as $key => $value) {
            $this->query->where($key, $value);
        }

        return $this->query->first();
    }

    public function findBy(array $criteria, ?array $orderBy = []): Collection
    {
        foreach ($criteria as $key => $value) {
            $this->query->where($key, $value);
        }

        foreach ($orderBy as $field => $direction) {
            $this->query->orderBy($field, $direction);
        }

        return $this->query->get();
    }
}