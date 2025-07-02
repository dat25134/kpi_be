<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\Request;

interface RoleRepositoryInterface extends BaseRepositoryInterface
{
    public function getRole();
    public function listRoleToSelect();
    public function summary();
    public function createRole(array $data);
    public function updateRole(int $id, array $data);
}