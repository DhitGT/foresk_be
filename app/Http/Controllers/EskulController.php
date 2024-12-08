<?php

namespace App\Http\Controllers;

use App\Models\Eskul;
use App\Models\Instansi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class EskulController extends Controller
{
    public function trash(Request $request)
    {
        $user = Auth::user();
        $instansi = Instansi::where('owner_id', $user->id)->firstOrFail();
        $eskul = Eskul::find($request->id);
        if ($instansi->id != $eskul->instansi_id) {
            return response()->json(['message' => 'Forbidden Action'], 403);
        }

        $eskul->deleted_at = Carbon::now();
        $eskul->update();
        return response()->json(['message' => 'Eskul updated successfully!'], 200);
    }
    public function restore(Request $request)
    {
        $user = Auth::user();
        $instansi = Instansi::where('owner_id', $user->id)->firstOrFail();
        $eskul = Eskul::find($request->id);
        if ($instansi->id != $eskul->instansi_id) {
            return response()->json(['message' => 'Forbidden Action'], 403);
        }

        $eskul->deleted_at = null;
        $eskul->update();
        return response()->json(['message' => 'Eskul updated successfully!'], 200);
    }
    public function store(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:2048', // Make logo field optional for updates
            'badge' => 'required|string|max:255',
            'gen' => 'required|string|max:255',
            'alumni' => 'required|integer',
            'instagram_url' => 'required|url|max:255',
            'whatsapp_number' => 'required|string|max:20',
        ]);

        $user = Auth::user();

        $instansi = Instansi::where('owner_id', $user->id)->first();
        if (!$instansi) {
            return response()->json(['message' => 'Institution not found'], 404);
        }

        // If an Eskul ID is passed, try to update the existing record
        if ($request->has('id')) {
            $eskul = Eskul::find($request->id);

            if (!$eskul) {
                return response()->json(['message' => 'Eskul not found'], 404);
            }

            // Update the Eskul with the validated data
            $eskul->update($validated);

            // Handle the logo file if it's uploaded during update
            if ($request->hasFile('logo')) {
                // Delete the old logo if exists
                if ($eskul->logo) {
                    Storage::delete($eskul->logo);
                }

                // Store the new logo
                $filePath = $request->file('logo')->store('logos', 'public');
                $eskul->logo = $filePath;
                $eskul->save();
            }

            return response()->json(['message' => 'Eskul updated successfully!'], 200);
        }

        // If no ID is passed, create a new Eskul
        // Store the uploaded logo file if it's provided
        if ($request->hasFile('logo')) {
            $filePath = $request->file('logo')->store('logos', 'public');
            $validated['logo'] = $filePath; // Save the file path to the database
        }

        $validated['instansi_id'] = $instansi->id;
        $validated['leader_id'] = 0;

        // Save new Eskul to the database
        Eskul::create($validated);

        return response()->json(['message' => 'Eskul created successfully!'], 201);
    }
}
