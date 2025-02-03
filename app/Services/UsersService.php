<?php

namespace App\Services;

use App\Enums\HttpStatusEnum;
use App\Enums\Permission;
use App\Enums\Role;
use App\Models\User;
use Illuminate\Http\Response;
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
}
