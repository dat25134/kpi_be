<?php

namespace App\Repositories;

use App\Models\User;
use App\Models\UserInfo;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;

class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function __construct(User $model)
    {
        parent::__construct($model);
    }

    public function all(): Collection
    {
        return $this->model->all();
    }

    public function getEmployeesWithFilters(array $filters, int $limit = 10): LengthAwarePaginator
    {
        $query = $this->model->with(['info', 'department', 'projects']);

        if (isset($filters['search']) && !empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('email', 'like', "%{$searchTerm}%")
                  ->orWhere('phone', 'like', "%{$searchTerm}%");
            });
        }

        if (isset($filters['department_id']) && !empty($filters['department_id'])) {
            $query->where('department_id', $filters['department_id']);
        }

        if (isset($filters['status']) && !empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['role_name']) && !empty($filters['role_name'])) {
            $query->role($filters['role_name']);
        }

        return $query->paginate($limit);
    }

    public function getEmployeeStats(): array
    {
        $totalEmployees = $this->model->count();
        $activeEmployees = $this->model->where('status', 'active')->count();
        $inactiveEmployees = $totalEmployees - $activeEmployees;
        
        $averageSalary = UserInfo::join('users', 'user_info.user_id', '=', 'users.id')
            ->whereNull('users.deleted_at')
            ->avg('user_info.salary');

        $departmentStats = DB::table('departments')
            ->leftJoin('users', 'departments.id', '=', 'users.department_id')
            ->select(
                'departments.id as departmentId',
                'departments.name as departmentName',
                'departments.code as departmentCode',
                DB::raw('COUNT(users.id) as employeeCount')
            )
            ->groupBy('departments.id', 'departments.name', 'departments.code')
            ->get()
            ->toArray();

        // Thống kê số lượng user theo role
        $roleStats = DB::table('model_has_roles')
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->select('roles.name as role', DB::raw('count(model_has_roles.model_id) as count'))
            ->groupBy('roles.name')
            ->get()
            ->map(function ($stat) {
                return [
                    'role' => $stat->role,
                    'count' => $stat->count,
                ];
            })
            ->toArray();

        return [
            'totalEmployees' => $totalEmployees,
            'activeEmployees' => $activeEmployees,
            'inactiveEmployees' => $inactiveEmployees,
            'averageSalary' => $averageSalary !== null ? round($averageSalary) : 0,
            'departmentStats' => $departmentStats,
            'roleStats' => $roleStats,
        ];
    }

    public function createEmployee(array $userData, array $userInfoData)
    {
        DB::beginTransaction();
        try {
            // Tạo User
            $user = $this->model->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'department_id' => $userData['departmentId'],
                'password' => Hash::make('password'), // Mật khẩu mặc định
                'employee_id' => 'EMP' . strtoupper(Str::random(6)), // Mã nhân viên tự động
                'status' => 'active',
                'join_date' => now(),
            ]);
            $user->syncRoles([$userData['roleName']]);

            // Tạo UserInfo
            UserInfo::create([
                'user_id' => $user->id,
                'salary' => $userInfoData['salary'] ?? null,
                'address' => $userInfoData['address'] ?? null,
                'birth_date' => isset($userInfoData['birthDate']) ? Carbon::createFromFormat('d/m/Y', $userInfoData['birthDate'])->format('Y-m-d') : null,
                'gender' => $userInfoData['gender'] ?? null,
                'education' => $userInfoData['education'] ?? null,
                'experience' => $userInfoData['experience'] ?? null,
                'skills' => $userInfoData['skills'] ?? [],
            ]);

            DB::commit();

            return $user->load(['info', 'department', 'projects']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateEmployee(int $id, array $userData, array $userInfoData)
    {
        DB::beginTransaction();
        try {
            $user = $this->findOrFail($id);

            // Cập nhật User
            $user->update([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'phone' => $userData['phone'] ?? null,
                'department_id' => $userData['departmentId'],
            ]);
            $user->syncRoles([$userData['roleName']]);

            // Cập nhật UserInfo
            $user->info->update([
                'salary' => $userInfoData['salary'] ?? null,
                'address' => $userInfoData['address'] ?? null,
                'birth_date' => isset($userInfoData['birthDate']) ? Carbon::createFromFormat('d/m/Y', $userInfoData['birthDate'])->format('Y-m-d') : null,
                'gender' => $userInfoData['gender'] ?? null,
                'education' => $userInfoData['education'] ?? null,
                'experience' => $userInfoData['experience'] ?? null,
                'skills' => $userInfoData['skills'] ?? [],
            ]);

            DB::commit();

            return $user->load(['info', 'department', 'projects']);

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function deleteEmployee(int $id): bool
    {
        $user = $this->findOrFail($id);

        if ($user->hasRole('admin')) {
            throw new \Exception('Không thể xóa tài khoản quản trị viên.');
        }

        DB::beginTransaction();
        try {
            $user->info()->delete();
            $user->delete();
            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function isAdmin(int $id): bool
    {
        $user = $this->find($id);
        return $user ? $user->hasRole('admin') : false;
    }

    public function findByEmail(string $email)
    {
        return $this->model->where('email', $email)->first();
    }

    public function manager()
    {
        return $this->model->role('truongphong')->get();
    }

    public function details(int $id)
    {
        return $this->model->with(['info', 'department', 'projects'])->findOrFail($id);
    }
}