<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'email' => 'required|string|email|max:150|unique:users',
            'phone' => 'nullable|string|max:20|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
            'role' => 'customer',
            'is_active' => true,
        ]);

        $token = auth('api')->login($user);

        return $this->respondWithToken($token, $user);
    }

    /**
     * Handle Login (Email, Username, or Phone)
     */
    public function login(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string', // can be email, username, or phone
            'password' => 'required|string',
        ]);

        $identifier = $request->identifier;
        $field = filter_var($identifier, FILTER_VALIDATE_EMAIL) ? 'email' : (is_numeric($identifier) ? 'phone' : 'username');

        $credentials = [
            $field => $identifier,
            'password' => $request->password,
        ];

        if (! $token = auth('api')->attempt($credentials)) {
            return response()->json(['success' => false, 'error' => 'Thông tin đăng nhập không chính xác'], 401);
        }

        $user = auth('api')->user();

        if (!$user->is_active) {
            auth('api')->logout();
            return response()->json(['success' => false, 'error' => 'Tài khoản của bạn đã bị khóa'], 403);
        }

        return $this->respondWithToken($token, $user);
    }

    /**
     * Get the authenticated User.
     */
    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => auth('api')->user()
        ]);
    }

    /**
     * Log the user out (Invalidate the token).
     */
    public function logout()
    {
        auth('api')->logout();

        return response()->json(['success' => true, 'message' => 'Successfully logged out']);
    }

    /**
     * Refresh a token.
     */
    public function refresh()
    {
        return $this->respondWithToken(auth('api')->refresh());
    }

    /**
     * Get the token array structure.
     */
    protected function respondWithToken($token, $user = null)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => $user ?? auth('api')->user()
        ]);
    }
}
