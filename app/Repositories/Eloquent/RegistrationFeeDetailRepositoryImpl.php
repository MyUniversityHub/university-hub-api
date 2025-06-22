<?php

namespace App\Repositories\Eloquent;

use App\Models\RegistrationFeeDetail;
use App\Repositories\Contracts\RegistrationFeeDetailRepositoryInterface;
use App\Repositories\Eloquent\Filters\RegistrationFeeDetailFilter;

class RegistrationFeeDetailRepositoryImpl extends BaseRepositoryImpl implements RegistrationFeeDetailRepositoryInterface
{
    public function __construct(RegistrationFeeDetail $model, RegistrationFeeDetailFilter $filter)
    {
        parent::__construct($model, $filter);
    }

    public function updateCompositeKey(array $keys, array $data)
    {
        return $this->model->where($keys)->update($data);
    }

    public function deleteCompositeKey(array $keys)
    {
        return $this->model->where($keys)->delete();
    }

    public function getRegistrationFeeDetailByStudentAndStatus($studentId, $status)
    {
        return $this->model->newQuery()
            ->where('student_id', $studentId)
            ->where('status', $status)
            ->get();
    }


}
