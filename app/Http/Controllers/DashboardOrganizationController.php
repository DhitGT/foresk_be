<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\EskulMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardOrganizationController extends Controller
{
    private function checkAccess()
    {
        $user = Auth::user();
        $userDb = User::findOrFail($user->id);
        if ($userDb->role != "Leader") {
            return response()->json(['message' => 'forbidden'], 403);

        }
    }
    public function getEskulMembers(Request $request)
    {
        $user = Auth::user();
        $this->checkAccess();

        $eskulData = eskul::where('leader_id', $user->id)->first();

        // Paginate the members, with a default of 10 items per page
        $eskulMemberData = EskulMember::where('eskul_id', $eskulData->id)
            ->paginate($request->get('per_page', 5)); // You can specify a different number of items per page using the `per_page` parameter

        return response()->json([
            'data' => $eskulMemberData->items(),  // Return the paginated items
            'pagination' => [
                'total' => $eskulMemberData->total(),
                'current_page' => $eskulMemberData->currentPage(),
                'per_page' => $eskulMemberData->perPage(),
                'last_page' => $eskulMemberData->lastPage(),
                'from' => $eskulMemberData->firstItem(),
                'to' => $eskulMemberData->lastItem(),
            ]
        ]);
    }

    public function storeEskulMember(Request $request)
    {
        $user = Auth::user();
        $this->checkAccess();
        // Validate the request data
        $request->validate([
            'name' => 'required|string|max:255',
            'gen' => 'required|string|max:255',
        ]);

        $eskulData = eskul::where('leader_id', $user->id)->first();


        // Create a new Eskul Member
        $eskulMember = EskulMember::create([
            'name' => $request->name,
            'gen' => $request->gen,
            'eskul_id' => $eskulData->id,
            'instansi_id' => $eskulData->instansi_id,
        ]);

        // Return a success response
        return response()->json([
            'message' => 'Eskul Member created successfully.',
            'data' => $eskulMember,
        ]);
    }
    //
    function getProfileInfo(Request $request)
    {

        $user = Auth::user();
        $this->checkAccess();

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
