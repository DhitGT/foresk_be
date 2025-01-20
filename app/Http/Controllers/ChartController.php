<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\EskulKas;
use App\Models\EskulMember;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    //
    public function getChartData()
    {
        try {
            // Fetch EskulKas with related Eskul
            $kas = EskulKas::with([
                'eskul' => function ($query) {
                    $query->select("*");
                }
            ])->get();

            // Fetch EskulMember with Eskul and count members for each Eskul
            $member = eskul::withCount('eskulMembers')->get();

            return response()->json(['kas' => $kas, 'member' => $member]);
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()], 500);
        }


    }
}
