<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contracts\BaseFilterAbstract;
use App\Repositories\Contracts\BaseRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BaseRepositoryImpl implements BaseRepositoryInterface
{
    protected Model $model;
    protected ?BaseFilterAbstract $filter;

    public function __construct(Model $model, ?BaseFilterAbstract $filter = null)
    {
        $this->model = $model;
        $this->filter = $filter;
    }

    public function all(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->all();
    }

    // lấy danh sách theo điều kiện
    public function listBy($column, $value, $columns = ['*']): \Illuminate\Database\Eloquent\Collection
    {
        return $this->model->newQuery()
            ->select($columns)
            ->where($column, $value)
            ->get();
    }

    // Lấy danh sách với phân trang
    public function listWithPaginate($limit = 15, $orderBy = 'id', $sortBy = 'ASC'): \Illuminate\Contracts\Pagination\LengthAwarePaginator
    {
        return $this->model->newQuery()->orderBy($orderBy, $sortBy)->paginate($limit);
    }

    public function find($id)
    {
        return $this->model->newQuery()->find($id);
    }

    public function create(array $data)
    {
        return $this->model->create($data);
    }

    // Tạo nhiều record
    public function bulkCreate(array $data): bool
    {
        return $this->model->newQuery()->insert($data);
    }

    public function update($id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);
        return $record;
    }

    // Update hoặc tạo mới record
    public function upCreate(array $data, array $columns = ['*']): int
    {
        return $this->model->newQuery()->upsert($data, $columns);
    }

    public function delete($id): true
    {
        $record = $this->find($id);
        $record->delete();
        return true;
    }

    public function bulkDelete(array $id)
    {
        return $this->model->whereIn($this->model->getKeyName(), $id)->delete();
    }


    // Tìm 1 record theo điều kiện
    public function findBy($column, $value, $columns = ['*'])
    {
        return $this->model->newQuery()
            ->select($columns)
            ->where($column, $value)
            ->first();
    }

    // Filter
    public function listWithFilter(Request|array $request = [], $columns = ['*']): Builder
    {
        $query = $this->model->newQuery()
            ->select($columns);

        if (!$this->filter) {
            return $query;
        }

        return $this->filter->apply($query, $request);
    }


    public function loadScopes($scopes): array
    {
        $namedScoped = [];
        foreach ($scopes as $name => $args) {
            $scopeName = Str::camel($name);
            if (!$this->model->hasNamedScope($scopeName) || is_null($args)) {
                continue;
            }
            $namedScoped[$scopeName] = $args;
        }

        return $namedScoped;
    }
}
