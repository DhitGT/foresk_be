<?php

namespace App\Http\Controllers;

use Google\Client;
use Google_Client;
use App\Models\User;
use App\Models\eskul;

use App\Models\instansi;
use Illuminate\Support\Str;
use App\Models\InstansiUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function googleSignIn(Request $request)
    {
        $client = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]); // Your Google Client ID
        $idToken = $request->input('credential');

        try {
            // Verify the token
            $payload = $client->verifyIdToken($idToken);

            if ($payload) {
                // Extract user information
                $googleId = $payload['sub']; // Google user ID
                $email = $payload['email'];
                $name = $payload['name'];
                $profileImage = $payload['picture'] ?? null;

                // Check if the user already exists
                $user = User::where('google_id', $googleId)->orWhere('email', $email)->first();

                $imagePath = null;
                if ($profileImage) {
                    // Fetch and save the image locally
                    $imageContents = file_get_contents($profileImage);
                    $imageName = uniqid() . '.jpg'; // Generate a unique file name
                    $imagePath = 'profiles/' . $imageName; // Path inside the storage folder
                    Storage::disk('public')->put($imagePath, $imageContents);
                }

                if (!$user) {
                    // Create a new user following the same pattern as the register function
                    $user = User::create([
                        'name' => $name,
                        'profile_image' => $imagePath,
                        'role' => 'Manager', // Default role, customize as needed
                        'email' => $email,
                        'google_id' => $googleId,
                        'password' => Hash::make(Str::random(12)), // Random password for security
                    ]);

                    // Create the associated instansi
                    $instansi = Instansi::create([
                        'nama' => null, // Example naming convention
                        'description' => 'Created via Google Sign-In',
                        'owner_id' => $user->id,
                        'total_organization' => 0,
                    ]);
                } else {
                    $instansi = Instansi::where('owner_id', $user->id)->first();
                }

                // Generate a token for the user
                $token = $user->createToken('auth_token')->plainTextToken;

                return response()->json([
                    'message' => 'Successfully authenticated',
                    'token' => $token,
                    'user' => $user,
                    'instansi' => $instansi,
                ]);
            } else {
                return response()->json(['error' => 'Invalid token'], 401);
            }
        } catch (\Exception $e) {
            return response()->json(['error' => 'Token verification failed', 'details' => $e->getMessage()], 500);
        }
    }
    public function handleGoogleCallback(Request $request)
    {
        $googleClient = new Google_Client();
        $googleClient->setClientId(config('google.client_id'));
        $googleClient->setClientSecret(config('google.client_secret'));
        $googleClient->setRedirectUri(config('google.redirect_uri'));

        // Get the token sent from the frontend
        $idToken = $request->input('id_token');

        try {
            // Verify the Google ID token
            $payload = $googleClient->verifyIdToken($idToken);

            if ($payload) {
                // Check if the user already exists in the database
                $user = User::where('google_id', $payload['sub'])->first();

                // If user doesn't exist, create a new one
                if (!$user) {
                    $user = User::create([
                        'name' => $payload['name'],
                        'email' => $payload['email'],
                        'google_id' => $payload['sub'],
                    ]);
                }

                // Log the user in
                Auth::login($user);

                // Optionally, return a response with user data
                return response()->json(['message' => 'Login successful', 'user' => $user]);
            } else {
                return response()->json(['message' => 'Invalid token'], 400);
            }
        } catch (\Exception $e) {
            return response()->json(['message' => 'Token verification failed', 'error' => $e->getMessage()], 400);
        }
    }
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
    public function deleteUser(Request $request)
    {
        $userId = $request->id;
        $userRequest = Auth::user();

        // Check if the authenticated user is a Manager
        if ($userRequest->role != "Manager") {
            return response()->json(['message' => 'Forbidden', 'status' => 403], 403);
        }

        // Retrieve the user to be deleted
        $user = User::find($userId);

        if (!$user) {
            return response()->json(['message' => 'User not found', 'status' => 404], 404);
        }

        // Check if the user belongs to the same instansi
        $instansi = Instansi::where('owner_id', $userRequest->id)->first();
        $instansiUser = InstansiUser::where('instansi_id', $instansi->id)
            ->where('user_id', $user->id)
            ->first();

        if (!$instansiUser) {
            return response()->json(['message' => 'User does not belong to your instansi', 'status' => 403], 403);
        }

        // If the user is a Leader of an Eskul, reset the leader_id
        $eskul = Eskul::where('leader_id', $user->id)->first();
        if ($eskul) {
            $eskul->leader_id = '0';
            $eskul->update();
        }

        // Delete the user record and related InstansiUser entry
        $instansiUser->delete();
        $user->delete();

        return response()->json(['message' => 'User deleted successfully', 'status' => 200], 200);
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
