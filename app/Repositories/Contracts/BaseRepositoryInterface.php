<?php

namespace App\Repositories\Contracts;

use Illuminate\Http\Request;

interface BaseRepositoryInterface
{
    public function all();

    public function listBy($column, $value, $columns = ['*']);

    public function find($id);

    public function create(array $data);

    public function bulkCreate(array $data);

    public function update($id, array $data);

    public function upCreate(array $data, array $columns = ['*']);

    public function delete($id);

    public function bulkDelete(array $id);

    public function findBy($column, $value, $columns = ['*']);

    public function listWithPaginate($limit);

    public function listWithFilter(Request|array $request = [], $columns = ['*']);

    public function loadScopes($scopes): array;
}
