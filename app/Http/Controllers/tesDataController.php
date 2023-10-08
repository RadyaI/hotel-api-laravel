<?php

namespace App\Http\Controllers;

use App\Models\tesData;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class tesDataController extends Controller
{
    function get()
    {
        $get = tesData::get();
        if ($get->isEmpty()) {
            return response()->json('kosong');
        }
        return response()->json($get);
    }

    function createData(Request $req)
    {
        $validasi = Validator::make($req->all(), [
            'nama' => 'required|min:2',
            'email' => 'required',
        ]);

        if ($validasi->fails()) {
            return response()->json($validasi->errors()->toJson());
        }

        $masukinData = DB::table('tesDataNama')->insert([
            'nama' => $req->nama,
            'email' => $req->email
        ]);
        $returnData = [
            'nama' => $req->nama,
            'email' => $req->email,
        ];

        if ($masukinData) {
            return response()->json($returnData);
        }
    }

    function deleteData($id)
    {
        $delete = tesData::where('no', $id)->delete();
        return response()->json('berhasil');
    }
}
