<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\EskulKas;
use App\Models\EskulMember;
use App\Models\instansi;
use App\Models\user;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    //
    public function getChartData()
    {
        try {
            // Fetch EskulKas with related Eskul

            $user = Auth::user();
            $instansi = instansi::where('owner_id', $user->id)->first();

            $kas = EskulKas::with([
                'eskul' => function ($query) {
                    $query->select("*");
                }
            ])->where('instansi_id', $instansi->id)->get();

            // Fetch EskulMember with Eskul and count members for each Eskul
            $member = eskul::withCount('eskulMembers')->where('instansi_id', $instansi->id)->get();

            return response()->json(['kas' => $kas, 'member' => $member]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }


    }
}
