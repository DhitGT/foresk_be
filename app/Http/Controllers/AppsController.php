<?php

namespace App\Http\Controllers;

use App\Models\eskul;
use App\Models\EskulMember;
use App\Models\instansi;
use Illuminate\Http\Request;

class AppsController extends Controller
{
    //
    public function GetAppsStats()
    {
        $instansiTotal = instansi::count();
        $eskulTotal = eskul::count();
        $userTotal = EskulMember::count();

        return response()->json(['instansi_total' => $instansiTotal, 'eskul_total' => $eskulTotal, 'user_total' => $userTotal]);
    }
}
