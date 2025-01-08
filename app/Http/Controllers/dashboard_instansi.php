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
    private function checkAccess()
    {
        $user = Auth::user();
        $userDb = User::findOrFail($user->id);
        if ($userDb->role != "Manager") {
            return response()->json(['message' => 'forbidden'], 403);

        }
    }
    //
    public function index(Request $request)
    {
        return;
    }
    public function getActivityReport(Request $request)
    {
        $user = Auth::user();

        $data = eskul_report_activity::get();
        $length = $data->count();
        $this->checkAccess();
        return response()->json(['data' => $data, 'report_length' => $length]);
    }





    public function getProfileInfo(Request $request)
    {
        $user = Auth::user();
        $this->checkAccess();



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
            ->first();

        if (!$data) {
            return response()->json(['data' => [], 'isFound' => 0]);
        }

        $length = $data->count();
        return response()->json(['data' => $data, 'isFound' => $length]);
    }
    public function getProfileInfoWithDomain(Request $request)
    {

        $user = Auth::user();
        // $this->checkAccess();

        $data = Instansi::join('instansi_web_pages', 'instansi_web_pages.instansi_id', '=', 'instansis.id') // Join instansi_web_pages with instansis
            ->leftJoin('users', 'users.id', '=', 'instansis.owner_id') // Join the users table
            ->where('instansi_web_pages.custom_domain_name', $request->custom_domain_name) // Filter by custom_domain_name
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
            ->first();


        $isOwner = false;
        if ($user) {
            $user->id == $data->owner_id;
        }


        $length = $data->count();
        return response()->json(['data' => $data, 'isFound' => $length > 0, 'isOwner' => $isOwner]);
    }

    public function getUserInstansi(Request $request)
    {
        $user = Auth::user();
        $this->checkAccess();

        $instansi = instansi::where('owner_id', $user->id)->first();

        $masterHakAkses = MasterHakAkses::all();

        $search = $request->input('search');

        $data = User::selectRaw('users.*, COALESCE(eskuls.id, 0) as eskul_id, eskuls.name as eskul_name')
            ->join('instansi_users', 'users.id', '=', 'instansi_users.user_id') // Join with pivot table
            ->leftJoin('eskuls', 'eskuls.leader_id', '=', 'users.id')
            ->where('instansi_users.instansi_id', '=', $instansi['id']) // Filter by instansi_id
            ->when($search, function ($query, $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('users.name', 'like', '%' . $search . '%')
                        ->orWhere('users.email', 'like', '%' . $search . '%');
                });
            })
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
