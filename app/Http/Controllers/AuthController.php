<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Illuminate\Http\JsonResponse;

/**
 * Class AuthController
 *
 * Handles user authentication and token management.
 *
 * @package App\Http\Controllers
 */
class AuthController extends Controller
{
    /**
     * Authenticate a user and return an API token.
     * Code Owner Salman@everestbuys.com github.com/skdevelopers
     * This endpoint validates user credentials and issues a personal access token.
     * Laravel 12 improvements such as enhanced dependency injection and in-memory testing
     * support have been taken into account to optimize this method for testability.
     *
     * @param Request $request The incoming request containing user credentials.
     * @return JsonResponse JSON response containing the token or an error message.
     *
     * @OA\Post(
     *     path="/api/login",
     *     summary="User Login",
     *     description="Authenticate user and return a new API token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="secret123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful authentication",
     *         @OA\JsonContent(
     *             @OA\Property(property="token", type="string", example="1|k3j2h4kjh5...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(Request $request): JsonResponse
    {
        // Validate incoming request data
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        // Attempt to find the user by email
        $user = User::where('email', $credentials['email'])->first();

        // Check password validity
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        // Issue a new personal access token
        $token = $user->createToken('api-token')->plainTextToken;

        return response()->json(['token' => $token], 200);
    }

    /**
     * Revoke the current user's API token to log out.
     *
     * This endpoint revokes the token associated with the current request,
     * ensuring secure logout. Optimized for testability using Laravel 12's built-in features.
     *
     * @param Request $request The incoming request.
     * @return JsonResponse JSON response confirming successful logout.
     *
     * @OA\Post(
     *     path="/api/logout",
     *     summary="User Logout",
     *     description="Revoke the current user's API token",
     *     tags={"Authentication"},
     *     @OA\Response(
     *         response=200,
     *         description="Successful logout",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Logged out successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     )
     * )
     */
    public function logout(Request $request): JsonResponse
    {
        // Revoke the token that was used to authenticate the current request.
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out successfully'], 200);
    }
}
