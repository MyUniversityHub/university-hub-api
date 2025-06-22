<?php

namespace App\Repositories\Contracts;

interface RegistrationFeeDetailRepositoryInterface extends BaseRepositoryInterface
{
    public function getRegistrationFeeDetailByStudentAndStatus($studentId, $status);
    public function updateCompositeKey(array $keys, array $data);

    public function deleteCompositeKey(array $keys);
}
