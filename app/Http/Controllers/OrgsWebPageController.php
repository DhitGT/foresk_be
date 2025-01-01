<?php

namespace App\Http\Controllers;

use App\Models\eskul_web_page_galery;
use web;
use Auth;
use App\Models\eskul;
use Illuminate\Http\Request;
use App\Models\eskul_web_page;
use App\Models\eskul_activities;
use App\Models\eskul_activities_galery;
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
    public function storeActivitiesDesc(Request $request)
    {
        // Validate the input
        $request->validate([
            'eskul_id' => 'required|exists:eskuls,id',
            'description' => 'required|string|max:5000',
        ]);

        try {
            // Find the Eskul and its Web Page
            $eskul = eskul::findOrFail($request->eskul_id);
            $ewp = eskul_web_page::where('eskul_id', $eskul->id)->first();

            // If the Eskul Web Page does not exist, return an error
            if (!$ewp) {
                return response()->json(['message' => 'Eskul Web Page not found.']);
            }

            // Update the description
            $ewp->activities_desc = $request->description;
            $ewp->save();

            // Return a success response with the updated data
            return response()->json([
                'message' => 'Activities description updated successfully!',
                'data' => $ewp,
            ]);

        } catch (\Exception $e) {
            // Handle unexpected errors
            return response()->json([
                'message' => 'An error occurred while updating the description.',
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function storeActivitiesEskulItem(Request $request)
    {
        $user = Auth::user();
        // Retrieve the Eskul
        $eskul = eskul::where('leader_id', $user->id)->first();
        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found.'], 404);
        }

        // Check if the request is for editing
        if ($request->is_edit == 'true') {
            // Retrieve the existing activity
            $eskulactv = eskul_activities::where('id', $request->activity_id)->first();
            if (!$eskulactv) {
                return response()->json(['message' => 'Activity not found.'], 404);
            }

            // Update fields
            if ($request->hasFile('cover_image')) {
                // Delete old cover image if it exists
                if ($eskulactv->cover_image && \Storage::disk('public')->exists($eskulactv->cover_image)) {
                    \Storage::disk('public')->delete($eskulactv->cover_image);
                }

                // Store the new cover image
                $file = $request->file('cover_image');
                $filePathCover = $file->store('cover_image', 'public');
                $eskulactv->cover_image = $filePathCover;
            }

            $eskulactv->update([
                'gen' => $request->gen,
                'date' => $request->date,
                'location' => $request->location,
                'title' => $request->title,
                'description' => $request->description,
            ]);

            // Handle gallery updates
            if ($request->gallery) {
                // Delete only images specified in the request
                if ($request->has('deletedImages')) {
                    foreach ($request->deletedImages as $deletedImage) {
                        $galleryItem = eskul_activities_galery::where('eskul_activities_id', $eskulactv->id)
                            ->where('id', $deletedImage)
                            ->first();

                        if ($galleryItem && \Storage::disk('public')->exists($galleryItem->image)) {
                            \Storage::disk('public')->delete($galleryItem->image);
                            $galleryItem->delete();
                        }
                    }
                }

                // Add new gallery images
                if ($request->has('gallery')) {
                    foreach ($request->gallery as $item) {
                        if (isset($item['imageUpload'])) {
                            $filePath = $item['imageUpload']->store('gallery', 'public');
                            eskul_activities_galery::create([
                                'eskul_activities_id' => $eskulactv->id,
                                'image' => $filePath,
                            ]);
                        }
                    }
                }
            }

            return response()->json(['message' => 'Activity updated successfully!']);
        }

        // If not edit, create a new activity
        $file = $request->file('cover_image');
        $filePathCover = $file->store('cover_image', 'public');
        $eskulactv = eskul_activities::create([
            'instansi_id' => $eskul?->instansi_id,
            'eskul_id' => $eskul?->id,
            'cover_image' => $filePathCover,
            'gen' => $request->gen,
            'date' => $request->date,
            'location' => $request->location,
            'title' => $request->title,
            'description' => $request->description,
        ]);

        foreach ($request->gallery as $item) {
            if (isset($item['imageUpload'])) {
                $filePath = $item['imageUpload']->store('gallery', 'public');
                eskul_activities_galery::create([
                    'eskul_activities_id' => $eskulactv->id,
                    'image' => $filePath,
                ]);
            }
        }

        return response()->json(['message' => 'Activity created successfully!']);
    }


    public function storeGallery(Request $request)
    {
        $user = Auth::user();

        $eskul = eskul::where('leader_id', $user->id)->first();
        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found.'], 404);
        }

        $ewp = eskul_web_page::where('eskul_id', $request->eskul_id)->first();


        // Delete only images specified in the request
        if ($request->has('deletedImages')) {
            foreach ($request->deletedImages as $deletedImage) {
                $galleryItem = eskul_web_page_galery::where('eskul_web_page_id', $ewp->id)
                    ->where('id', $deletedImage)
                    ->first();

                if ($galleryItem && \Storage::disk('public')->exists($galleryItem->image)) {
                    \Storage::disk('public')->delete($galleryItem->image);
                    $galleryItem->delete();
                }
            }
        }

        // Add new gallery images
        if ($request->has('gallery')) {
            foreach ($request->gallery as $item) {
                if (isset($item['imageUpload'])) {
                    $filePath = $item['imageUpload']->store('gallery', 'public');
                    eskul_web_page_galery::create([
                        'eskul_id' => $request->eskul_id,
                        'eskul_web_page_id' => $ewp->id,
                        'image' => $filePath,
                    ]);
                }
            }
        }


        return response()->json([
            'message' => 'success!',
            'data' => [],
        ]);
    }
    public function getEskulWebPage(Request $request)
    {
        $eskulWp = eskul::where('id', $request->eskul_id)
            ->with([
                'webPages' => function ($query) {
                    $query->select('*')->with([
                        'webPageGalery' => function ($query) {
                            $query->select('*');
                        },
                        'webPageActivities' => function ($query) {
                            $query->select('*')->with([
                                'webPageActivitiesGalery' => function ($query) {
                                    $query->select('*');
                                }
                            ]);
                        },
                    ]); // Fetch all fields from eskul_achievements
                },
            ])->first();
        return response()->json([
            'message' => 'success!',
            'data' => $eskulWp,
        ]);
    }
    public function getEskulWebPageUrl(Request $request)
    {
        $ewp = eskul_web_page::where("custom_domain_name", $request->cdn)->first();

        $eskulWp = eskul::where('id', $ewp->eskul_id)
            ->with([
                'webPages' => function ($query) {
                    $query->select('*')->with([
                        'webPageGalery' => function ($query) {
                            $query->select('*');
                        },
                        'webPageActivities' => function ($query) {
                            $query->select('*')->with([
                                'webPageActivitiesGalery' => function ($query) {
                                    $query->select('*');
                                }
                            ]);
                        },
                    ]); // Fetch all fields from eskul_achievements
                },
            ])->withCount('eskulMembers')->first();
        return response()->json([
            'message' => 'success!',
            'data' => $eskulWp,
        ]);
    }
}
