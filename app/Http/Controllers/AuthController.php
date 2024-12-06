<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\instansi;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    //
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|max:255',
            'instansiName' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $request->username,
            'role' => 'manager',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $instansi = instansi::create([
            'nama' => $request->instansiName,
            'description' => $request->instansiName,
            'owner_id' => $user->id,
            'total_organization' => 0
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user, 'instansi' => $instansi]);
    }

    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'user' => $user]);
    }
    public function nologin()
    {
        return response()->json(['message' => 'You are not authenticated', 'status' => 401], 401);
    }

    public function logout()
    {
        Auth::user()->tokens()->delete();

        return response()->json(['message' => 'Logged out successfully']);
    }

    public function profile()
    {
        return response()->json(['user' => Auth::user()]);
    }
    public function profiles()
    {
        $users = User::get();
        return response()->json(['user' => $users]);
    }
    public function getAuth()
    {
        $users = Auth::user();
        return response()->json(['user' => $users]);
    }
}
