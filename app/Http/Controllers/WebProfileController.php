<?php

namespace App\Http\Controllers;

use App\Models\Instansi;
use App\Models\instansi_web_page;
use Illuminate\Http\Request;
use App\Models\InstansiWebPage;
use Illuminate\Support\Facades\Auth;

class WebProfileController extends Controller
{
    public function store(Request $request)
    {
        // Validate incoming request
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'img_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048', // Make image optional

            'badge.*.name' => 'string|max:50',
            'badge.*.color' => 'string|max:7', // e.g. '#A7F3D0'
        ]);

        // Handle file upload if present
        $imagePath = null;
        if ($request->hasFile('img_profile')) {
            // Save the file and get the path
            $imagePath = $request->file('img_profile')->store('profiles', 'public');
        }

        // Get the currently authenticated user
        $user = Auth::user();

        // Find the associated instansi
        $instansi = instansi::where('owner_id', $user->id)->firstOrFail();

        // Find or create the web profile for the instansi
        $webProfile = instansi_web_page::where('instansi_id', $instansi->id)->first();

        if ($webProfile) {
            // Update the existing web profile
            $webProfile->description = $validatedData['description'];
            if ($imagePath) {
                $webProfile->img_profile = $imagePath; // Update the image if new one is uploaded
            }

            if ($request['badge']) {
                $webProfile->badge = $request['badge']; // Update the badge
            } else {
                $webProfile->badge = json_encode([]);
            }
            $webProfile->save();

            return response()->json([
                'message' => 'Web profile updated successfully!',
                'data' => $webProfile,
            ], 200);
        } else {
            // Create a new web profile
            $webProfile = new instansi_web_page();
            $webProfile->description = $validatedData['description'];
            $webProfile->instansi_id = $instansi->id;
            $webProfile->img_profile = $imagePath; // Save image path if available
            $webProfile->badge = json_encode($validatedData['badge']); // Convert badge array to JSON
            $webProfile->save();

            return response()->json([
                'message' => 'Web profile created successfully!',
                'data' => $webProfile,
            ], 201);
        }
    }
}
