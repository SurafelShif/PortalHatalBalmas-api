<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Enums\Permission;
use App\Enums\Role;
use App\Http\Resources\UserResource;
use App\Models\User;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UsersService
{
    public function addAdmin(string $full_name, int $personal_id)
    {
        try {
            $user = User::where('personal_id', $personal_id)->first();

            if (!is_null($user)) {
                if ($user->hasRole(Role::ADMIN)) {
                    return HttpStatusEnum::CONFLICT;
                }
            } else {
                $user = User::create(["personal_id" => $personal_id, "full_name" => $full_name]);
            }
            $user->assignRole(Role::ADMIN);
            $user->givePermissionTo(Permission::MANAGE_USERS);
            return Response::HTTP_CREATED;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function deleteAdmin($uuid)
    {
        try {
            $user = User::where('uuid', $uuid)->first();
            $AuthenticatedUser = Auth::user();
            if (is_null($user))
                return HttpStatusEnum::NOT_FOUND;
            if ($AuthenticatedUser->personal_id === $user->personal_id) {
                return HttpStatusEnum::FORBIDDEN;
            }
            $user->removeRole(Role::ADMIN);
            $user->revokePermissionTo(Permission::MANAGE_USERS);
            return Response::HTTP_CREATED;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getAdmins()
    {
        try {
            $users = User::whereHas('roles', function ($query) {
                $query->where('name', 'admin');
            })
                ->select(['uuid', 'full_name', 'personal_id'])
                ->get();

            return $users;
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    public function getLoggedUser()
    {
        try {
            $user = Auth::user();
            return new UserResource($user);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
}
