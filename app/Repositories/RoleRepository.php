<?php

namespace App\Repositories;

use App\Models\Role;
use App\Repositories\Contracts\RoleRepositoryInterface;
use Illuminate\Support\Str;

class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    public function __construct(Role $model){
        parent::__construct($model);
    }

    public function getRole()
    {
       return $this->model->orderBy('order', 'asc')->get();
    }

    public function createRole(array $data)
    {
        $data['color'] = $data['color'] ?? Role::generateUniqueColor();
        $data['order'] = Role::max('order') + 1;
        $data['name'] = Str::slug($data['display_name'], '');
        $data['guard_name'] = 'web';
        return $this->model->create($data);
    }

    public function updateRole(int $id, array $data)
    {
        $dataUpdate = [
            'display_name' => $data['display_name'],
            'status' => $data['status'],
            'description' => $data['description'],
            'code' => $data['code'],
        ];
        return $this->model->findOrFail($id)->update($dataUpdate);
    }

    public function listRoleToSelect()
    {
        return $this->model->where('status', 'active')->get(['id', 'name', 'display_name']);
    }

    public function summary()
    {
        $totalRoles = $this->model->count();
        $activeRoles = $this->model->where('status', 'active')->count();
        $inactiveRoles = $this->model->where('status', 'inactive')->count();
        $totalEmployees = \App\Models\User::count();
        $highestRole = $this->model->orderBy('order', 'asc')->first();
        return [
            'totalRoles' => $totalRoles,
            'activeRoles' => $activeRoles,
            'inactiveRoles' => $inactiveRoles,
            'totalEmployees' => $totalEmployees,
            'highestRole' => $highestRole,
        ];
    }
}
