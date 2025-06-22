<?php

namespace App\Repositories\Contracts;

interface CurriculumProgramRepositoryInterface extends BaseRepositoryInterface
{
    // ...add custom methods if needed...
    public function getCoursesByMajorId($majorId);

    public function deleteByCompositeKey($majorId, $courseId);
}
