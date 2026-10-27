<?php
namespace App\Modules\Users\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Modules\Users\Models\User;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6|confirmed',
        ]);
        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => bcrypt($validated['password']),
            'role' => 'customer',
        ]);
        $token = auth("api")->login($user);
        return response()->json([
            'error' => ['message' => 'Registered successfully', 'code' => 201],
            'token' => $token,
        ], 201);
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);
        if (!$token = auth("api")->attempt($request->only('email', 'password'))) {
            return response()->json([
                'error' => ['message' => 'Invalid credentials', 'code' => 401, 'details' => []],
            ], 401);
        }
        return response()->json([
            'token' => $token,
            'user' => Auth::user(),
        ]);
    }
    public function logout()
    {
        auth("api")->logout();
        return response()->json(['error' => ['message' => 'Logged out', 'code' => 200, 'details' => []]]);
    }
    public function me()
    {
        return response()->json(auth("api")->user());
    }
}
