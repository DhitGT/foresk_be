<?php

namespace App\Http\Controllers;

use App\Models\eskul_report_activity;
use App\Models\eskul;
use App\Models\EskulAbsensi;
use App\Models\MasterEskulAbsensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class EskulReportActivityController extends Controller
{
    public function index(Request $request)
    {
        $query = eskul_report_activity::query();

        $user = Auth::user();
        $eskul = eskul::where('leader_id', $user->id)->first();


        // Apply filters
        if ($request->has('start_date')) {
            $query->where('date_start', '>=', $request->input('start_date'));
        }

        if ($request->has('end_date')) {
            $query->where('date_end', '<=', $request->input('end_date'));
        }

        $activities = $query->get();

        return response()->json($activities);
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        $eskul = eskul::where('leader_id', $user->id)->first();

        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found for the user'], 404);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'location' => 'required|string|max:255',
            'picture' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:8048',
            'description' => 'required|string',
            'date_start' => 'required|date',
            'date_end' => 'required|date',

        ]);

        // Handle file upload
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $path = $file->store('images', 'public'); // Save to storage/app/public/images
        }

        $eskulReportUUID = Str::uuid();

        $absenCode = '';
        do {
            $absenCode = strtoupper(Str::random(5));
        } while (MasterEskulAbsensi::where('absent_code', $absenCode)->exists());

        $masterAbsen = MasterEskulAbsensi::create([
            'eskul_id' => $eskul->id,
            'eskul_report_activity_id' => $eskulReportUUID,
            'absent_code' => $absenCode,
        ]);

        $activity = eskul_report_activity::create([
            'id' => $eskulReportUUID,
            'eskul_id' => $eskul->id, // Dynamically set eskul_id
            'title' => $validated['title'],
            'location' => $validated['location'],
            'picture' => $path ?? null,
            'description' => $validated['description'],
            'date_start' => $validated['date_start'],
            'date_end' => $validated['date_end'],
            'absent_code' => $absenCode,
        ]);



        return response()->json($activity, 201);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $user = Auth::user();
        $eskul = eskul::where('leader_id', $user->id)->first();

        if (!$eskul) {
            return response()->json(['message' => 'Eskul not found for the user'], 404);
        }

        $activity = eskul_report_activity::find($id);

        if (!$activity) {
            return response()->json(['message' => 'Activity not found'], 404);
        }

        $validated = $request->validate([
            'title' => 'string|max:255',
            'location' => 'string|max:255',
            'picture' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'description' => 'string',
            'date_start' => 'date',
            'date_end' => 'date',

        ]);

        // Handle file upload
        if ($request->hasFile('picture')) {
            $file = $request->file('picture');
            $path = $file->store('images', 'public');

            // Delete the old picture if exists
            if ($activity->picture) {
                Storage::disk('public')->delete($activity->picture);
            }

            $validated['picture'] = $path;
        }

        // Dynamically update eskul_id and other validated fields
        $validated['eskul_id'] = $eskul->id;

        $activity->update($validated);

        return response()->json($activity);
    }
}
