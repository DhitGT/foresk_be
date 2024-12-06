<?php

namespace App\Http\Controllers;

use App\Models\EskulMember;
use App\Models\instansi;
use App\Models\WebInstansiFollower;
use Illuminate\Http\Request;
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



}
