<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardOrganizationController extends Controller
{
    //
    function getProfileInfo(Request $request)
    {
        $user = Auth::user();


        $data = Eskul::where('eskuls.leader_id', $user->id)
            ->leftJoin('users', 'users.id', '=', 'eskuls.leader_id')
            ->select(
                'users.name as leader_name',
                'eskuls.*',
                'eskuls.id as eskul_id',
                \DB::raw('(SELECT COUNT(*) FROM eskul_members WHERE eskul_members.eskul_id = eskuls.id) as total_member'),
                \DB::raw('(SELECT COUNT(*) FROM eskul_achivements WHERE eskul_achivements.eskul_id = eskuls.id) as total_achivement')
            )
            ->with([
                'achievements' => function ($query) {
                    $query->select('*'); // Fetch all fields from eskul_achievements
                },
                'webPages' => function ($query) {
                    $query->select('*'); // Fetch all fields from eskul_web_pages
                },
                'kas' => function ($query) {
                    $query->select('*'); // Fetch all fields from eskul_kas
                },
                'instansi' => function ($query) {
                    $query->select('*')->with([
                        'instansi_web_page' => function ($query) {
                            $query->select('*'); // Fetch all fields from eskul_kas
                        }
                    ]); // Fetch all fields from eskul_kas
                }
            ])
            ->first();



        $length = $data->count();
        return response()->json(['data' => $data, 'isFound' => $length]);
    }
}
