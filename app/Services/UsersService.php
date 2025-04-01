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
            if (!$user->hasRole(Role::ADMIN)) {
                return HttpStatusEnum::CONFLICT;
            }
            // if ($AuthenticatedUser->personal_id === $user->personal_id) {
            //     return HttpStatusEnum::FORBIDDEN;
            // }
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
    public function getUserByPersonalNumber($personal_id)
    {
        try {
            // if ($personal_id && preg_match('/^\d{9}$/', $personal_id) !== 1) {
            //     return HttpStatusEnum::BAD_REQUEST;
            // }
            $user = User::whereDoesntHave('roles', function ($query) {
                $query->where('name', 'admin');
            })
                ->where('personal_id', $personal_id)->where('is_deleted', false)->first();
            if (is_null($user)) {
                return HttpStatusEnum::NOT_FOUND;
            }
            return [
                'full_name' => $user['full_name'],
                'personal_id' => $user['personal_id'],
            ];
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return HttpStatusEnum::ERROR;
        }
    }
    //------------------------------------------------------------//
    // public function getUserByPersonalNumber($personal_number)
    // {
    //     try {
    //         if ($personal_number && preg_match('/^\d{7,8}$/', $personal_number) !== 1) {
    //             return HttpStatusEnum::BAD_REQUEST;
    //         }
    //         $user = User::where('personal_id', $personal_id)->first();
    //         if (is_null($user)) {
    //             $user = $this->getUserFromVatican($personal_number);
    //             if (is_null($user)) {
    //                 return HttpStatusEnum::NOT_FOUND;
    //             }
    //         } else {
    //             if ($user->hasRole(Role::ADMIN)) {
    //                 return HttpStatusEnum::CONFLICT;
    //             }
    //         }
    //         return [
    //             'full_name' => $user['full_name'],
    //             'personal_number' => $user['personal_number'],
    //         ];
    //     } catch (\Exception $e) {
    //         Log::error($e->getMessage());
    //         return HttpStatusEnum::ERROR;
    //     }
    // }
    // private function getUserFromVatican($personalNumber)
    // {
    //     try {
    //         $client = new Client();
    //         $vaticanUrl = env("VATICAN_URL");
    //         $vaticanToken = env("VATICAN_TOKEN");

    //         $queryParams = [
    //             'columns' => implode(',', [
    //                 AdfsColumnsEnum::PERSONAL_NUMBER->value,
    //                 AdfsColumnsEnum::FIRST_NAME->value,
    //                 AdfsColumnsEnum::SURNAME->value,
    //             ]),
    //         ];

    //         $response = $client->get(
    //             $vaticanUrl . "/api/users/" . $personalNumber,
    //             [
    //                 'verify' => false,
    //                 'query' => $queryParams,
    //                 'headers' => [
    //                     'Authorization' => 'Bearer ' . $vaticanToken,
    //                 ],
    //             ]
    //         );

    //         $userFromAdfs = json_decode($response->getBody(), true);
    //         return [
    //             'full_name' => $userFromAdfs['first_name'] . ' ' . $userFromAdfs['surname'],
    //             'personal_number' => $userFromAdfs['personal_number'],
    //         ];
    //     } catch (\Exception $e) {
    //         $statusCode = $e->getCode();
    //         if ($statusCode === 0) {
    //             return null;
    //         }
    //     }
    // }
}
