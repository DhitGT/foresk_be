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
        // $user = Auth::user();
        // $instansi = Instansi::where('leader_id', $user->id)->first();
        // Validate incoming request
        $validatedData = $request->validate([
            'name' => 'required|string',
            'description' => 'required|string',
            'custom_domain_name' => 'required|string|',
            'img_profile' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,png|max:6048', // Make image optional

            'badge' => 'string',
            'badge.*.name' => 'string|max:50',
            'badge.*.color' => 'string|max:7', // e.g. '#A7F3D0'
        ]);

        $checkCdn = instansi_web_page::where('custom_domain_name', $request->custom_domain_name)->count();

        if ($checkCdn != 0) {
            return response()->json([
                'message' => 'Domain Name Alredy Exist',
                'data' => [],
            ], );
        }

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

            if ($validatedData['name']) {
                $instansi->nama = $validatedData['name'];
                $instansi->update();
            }


            $webProfile->description = $validatedData['description'];
            $webProfile->custom_domain_name = $validatedData['custom_domain_name'];
            if ($imagePath) {
                $webProfile->img_profile = $imagePath;
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

            $instansi->nama = $validatedData['name'];
            $instansi->update();

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
