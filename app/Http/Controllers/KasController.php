<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\eskul_web_page;
use App\Models\EskulKas;
use App\Models\EskulKasLog;
use App\Models\instansi;
use Illuminate\Http\Request;

class KasController extends Controller
{
    //
    public function getEskulKas(Request $request)
    {

        $eskul_id = $request->eskul_id;

        if ($eskul_id == '') {
            $ewp = eskul_web_page::where('custom_domain_name', $request->cdn)->first();
            $eskul_id = $ewp->eskul_id;
        }



        $kas = eskul::where('id', $eskul_id)

            ->select(
                '*'
            )
            ->with([
                'kas' => function ($query) {
                    $query->select('*'); // Fetch all fields from eskul_achievements
                },
                'kasLogs' => function ($query) {
                    $query->select('*');
                },

            ])->first();

        return response()->json(['data' => $kas]);
    }
    public function storeKas(Request $request)
    {
        $eskul = eskul::where('id', $request->eskul_id)->first();
        $type = $request->flag;
        $amount = $request->amount;
        $description = $request->description;
        $eskulKas = EskulKas::where('eskul_id', $eskul->id)->first();
        $instansi = instansi::where('id', $eskul->instansi_id)->first();
        $totalKasBefore = $eskulKas->total;

        if ($type == 'expense' && $totalKasBefore < $amount && $totalKasBefore != $amount && $amount < 1) {
            return response()->json(['message' => 'cannot expense more than ' . $totalKasBefore]);
        }

        $kas = EskulKasLog::create([
            'eskul_id' => $eskul->id,
            'eskul_kas_id' => $eskulKas->id,
            'instansi_id' => $instansi->id,
            'amount' => $amount,
            'flag' => $type,
            'description' => $description
        ]);

        if ($type == 'income') {
            $eskulKas->total = $totalKasBefore += $amount;
        } else if ($type == 'expense') {
            if ($totalKasBefore > $amount || $totalKasBefore == $amount) {
                if ($amount > 0) {
                    $eskulKas->total = $totalKasBefore -= $amount;
                }
            } else {
                return response()->json(['message' => 'cannot expense more than ' . $totalKasBefore]);
            }
        }

        $eskulKas->update();


        return response()->json(['data' => $kas]);
    }
}
