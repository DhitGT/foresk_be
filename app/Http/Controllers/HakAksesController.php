<?php

namespace App\Http\Controllers;


use App\Models\UserHakAkses;
use Illuminate\Http\Request;
use App\Models\MasterHakAkses;
use Illuminate\Support\Facades\Auth;

class HakAksesController extends Controller
{
    public function getMasterHakAkses(Request $request)
    {
        $user = Auth::user();

        if ($user->role != 'manager') {
            return response()->json(['message' => 'no access']);
        }

        $hakAkses = MasterHakAkses::get();
        return response()->json(['data' => $hakAkses]);

    }

    public function updateHakAkses(Request $request)
    {
        $userId = $request->user_id;
        $hakAksesKode = $request->hak_akses_kode;
        $flag = $request->flag;

        if ($flag == 'add') {
            $userHakAkses = UserHakAkses::where('userId', $userId)
                ->where('hakAksesKode', $hakAksesKode)
                ->first();

            if (!$userHakAkses) {
                UserHakAkses::create([
                    'userId' => $userId,
                    'hakAksesKode' => $hakAksesKode
                ]);

                return response()->json(['message' => 'Hak akses added successfully']);
            } else {
                return response()->json(['message' => 'Hak akses already exists'], 400);
            }
        } else if ($flag == 'remove') {
            $userHakAkses = UserHakAkses::where('userId', $userId)
                ->where('hakAksesKode', $hakAksesKode)
                ->first();

            if ($userHakAkses) {
                $userHakAkses->delete();

                return response()->json(['message' => 'Hak akses removed successfully']);
            } else {
                return response()->json(['message' => 'Hak akses not found'], 404);
            }
        } else {
            return response()->json(['message' => 'Invalid flag'], 400);
        }
    }
}
