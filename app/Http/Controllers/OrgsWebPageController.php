<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\eskul_web_page;
use Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class OrgsWebPageController extends Controller
{
    public function storeNavbarWebpage(Request $request)
    {
        $user = Auth::user();

        // Retrieve the eskul and its web page
        $eskul = eskul::where('leader_id', $user->id)->first();
        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found.'], 404);
        }

        $eskulwp = eskul_web_page::where('eskul_id', $eskul->id)->first();
        if (!$eskulwp) {
            return response()->json(['message' => 'Eskul web page not found.'], 404);
        }

        // Update navbar title if provided
        if ($request->navbar_title) {
            $eskulwp->navbar_title = $request->navbar_title;
            $eskulwp->update();
        }

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');

            // Validate the file (optional)
            $request->validate([
                'logo' => 'image|max:6048', // Ensure it's an image and max size is 2MB
            ]);

            // Delete the old logo if it exists
            if ($eskul->logo && Storage::exists($eskul->logo)) {
                Storage::delete($eskul->logo);
            }

            // Store the new logo
            $path = $file->store('logos', 'public');
            $eskul->logo = $path;
            $eskul->update();
        }

        return response()->json([
            'message' => 'success!',
            'data' => [
                'navbar_title' => $eskulwp->navbar_title,
                'logoUpload' => $path,
                'logo' => $eskul->logo ? asset('storage/' . $eskul->logo) : null,
            ],
        ]);
    }
    public function storeJumbotronWebpage(Request $request)
    {
        $user = Auth::user();

        // Retrieve the eskul and its web page
        $eskul = eskul::where('leader_id', $user->id)->first();
        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found.'], 404);
        }

        $eskulwp = eskul_web_page::where('eskul_id', $eskul->id)->first();
        if (!$eskulwp) {
            return response()->json(['message' => 'Eskul web page not found.'], 404);
        }

        // Update navbar title if provided
        if ($request->jumbotron_title) {
            $eskulwp->jumbotron_title = $request->jumbotron_title;
        }
        if ($request->jumbotron_subtitle) {
            $eskulwp->jumbotron_subtitle = $request->jumbotron_subtitle;
        }
        if ($request->form_register_link) {
            if ($request->is_form_register_link == 'true') {
                $eskulwp->form_register_link = $request->form_register_link;
            } else {
                $eskulwp->form_register_link = null;
            }
        }

        // Handle logo upload
        if ($request->hasFile('jumbotron_image')) {
            $file = $request->file('jumbotron_image');

            // Validate the file (optional)
            $request->validate([
                'jumbotron_image' => 'image|max:6048', // Ensure it's an image and max size is 2MB
            ]);

            // Delete the old logo if it exists
            if ($eskulwp->jumbotron_image && Storage::exists($eskulwp->jumbotron_image)) {
                Storage::delete($eskulwp->jumbotron_image);
            }

            // Store the new logo
            $path = $file->store('images', 'public');
            $eskulwp->jumbotron_image = $path;

        }

        $eskulwp->update();


        return response()->json([
            'message' => 'success!',
            'data' => [],
        ]);
    }
    public function storeAboutUsWebpage(Request $request)
    {
        $user = Auth::user();

        // Retrieve the eskul and its web page
        $eskul = eskul::where('leader_id', $user->id)->first();
        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found.'], 404);
        }

        $eskulwp = eskul_web_page::where('eskul_id', $eskul->id)->first();
        if (!$eskulwp) {
            return response()->json(['message' => 'Eskul web page not found.'], 404);
        }

        // Update navbar title if provided
        if ($request->about_desc) {
            $eskulwp->about_desc = $request->about_desc;
        }


        $eskulwp->update();


        return response()->json([
            'message' => 'success!',
            'data' => [],
        ]);
    }
}
