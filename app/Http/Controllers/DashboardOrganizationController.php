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

        $eskulMemberData = EskulMember::where('eskul_id', $eskulData->id)
            ->paginate($request->get('per_page', 5));

        return response()->json([
            'data' => $eskulMemberData->items(),
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

        $request->validate([
            'name' => 'required|string|max:255',
            'gen' => 'required|string|max:255',
        ]);

        $eskulData = eskul::where('leader_id', $user->id)->first();

        $eskulMember = EskulMember::create([
            'name' => $request->name,
            'gen' => $request->gen,
            'eskul_id' => $eskulData->id,
            'instansi_id' => $eskulData->instansi_id,
        ]);

        return response()->json([
            'message' => 'Eskul Member created successfully.',
            'data' => $eskulMember,
        ]);
    }

    public function updateEskulMember(Request $request)
    {
        $user = Auth::user();
        $this->checkAccess();

        $request->validate([
            'name' => 'required|string|max:255',
            'gen' => 'required|string|max:255',
        ]);

        $eskulMember = EskulMember::findOrFail($request->id);
        $eskulData = eskul::where('leader_id', $user->id)->first();

        if ($eskulMember->eskul_id !== $eskulData->id) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        $eskulMember->update([
            'name' => $request->name,
            'gen' => $request->gen,
        ]);

        return response()->json([
            'message' => 'Eskul Member updated successfully.',
            'data' => $eskulMember,
        ]);
    }

    public function deleteEskulMember(Request $request)
    {
        $user = Auth::user();
        $this->checkAccess();

        $eskulMember = EskulMember::findOrFail($request->id);
        $eskulData = eskul::where('leader_id', $user->id)->first();

        if ($eskulMember->eskul_id !== $eskulData->id) {
            return response()->json(['message' => 'forbidden'], 403);
        }

        $eskulMember->delete();

        return response()->json([
            'message' => 'Eskul Member deleted successfully.',
        ]);
    }
    public function getProfileInfo(Request $request)
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
                    $query->select('*');
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
                    ]);
                },
                'kas' => function ($query) {
                    $query->select('*');
                },
                'instansi' => function ($query) {
                    $query->select('*')->with([
                        'instansi_web_page' => function ($query) {
                            $query->select('*');
                        }
                    ]);
                }
            ])
            ->first();

        $length = $data->count();
        return response()->json(['data' => $data, 'isFound' => $length]);
    }
}
