<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleController extends Controller
{
    use ApiResponse;
    public function __construct(
        public RoleRepositoryInterface $roleRepository
    )
    {

    }

    public function getRolesActive()
    {
        try {
            $redirects = $this->roleRepository->listWithFilter()->where(Role::active(), ROLE_STATUS_ACTIVE)->get();
        } catch (\Exception $e) {
            return $this->errorResponse('Error', Response::HTTP_UNPROCESSABLE_ENTITY, $e->getMessage());
        }

        return $this->successResponse($redirects, 'Danh sách người dùng');
    }
}
