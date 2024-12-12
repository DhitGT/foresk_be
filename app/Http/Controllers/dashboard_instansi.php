<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\instansi;
use App\Models\EskulMember;
use App\Models\MasterHakAkses;
use App\Models\User;
use App\Models\UserHakAkses;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use App\Models\WebInstansiFollower;
use Illuminate\Support\Facades\Auth;
use App\Models\eskul_report_activity;

class dashboard_instansi extends Controller
{
    //
    public function index(Request $request)
    {
        return;
    }
    public function getActivityReport(Request $request)
    {
        $data = eskul_report_activity::get();
        $length = $data->count();
        return response()->json(['data' => $data, 'report_length' => $length]);
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
        $eskulQuery = Eskul::where('instansi_id', $instansi->id)
            ->select(
                'eskuls.*',
                \DB::raw('(SELECT COUNT(*) FROM eskul_members WHERE eskul_members.eskul_id = eskuls.id) as total_member'),
                \DB::raw('(SELECT COUNT(*) FROM eskul_achivements acv WHERE acv.eskul_id = eskuls.id) as total_achievement'),
                \DB::raw("(SELECT COUNT(*) FROM eskul_achivements acv WHERE acv.eskul_id = eskuls.id and acv.year = $nowYear) as total_achievement_year"),
                \DB::raw('(SELECT about_desc FROM eskul_web_pages WHERE eskul_web_pages.eskul_id = eskuls.id) as about'),
                \DB::raw('(SELECT COALESCE(total, 0) FROM eskul_kas ekas WHERE ekas.eskul_id = eskuls.id) as total_kas'),
                \DB::raw('(SELECT name FROM users WHERE users.id = eskuls.leader_id LIMIT 1) as leader_name')
            );

        // If not fetching trash, exclude soft-deleted records
        if ($request->isTrash !== true) {
            $eskulQuery->whereNull('eskuls.deleted_at');
        } else {
            $eskulQuery->whereNotNull('eskuls.deleted_at');

        }

        // Fetch the results
        $eskul = $eskulQuery->get();

        // Return the data
        return response()->json(['data' => $eskul]);
    }

    public function getProfileInfo(Request $request)
    {
        $user = Auth::user();


        $data = Instansi::where('instansis.owner_id', $user->id)
            ->leftJoin('instansi_web_pages', 'instansi_web_pages.instansi_id', '=', 'instansis.id')
            ->leftJoin('users', 'users.id', '=', 'instansis.owner_id')
            ->select(
                'users.name as owner_name',
                'instansis.*',
                'instansi_web_pages.*',
                'instansis.id as instansi_id',
                'instansi_web_pages.id as instansi_wp_id',
                // Subquery to get total members
                \DB::raw('(SELECT COUNT(*) FROM eskul_members WHERE eskul_members.instansi_id = instansis.id) as total_member'),
                // Subquery to get total followers
                \DB::raw('(SELECT COUNT(*) FROM web_instansi_followers WHERE web_instansi_followers.instansi_id = instansis.id) as total_followers'),
                \DB::raw('(SELECT COUNT(*) FROM eskuls WHERE eskuls.instansi_id = instansis.id) as total_organization'),
                \DB::raw('(SELECT COUNT(*) FROM eskul_achivements WHERE eskul_achivements.instansi_id = instansis.id) as total_achivement')
            )
            ->get();

        $length = $data->count();
        return response()->json(['data' => $data, 'isFound' => $length]);
    }
    public function getUserInstansi(Request $request)
    {
        $user = Auth::user();

        $instansi = instansi::where('owner_id', $user->id)->first();

        $masterHakAkses = MasterHakAkses::all();

        $data = User::select('users.*', 'eskuls.id as eskul_id', 'eskuls.name as eskul_name') // Select all user fields
            ->join('instansi_users', 'users.id', '=', 'instansi_users.user_id') // Join with pivot table
            ->leftJoin('eskuls', 'eskuls.leader_id', '=', 'users.id')
            ->where('instansi_users.instansi_id', '=', $instansi['id']) // Filter by instansi_id
            ->get();

        $result = [];

        foreach ($data as $user) {
            $access = [];

            foreach ($masterHakAkses as $hakAkses) {
                $userHakAkses = UserHakAkses::where('userId', $user->id)
                    ->where('hakAksesKode', $hakAkses->Kode)
                    ->first();

                $access[] = [
                    'kode' => $hakAkses->Kode,
                    'name' => $hakAkses->Nama,
                    'value' => $userHakAkses ? true : false
                ];
            }

            $result[] = [
                'id' => $user->id,
                'profile_image' => $user->profile_image,
                'role' => $user->role,
                'email' => $user->email,
                'name' => $user->name,
                'eskul_id' => $user->eskul_id,
                'eskul_name' => $user->eskul_name,
                'access' => $access
            ];
        }

        $length = count($result);
        return response()->json(['data' => $result, 'isFound' => $length]);
    }



}
