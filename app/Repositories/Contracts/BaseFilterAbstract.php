<?php

namespace App\Repositories\Contracts;

use App\Traits\ApiResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseFilterAbstract
{
    protected Request $request;
    use ApiResponse;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function apply(Builder $query): Builder|\Illuminate\Http\JsonResponse
    {
        foreach ($this->filters() as $column => $method) {

            if ($this->request->filled($column)) {
                if (is_array($method)) {
                    $baseMethod = $method[0];
                    $field = $method[1];
                    try {
                        $this->$baseMethod($query, $field, $this->request->input($column));
                    }
                    catch (\Exception $e) {
                        return $this->errorResponse($e->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
                    }
                } else {
                    $this->$method($query, $this->request->input($column));
                }
            }
        }
        return $query;
    }


    abstract protected function filters(): array;

    protected function filterLike(Builder $query, string $field, string $value)
    {
        $query->where($field, 'LIKE', "%{$value}%");
    }

    protected function filterExact(Builder $query, string $field, string $value)
    {
        $query->where($field, $value);
    }

    protected function filterBetweenDatetime(Builder $query, string $field, array $range)
    {
        if (count($range) === 2 && $range[0] && $range[1]) {
            $query->whereBetween($field, [$range[0], $range[1]]);
        }
    }
}
