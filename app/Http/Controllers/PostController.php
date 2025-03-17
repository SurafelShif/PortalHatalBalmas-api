<?php

namespace App\Http\Controllers;

use App\Enums\HttpStatusEnum;
use App\Enums\ResponseMessages;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\GetPostsRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Services\PostsService;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

class PostController extends Controller
{
    public function __construct(private PostsService $PostService) {}

    /**
     * @OA\Get(
     *     path="/api/posts",
     *     summary="מביא רשימת חדשות",
     *     description="מביא רשימת חדשות עם אפשרות לסינון לפי קטגוריה, חיפוש ודפדוף.",
     *     operationId="getPosts",
     *     tags={"Post"},
     *
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="סינון לפי מאפיין הקטגוריה",
     *         required=false,
     *         @OA\Schema(type="int", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="מילת חיפוש בכותרת, תיאור או תוכן",
     *         required=false,
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="כמות פריטים בעמוד",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="מספר עמוד לתצוגה",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function getPosts(GetPostsRequest $request)
    {
        $search = $request->query('query');
        $category_id = $request->query('category_id');
        $limit = $request->query('limit');
        $page = $request->query('page', 1);
        $result = $this->PostService->getPosts($category_id, $limit, $page, $search);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
    /**
     * @OA\Get(
     *     path="/api/posts/admin",
     *     summary="מביא רשימת חדשות",
     *     description="מביא רשימת חדשות עם אפשרות לסינון לפי קטגוריה, חיפוש ודפדוף.",
     *     operationId="getAdminPosts",
     *     tags={"Post"},
     *
     *     @OA\Parameter(
     *         name="category_id",
     *         in="query",
     *         description="סינון לפי מאפיין הקטגוריה",
     *         required=false,
     *         @OA\Schema(type="int", example="1")
     *     ),
     *     @OA\Parameter(
     *         name="query",
     *         in="query",
     *         description="מילת חיפוש בכותרת, תיאור או תוכן",
     *         required=false,
     *         @OA\Schema(type="string", example="")
     *     ),
     *     @OA\Parameter(
     *         name="limit",
     *         in="query",
     *         description="כמות פריטים בעמוד",
     *         required=false,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="מספר עמוד לתצוגה",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function getAdminPosts(GetPostsRequest $request)
    {
        $search = $request->query('query');
        $category_id = $request->query('category_id');
        $limit = $request->query('limit');
        $page = $request->query('page', 1);
        $result = $this->PostService->getAdminPosts($category_id, $limit, $page, $search);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
    /**
     * @OA\Get(
     *     path="/api/posts/{uuid}",
     *     summary="מביא פוסט לפי UUID",
     *     description="מביא פוסט בודד לפי מזהה ייחודי (UUID).",
     *     operationId="getPostByUUid",
     *     tags={"Post"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הפוסט",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="בקשה לא תקינה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הפוסט לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */

    public function getPostByUUid($uuid)
    {
        $result = $this->PostService->getPostByUUid($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::POST_NOT_FOUND], Response::HTTP_NOT_FOUND),
                HttpStatusEnum::BAD_REQUEST => response()->json(["message" => ResponseMessages::BAD_REQUEST], Response::HTTP_BAD_REQUEST),
            };
        }
        return response()->json($result, Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/posts",
     *     summary="יוצר פוסט חדש",
     *     description="יוצר פוסט חדש עם כותרת, תיאור, תוכן, קטגוריה ותמונה.",
     *     operationId="createPost",
     *     tags={"Post"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 required={"title", "description", "content", "category_id", "image"},
     *                 @OA\Property(property="title", type="string", example="כותרת הפוסט"),
     *                 @OA\Property(property="description", type="string", example="תיאור קצר של הפוסט"),
     *  @OA\Property(
     *     property="content",
     *     type="string",
     *     example="{blah:{dfgfdgfd}"
     * ),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="image", type="string", format="binary", description="תמונה מצורפת לפוסט")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="הפוסט נוצר בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="בקשה לא תקינה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */


    public function createPost(CreatePostRequest $request)
    {
        $result = $this->PostService->createPosts($request->title, $request->description, $request->content, $request->category_id, $request->image);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_CREATED);
    }

    /**
     * @OA\Delete(
     *     path="/api/posts/{uuid}",
     *     summary="מוחק פוסט לפי UUID",
     *     description="מוחק פוסט בודד לפי מזהה ייחודי (UUID).",
     *     operationId="deletePostByUUid",
     *     tags={"Post"},
     *
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הפוסט",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפעולה בוצעה בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="הפוסט לא נמצא",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */

    public function deletePost($uuid)
    {
        $result = $this->PostService->deletePost($uuid);
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::POST_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'message' => ResponseMessages::SUCCESS_ACTION,
        ], Response::HTTP_OK);
    }
    /**
     * @OA\Post(
     *     path="/api/posts/{uuid}",
     *     summary="מעדכן פוסט ",
     *     description="מעדכן פוסט .",
     *     operationId="updatePost",
     *     tags={"Post"},
     *     @OA\Parameter(
     *         name="uuid",
     *         in="path",
     *         description="UUID של הפוסט",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid", example="4a206b4-99d6-4692-915c-4935766e0420")
     *     ),
     *
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="title", type="string", example="כותרת הפוסט"),
     *                 @OA\Property(property="description", type="string", example="תיאור קצר של הפוסט"),
     *  @OA\Property(
     *     property="content",
     *     type="string",
     *     example="{blah:{dfgfdgfd}"
     * ),
     *                 @OA\Property(property="category_id", type="integer", example=1),
     *                 @OA\Property(property="image", type="string", format="binary", description="תמונה מצורפת לפוסט")
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="הפוסט נוצר בהצלחה",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="בקשה לא תקינה",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="שגיאה בשרת",
     *     )
     * )
     */
    public function updatePost($uuid, UpdatePostRequest $request)
    {
        $result = $this->PostService->updatePost($uuid, $request->validated());
        if ($result instanceof HttpStatusEnum) {
            return match ($result) {
                HttpStatusEnum::ERROR => response()->json(["message" => ResponseMessages::ERROR_OCCURRED], Response::HTTP_INTERNAL_SERVER_ERROR),
                HttpStatusEnum::NOT_FOUND => response()->json(["message" => ResponseMessages::POST_NOT_FOUND], Response::HTTP_NOT_FOUND),
            };
        }
        return response()->json([
            'data' => $result,
        ], Response::HTTP_OK);
    }
}
