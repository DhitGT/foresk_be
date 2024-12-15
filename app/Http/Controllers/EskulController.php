<?php

namespace App\Http\Controllers;

use App\Models\Eskul;
use App\Models\eskul_web_page;
use App\Models\EskulKas;
use App\Models\Instansi;
use App\Models\instansi_web_page;
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
            'logo' => 'image|mimes:jpeg,png,jpg,gif|max:6048', // Make logo field optional for updates
            'badge' => 'required|string|max:255',
            'gen' => 'required|string|max:255',
            'custom_domain_name' => 'required|string|max:255',
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
            if ($request->custom_domain_name) {
                $eskulwp = eskul_web_page::where('eskul_id', $request->id)->first();
                $eskulwp->custom_domain_name = $request->custom_domain_name;
                $eskulwp->update();
            }

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
        $eskulData = Eskul::create($validated);

        eskul_web_page::create([
            'instansi_id' => $instansi->id,
            'eskul_id' => $eskulData->id,
            'custom_domain_name' => $request->custom_domain_name
        ]);

        EskulKas::create([
            'instansi_id' => $instansi->id,
            'eskul_id' => $eskulData->id,
            'total' => 0
        ]);

        // Save new Eskul to the database

        return response()->json(['message' => 'Eskul created successfully!', 'eskuldata' => $eskulData], 201);
    }

    public function getEskulInstansi(Request $request)
    {
        $user = Auth::user();

        // Find the instansi for the logged-in user
        $instansi = Instansi::where('owner_id', $user->id)->first();

        if (!$instansi) {
            return response()->json(['message' => 'Institution not found'], 404);
        }

        $nowYear = Carbon::now()->year; // Get the current year

        // Build the eskul query
        $eskulQuery = Eskul::where('eskuls.instansi_id', $instansi->id)
            ->select(
                'eskuls.*',
                \DB::raw('(SELECT COUNT(*) FROM eskul_members WHERE eskul_members.eskul_id = eskuls.id) as total_member'),
                \DB::raw('(SELECT COUNT(*) FROM eskul_achivements acv WHERE acv.eskul_id = eskuls.id) as total_achievement'),
                \DB::raw("(SELECT COUNT(*) FROM eskul_achivements acv WHERE acv.eskul_id = eskuls.id and acv.year = $nowYear) as total_achievement_year"),
                \DB::raw('(SELECT about_desc FROM eskul_web_pages WHERE eskul_web_pages.eskul_id = eskuls.id) as about'),
                \DB::raw('(SELECT COALESCE(total, 0) FROM eskul_kas ekas WHERE ekas.eskul_id = eskuls.id) as total_kas'),
                \DB::raw('(SELECT name FROM users WHERE users.id = eskuls.leader_id LIMIT 1) as leader_name'),
                \DB::raw('(SELECT custom_domain_name FROM eskul_web_pages WHERE eskul_web_pages.eskul_id = eskuls.id LIMIT 1) as custom_domain_name ')
            );

        // If not fetching trash, exclude soft-deleted records
        if ($request->isTrash !== true) {
            $eskulQuery->whereNull('eskuls.deleted_at');
        } else {
            $eskulQuery->whereNotNull('eskuls.deleted_at');
        }

        // Handle search
        if ($request->has('search')) {
            $eskulQuery->leftJoin('users as u', 'u.id', 'eskuls.leader_id');
            $eskulQuery->leftJoin('eskul_web_pages as ewp', 'ewp.eskul_id', 'eskuls.id');
            $eskulQuery->where(function ($query) use ($request) {
                $query->where('eskuls.name', 'like', '%' . $request->search . '%')
                    ->orWhere('eskuls.badge', 'like', '%' . $request->search . '%')
                    ->orWhere('ewp.about_desc', 'like', '%' . $request->search . '%')
                    ->orWhere('u.name', 'like', '%' . $request->search . '%');
            });
        }

        // Fetch the results
        $eskul = $eskulQuery->get();

        // Return the data
        return response()->json(['data' => $eskul]);
    }
    public function getEskulInstansiPublic(Request $request)
    {

        $instansiwp = instansi_web_page::where('custom_domain_name', $request->custom_domain_name)->first();



        // Find the instansi for the logged-in user
        $instansi = Instansi::where('id', $instansiwp->instansi_id)->first();

        if (!$instansi) {
            return response()->json(['message' => 'Institution not found'], 404);
        }

        $nowYear = Carbon::now()->year; // Get the current year

        // Build the eskul query
        $eskulQuery = Eskul::where('eskuls.instansi_id', $instansi->id)
            ->select(
                'eskuls.*',
                \DB::raw('(SELECT COUNT(*) FROM eskul_members WHERE eskul_members.eskul_id = eskuls.id) as total_member'),
                \DB::raw('(SELECT COUNT(*) FROM eskul_achivements acv WHERE acv.eskul_id = eskuls.id) as total_achievement'),
                \DB::raw("(SELECT COUNT(*) FROM eskul_achivements acv WHERE acv.eskul_id = eskuls.id and acv.year = $nowYear) as total_achievement_year"),
                \DB::raw('(SELECT about_desc FROM eskul_web_pages WHERE eskul_web_pages.eskul_id = eskuls.id) as about'),
                \DB::raw('(SELECT COALESCE(total, 0) FROM eskul_kas ekas WHERE ekas.eskul_id = eskuls.id) as total_kas'),
                \DB::raw('(SELECT name FROM users WHERE users.id = eskuls.leader_id LIMIT 1) as leader_name'),
                \DB::raw('(SELECT custom_domain_name FROM eskul_web_pages WHERE eskul_web_pages.eskul_id = eskuls.id LIMIT 1) as custom_domain_name ')
            );

        // If not fetching trash, exclude soft-deleted records
        if ($request->isTrash !== true) {
            $eskulQuery->whereNull('eskuls.deleted_at');
        } else {
            $eskulQuery->whereNotNull('eskuls.deleted_at');
        }

        // Handle search
        if ($request->has('search')) {
            $eskulQuery->leftJoin('users as u', 'u.id', 'eskuls.leader_id');
            $eskulQuery->leftJoin('eskul_web_pages as ewp', 'ewp.eskul_id', 'eskuls.id');
            $eskulQuery->where(function ($query) use ($request) {
                $query->where('eskuls.name', 'like', '%' . $request->search . '%')
                    ->orWhere('eskuls.badge', 'like', '%' . $request->search . '%')
                    ->orWhere('ewp.about_desc', 'like', '%' . $request->search . '%')
                    ->orWhere('u.name', 'like', '%' . $request->search . '%');
            });
        }

        // Fetch the results
        $eskul = $eskulQuery->get();

        // Return the data
        return response()->json(['data' => $eskul]);
    }
}
