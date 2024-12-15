<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\eskul;
use App\Models\instansi;
use App\Models\InstansiUser;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

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
            'profile_image' => $request->profile_image,
            'role' => 'Manager',
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
    public function addUser(Request $request)
    {
        $userRequest = Auth::user();


        $instansi = Instansi::where('owner_id', $userRequest->id)->first();

        if ($userRequest->role != "Manager") {
            return response()->json(['message' => 'Forbidden', 'status' => 403]);
        }

        $imagePath = null;
        if ($request->hasFile('profile_image')) {
            // Save the file and get the path
            $imagePath = $request->file('profile_image')->store('profiles', 'public');
        }

        $request->validate([
            'username' => 'required|string|max:255',
            'password' => 'required|string',
            'profile_image' => 'file|nullable',
            'email' => 'required|string|email|max:255|unique:users',
            'leader_eskul_id' => 'required',
        ]);

        $user = User::create([
            'name' => $request->username,
            'profile_image' => $imagePath,
            'role' => $request->leader_eskul_id != 0 ? 'Leader' : 'Member',
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        InstansiUser::create([
            'instansi_id' => $instansi->id,
            'user_id' => $user->id
        ]);

        if ($request->leader_eskul_id != 0) {
            $eskul = eskul::where('id', $request->leader_eskul_id)->first();
            $eskul->leader_id = $user->id;
            $eskul->update();
        }


        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'data' => $user]);
    }

    public function editUser(Request $request)
    {
        $userRequest = Auth::user();

        // Check if the user has the manager role
        if ($userRequest->role != "Manager") {
            return response()->json(['message' => 'Forbidden', 'status' => 403]);
        }

        // Fetch the user to be edited
        $user = User::find($request->id);
        if (!$user) {
            return response()->json(['message' => 'User not found', 'status' => 404]);
        }

        $instansi = Instansi::where('owner_id', $userRequest->id)->first();

        // Validate request
        $request->validate([
            'username' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string',
            'profile_image' => 'file|nullable',
            'leader_eskul_id' => 'nullable',
        ]);




        if ($request->leader_eskul_id == 0) {
            $eskulEdit = eskul::where('leader_id', $request->id)->first();
            if ($eskulEdit) {
                $eskulEdit->leader_id = 0;
                $eskulEdit->update();
            }
        }

        if ($request->leader_eskul_id) {
            $eskul = eskul::find($request->leader_eskul_id);
            if ($eskul && ($eskul->leader_id != null && $eskul->leader_id != '0')) {
                return response()->json(['data' => [], 'message' => 'This Organization Alredy Have Leader', 'status' => 422]);
            }
        }

        $imagePath = $user->profile_image; // Keep the current profile image path
        if ($request->hasFile('profile_image')) {
            // Save the new file and update the path
            $imagePath = $request->file('profile_image')->store('profiles', 'public');

            // Optionally, delete the old image if necessary
            if ($user->profile_image) {
                Storage::disk('public')->delete($user->profile_image);
            }
        }

        // Update user details
        $user->update([
            'name' => $request->username,
            'role' => $request->leader_eskul_id != 0 ? 'Leader' : 'Member',
            'profile_image' => $imagePath,
            'email' => $request->email,
            'password' => $request->password ? Hash::make($request->password) : $user->password,
        ]);

        // Update leader eskul if provided
        if ($request->leader_eskul_id && ($request->leader_eskul_id != 0 || $request->leader_eskul_id != null)) {
            $eskul = eskul::where('id', $request->leader_eskul_id)->first();
            if ($eskul) {
                $eskul->leader_id = $user->id;
                $eskul->update();
            }
        }

        return response()->json(['message' => 'User updated successfully', 'data' => $user]);
    }


    public function login(Request $request)
    {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid login credentials'], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json(['token' => $token, 'data' => $user]);
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
