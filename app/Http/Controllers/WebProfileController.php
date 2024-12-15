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
            'description' => 'required|string',
            'custom_domain_name' => 'required|string|unique:instansi_web_pages,custom_domain_name,' . $request->id,
            'img_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,png|max:6048', // Make image optional

            'badge' => 'string',
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
            $webProfile->custom_domain_name = $validatedData['custom_domain_name'];
            $webProfile->instansi_id = $instansi->id;
            $webProfile->img_profile = $imagePath;
            if (is_array($validatedData['badge'])) {
                $webProfile->badge = json_encode($validatedData['badge']);
            } else {
                $webProfile->badge = $validatedData['badge'];
            }
            $webProfile->save();

            return response()->json([
                'message' => 'Web profile created successfully!',
                'data' => $webProfile,
            ], 201);
        }
    }
}
