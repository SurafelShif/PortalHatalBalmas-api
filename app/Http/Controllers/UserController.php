<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\AddAdminRequest;
use App\Services\UsersService;
use Symfony\Component\HttpFoundation\Response;


class UserController extends Controller
{
    public function __construct(private UsersService $usersService) {}

    /**
     * @OA\Get(
     *      path="/api/users",
     *      operationId="getAdmins",
     *      tags={"Users"},
     *      summary="קבלת רשימת המנהלים",
     *      description="מחזיר רשימה של כל המשתמשים שיש להם הרשאת מנהל",
     *      @OA\Response(
     *          response=200,
     *          description="רשימת המנהלים התקבלה בהצלחה",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="הפעולה התבצעה בהצלחה"),
     *              @OA\Property(property="data", type="array", @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="full_name", type="string", example="ישראל ישראלי"),
     *                  @OA\Property(property="uuid", type="string", format="uuid", example="b143c4ab-91a7-481a-ab1a-cf4a00d2fc11"),
     *                  @OA\Property(property="personal_id", type="string", example="123456789")
     *              ))
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="שגיאת שרת פנימית",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="אירעה שגיאה"))
     *      )
     * )
     */
    public function index()
    {
        $result = $this->usersService->getAdmins();
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json(
            $result,
            Response::HTTP_OK
        );
    }

    /**
     * @OA\Post(
     *      path="/api/users",
     *      operationId="addAdmin",
     *      tags={"Users"},
     *      summary="הוספת מנהל חדש",
     *      description="הוספת משתמש לרשימת המנהלים לפי מספר תעודת זהות",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"fullName", "personal_id"},
     *              @OA\Property(property="fullName", type="string", example="ישראל ישראלי"),
     *              @OA\Property(property="personal_id", type="string", example="123456789")
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="המנהל נוסף בהצלחה",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="הפעולה התבצעה בהצלחה"))
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="משתמש זה כבר מנהל",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="משתמש זה כבר מנהל מערכת"))
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="שגיאת שרת פנימית",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="אירעה שגיאה"))
     *      )
     * )
     */
    public function addAdmin(AddAdminRequest $request)
    {
        $result = $this->usersService->addAdmin($request->fullName, $request->personal_id);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::CONFLICT => response()->json(["message" => ResponseMessages::ALREADY_ADMIN], Response::HTTP_CONFLICT),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *      path="/api/users/{personal_id}",
     *      operationId="deleteAdmin",
     *      tags={"Users"},
     *      summary="הסרת מנהל",
     *      description="הסרת הרשאת מנהל ממשתמש לפי מספר תעודת זהות",
     *      @OA\Parameter(
     *          name="personal_id",
     *          in="path",
     *          required=true,
     *          description="מספר תעודת הזהות של המנהל להסרה",
     *          @OA\Schema(type="string", example="123456789")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="המנהל הוסר בהצלחה",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="הפעולה התבצעה בהצלחה"))
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="אין אפשרות להסיר את עצמך",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="אין אפשרות להסיר את עצמך"))
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="משתמש לא נמצא",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="משתמש אינו נמצא"))
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="שגיאת שרת פנימית",
     *          @OA\JsonContent(@OA\Property(property="message", type="string", example="אירעה שגיאה"))
     *      )
     * )
     */
    public function deleteAdmin($personal_id)
    {
        $result = $this->usersService->deleteAdmin($personal_id);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::USER_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::FORBIDDEN => response()->json(["message" => ResponseMessages::SELF_REMOVAL], Response::HTTP_FORBIDDEN),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Get(
     *      path="/api/user",
     *      operationId="user",
     *      tags={"Users"},
     *      summary="Get authenticated user",
     *      description="Returns the authenticated user's details",
     *      @OA\Response(
     *          response=200,
     *          description="הפעולה התבצעה בהצלחה",
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="המשתמש לא מחובר",
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="אירעה שגיאה",
     *      )
     * )
     */
    public function user()
    {
        $result = $this->usersService->getLoggedUser();
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
}
