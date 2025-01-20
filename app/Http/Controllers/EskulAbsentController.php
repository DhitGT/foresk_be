<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\eskul_web_page;
use App\Models\EskulAbsensi;
use App\Models\EskulMember;
use Date;
use Illuminate\Http\Request;
use App\Models\MasterEskulAbsensi;
use Illuminate\Support\Facades\Auth;

class EskulAbsentController extends Controller
{
    //

    public function GetEskulAbsen(Request $request)
    {
        try {
            $data = MasterEskulAbsensi::query(); // Initialize query for MasterEskulAbsensi

            if ($request->eskul_cdn) {
                // Find the eskul_web_page record
                $ewp = eskul_web_page::where('custom_domain_name', $request->eskul_cdn)->first();

                // Check if the record exists and apply filter
                if ($ewp) {
                    $data->where('eskul_id', $ewp->eskul_id);
                } else {
                    return response()->json(['data' => null, 'isFound' => 0]); // Return not found response
                }
            } else {
                $user = Auth::user();
                $eskul = eskul::where('leader_id', $user->id)->first();

                if ($eskul) {
                    $data->where('eskul_id', $eskul->id);
                } else {
                    return response()->json(['data' => null, 'isFound' => 0]); // Return not found response
                }
            }

            // Fetch the data with relationships
            $result = $data->with([
                'eskulReportActivity' => function ($query) {
                    $query->select('*'); // Fetch all fields
                },
                'eskul' => function ($query) {
                    $query->select('*'); // Fetch all fields
                },
            ])->get();

            $length = $result->count();

            return response()->json(['data' => $result, 'isFound' => $length]); // Return the result
        } catch (\Throwable $th) {
            return response()->json(['error' => $th->getMessage()], 500); // Return the error message with a 500 status code
        }
    }

    public function GetEskulAbsenByCode(Request $request)
    {
        $data = MasterEskulAbsensi::where('absent_code', $request->absent_code)

            ->select(
                '*'
            )
            ->with([
                'eskulReportActivity' => function ($query) {
                    $query->select('*'); // Fetch all fields from eskul_achievements
                },
                'eskulAbsen' => function ($query) {
                    $query->select('*')->with([
                        'member' => function ($query) {
                            $query->select('*'); // Fetch all fields from eskul_achievements
                        },
                    ]); // Fetch all fields from eskul_kas
                },

            ])
            ->first();



        $length = $data->count();
        return response()->json(['data' => $data, 'isFound' => $length]);
    }
    public function editAbsen(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',

        ]);

        $eskulAbsen = EskulAbsensi::where('id', $request->id)->first();

        $eskulAbsen->keterangan = $request->keterangan;

        $eskulAbsen->update();

        return response()->json(['data' => $eskulAbsen]);
    }
    public function deleteAbsen(Request $request)
    {

        $validated = $request->validate([
            'id' => 'required|string|max:255',
        ]);

        $eskulAbsen = EskulAbsensi::where('id', $request->id)->first();
        $eskulAbsen->delete();

        return response()->json(['data' => [], 'message' => 'success']);
    }
    public function getUserByName(Request $request)
    {
        $user = Auth::user();
        $eskul = eskul::where("leader_id", $user->id)->first();
        $names = EskulMember::where('name', 'LIKE', '%' . $request->name . '%')->where('eskul_id', $eskul->id)->get();
        return response()->json(['data' => $names, 'message' => 'success']);
    }
    public function storeAbsen(Request $request)
    {
        $validated = $request->validate([
            'member_id' => 'required|string|max:255',
            'eskul_id' => 'required|string|max:255',
            'absent_code' => 'required|string|max:255',
            'keterangan' => 'required|string|max:255',

        ]);

        $eskulAbsen = EskulAbsensi::create(
            [
                'member_id' => $request->member_id,
                'eskul_id' => $request->eskul_id,
                'absent_code' => $request->absent_code,
                'keterangan' => $request->keterangan,
                'date' => Date::now(),
            ]
        );

        return response()->json(['data' => $eskulAbsen]);
    }
}
