<?php

namespace App\Http\Controllers;

use App\Models\transaksi;
use App\Models\kamar;
use Illuminate\Auth\Events\Validated;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

use Illuminate\Support\Facades\Mail;
use App\Mail\EmailNotification;
use App\Mail\emailConfirmation;
use App\Mail\FeedbackMail;

class TransaksiController extends Controller
{
    public function gettransaksi()
    {
        $transaksi = transaksi::get();
        return response()->json($transaksi);
    }
    public function cekbooking($id)
    {
        // $transaksi = transaksi::where('id_transaksi', $id)
        //     ->whereIn('status', ['dipesan' , 'dikonfirmasi'])
        //     pengganti operator || 
        //     ->get();

        $cek_status = transaksi::where('id_transaksi', $id)
            ->where('status', '=', 'dipesan')
            ->select('status')->first();
        if ($cek_status) {
            return response()->json([
                'msg' => 'Pesanan belum di konfirmasi',
                'kode' => 'dikonfirmasi'
            ], 410);
        }

        $cekStatusLagi = transaksi::where('id_transaksi', $id)
            ->whereIn('status', ['dipakai', 'dibersihkan', 'selesai'])
            ->first();
        // Ternyata ini gk bisa pake get lol

        if ($cekStatusLagi) {
            return response()->json(['msg' => 'Expaired'], 500);
        }

        $transaksi = transaksi::where('id_transaksi', $id)
            ->where('status', 'dikonfirmasi')
            ->get();

        $cek = transaksi::where('id_transaksi', $id)
            ->where('status', 'dikonfirmasi')
            ->count('id_transaksi');

        if ($cek == 0) {
            return response()->json(['msg' => 'Data Not Found'], 404);
        }
        return response()->json($transaksi);
    }
    public function pilihtransaksibynama($id)
    {
        $transaksi = transaksi::where('nama_tamu', $id)->join('kamars', 'kamars.id_kamar', '=', 'transaksis.id_kamar')->first();
        return response()->json($transaksi);
    }
    public function pilihtransaksibyid($id)
    {
        $transaksi = transaksi::where('id_transaksi', $id)->join('kamars', 'kamars.id_kamar', '=', 'transaksis.id_kamar')->first();
        return response()->json($transaksi);
    }
    public function notconfirmed()
    {
        $transaksi = transaksi::where('status', 'dipesan')->get();
        return response()->json($transaksi);
    }
    public function confirmed()
    {
        $transaksi = transaksi::where('status', 'dikonfirmasi')->get();
        return response()->json([$transaksi]);
    }
    public function ongoing()
    {
        $transaksi = transaksi::where('status', 'dipakai')->get();
        return response()->json($transaksi);
    }
    public function dibersihkan()
    {
        $transaksi = transaksi::where('status', 'dibersihkan')->get();
        return response()->json($transaksi);
    }
    public function history()
    {
        $history = transaksi::where('status', 'selesai')->orderBy('id_primary_transaksi', 'desc')->get();
        if ($history->isEmpty()) {
            return response()->json(['msg' => 'Data is empty'], 404);
        }

        return response()->json($history);
    }
    public function createtransaksi(Request $req)
    {

        $validator = Validator::make($req->all(), [
            // 'nama_tamu' => 'required',
            'nama_tamu' => 'required',
            'email' => 'required',
            'checkin' => 'required',
            'checkout' => 'required',
            // 'tgl_pesan' => 'required',
            'id_kamar' => 'required',
            'jumlah_kamar' => 'required',
            // 'harga' => 'required',
            // 'status' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->tojson());
        }

        $tgl_pesan = Carbon::now();
        // $id_kamar = $req->input('id_kamar');
        // $jumlah = $req->input('jumlah_kamar');
        // $kamar = kamar::where('id_kamar',$id_kamar)->first();
        // $get_harga = $kamar -> harga; 
        // $total_harga = $get_harga * $jumlah;
        $kode = Str::random(5);

        $createtransaksi = transaksi::create([
            'id_transaksi' => $kode,
            'nama_tamu' => $req->input('nama_tamu'),
            'email' => $req->input('email'),
            'tgl_pesan' => $tgl_pesan,
            'check_in' => $req->input('checkin'),
            'check_out' => $req->input('checkout'),
            'id_kamar' => $req->input('id_kamar'),
            'id_fasilitas' => null,
            'jumlah_kamar' => $req->input('jumlah_kamar'),
            'total_harga' => $req->input('harga'),
            'status' => 'dipesan',
        ]);

        $kamar = DB::table('kamars')->where('id_kamar', $req->input('id_kamar'))->select('total_pesan')->first();
        $get = $kamar->total_pesan;
        $itung = $get + $req->input('jumlah_kamar');

        $updatestatuskamar = kamar::where('id_kamar', '=', $req->input('id_kamar'))->update([
            'status_kamar' => 'dipesan',
            'total_pesan' => $itung
        ]);
        // 2023-07-22

        if (!$updatestatuskamar) {
            return response()->json(['message' => 'Failed update kamars', 'reason' => 'kamar'], 500);
        }

        $id_transaksi = $kode;
        $nama_tamu = $req->input('nama_tamu');
        $tanggal_checkin = $req->input('checkin');
        $tanggal_checkout = $req->input('checkout');
        $jumlah_kamar = $req->input('jumlah_kamar');
        $harga = $req->input('harga');

        // Butuh 6 parameter cuy sesuai sama yang ada di file EmailNotification oke
        $send_email = new EmailNotification($nama_tamu, $id_transaksi, $tanggal_checkin, $tanggal_checkout, $jumlah_kamar, $harga);
        Mail::to($req->input('email'))->send($send_email);

        if ($send_email) {
            return response()->json(['message' => 'berhasil kirim email']);
        }

        if ($createtransaksi && $updatestatuskamar) {
            return response()->json(['Message' => 'Sukses tambah transaksi']);
        } else {
            return response()->json(['Message' => 'Gagal tambah transaksi']);
        }
    }

    public function updatetransaksi(Request $r, $id)
    {
        $update = transaksi::where('id_transaksi', $id)->update([
            'nama_tamu' => $r->input('nama_tamu'),
            'email' => $r->input('email'),
            'tgl_pesan' => $r->input('tgl_pesan'),
            'check_in' => $r->input('check_in'),
            'check_out' => $r->input('check_out'),
            'id_kamar' => $r->input('id_kamar'),
            'id_fasilitas' => $r->input('id_fasilitas'),
            'jumlah_kamar' => $r->input('jumlah_kamar'),
            'total_harga' => $r->input('harga'),
            'status' => $r->input('status'),
        ]);

        if ($update) {
            return response()->json(['Message' => 'Sukses update transaksi']);
        } else {
            return response()->json(['Message' => 'Gagal update transaksi']);
        }
    }
    public function deletetransaksi($id)
    {
        $delete = transaksi::where('id_transaksi', $id)->delete();
        if ($delete) {
            return response()->json('Berhasil menghapus data');
        } else {
            return response()->json('Gagal menghapus data');
        }
    }
    public function deletealltransaksi()
    {
        // $delete = transaksi::truncate();
        $delete = DB::table('transaksis')->delete();
        return response()->json(['message' => 'Sukses reset history']);
    }
    public function konfirmasi(Request $req, $id)
    {
        $transaksi = transaksi::where('id_transaksi', $id)->update([
            // 'no_kamar' => $req->input('no_kamar'),
            'status' => 'dikonfirmasi'
        ]);

        $id_transaksi = $req->input('id_transaksi');
        $nama_tamu = $req->input('nama_tamu');

        $email = new emailConfirmation($id_transaksi, $nama_tamu);
        Mail::to($req->input('email'))->send($email);

        if (!$email) {
            return response()->json(['msg' => 'Failed send email', 'type' => 'email'], 412);
        }

        return response()->json([
            'Message' => 'Berhasil Konfirmasi'
        ], 200);
    }
    public function checkin(Request $req, $id)
    {

        // $roomCheck = transaksi::where('no_kamar', $req->input('no_kamar'))
        //     ->whereIn('status', ['dipesan', 'dikonfirmasi', 'dipakai'])
        //     ->count('no_kamar');

        // if ($roomCheck >= 1) {
        //     return response()->json(['msg' => 'The room is already in use!'], 422);
        // }

        $update = transaksi::where('id_transaksi', $id)->update([
            'no_kamar' => 0,
            'status' => 'dipakai'
        ]);

        return response()->json([
            'Message' => 'Sukses Check-In',
            'Result' => $update
        ]);
    }
    public function checkout($id, $id_kamar)
    {
        $checkout = transaksi::where('id_transaksi', $id)->update([
            'status' => 'selesai'
        ]);

        $roomUpdate = DB::table('no_kamar')->where('id_transaksi', $id)->update([
            'id_transaksi' => '0',
            'status' => 'kosong'
        ]);

        $kamar = kamar::where('id_kamar', $id_kamar)->update([
            'status_kamar' => 'kosong'
        ]);

        if (!$checkout) {
            return response()->json(['msg' => 'Check-Out Failed', 'type' => 'checkout'], 409);
        }

        if (!$kamar) {
            return response()->json(['msg' => 'Failed Update Room', 'type' => 'room'], 409);
        }

        if ($checkout && $kamar) {
            return response()->json(['msg' => 'Successfull'], 200);
        }
    }
    public function kamardone($id, $id_kamar)
    {
        $update = transaksi::where('id_transaksi', $id)->update([
            'status' => 'selesai'
        ]);

        $kamar = kamar::where('id_kamar', $id_kamar)->update([
            'status_kamar' => 'kosong'
        ]);

        return response()->json([
            'Message' => 'Sukses selesai booking',
            'booking' => $update,
            'kamar' => $kamar
        ]);
    }

    public function feedback(Request $req)
    {
        $check = transaksi::where('id_transaksi', $req->input('id_transaksi'))->first();
        $check2 = DB::table('transaksis')
            ->where('id_transaksi', $req->input('id_transaksi'))
            ->whereNotIn('status', ['dipesan', 'dikonfirmasi'])
            ->first();

        if (!$check) {
            return response()->json(['msg' => 'Data Not Found'], 404);
        } else if (!$check2) {
            return response()->json(['msg' => 'Not complete'], 400);
        }

        $check3 = DB::table('feedback')->where('id_transaksi', $req->input('id_transaksi'))->count('id_transaksi');
        if ($check3 === 2) {
            return response()->json(['msg' => 'You already send 2 feedback'], 406);
        }

        $getName = DB::table('transaksis')->where('id_transaksi', $req->input('id_transaksi'))->first();
        $nama = $getName->nama_tamu;

        $send = DB::table('feedback')->insert([
            'id_transaksi' => $req->input('id_transaksi'),
            'isi' => $req->input('isi'),
            'email' => $req->input('email'),
            'tgl' => Carbon::now(),
            'review' => $req->input('review'),
            'nama_tamu' => $nama
        ]);

        if ($send) {
            return response()->json(['msg' => 'Success send feedback'], 200);
        }

        return response()->json(['msg' => 'Failed send feedback'], 500);
    }

    public function getFeedback()
    {
        $get = DB::table('feedback')
            ->orderBy('id_feedback', 'desc')
            ->get();
        return response()->json($get);
    }

    public function selectFeedback($id)
    {
        $get = DB::table('feedback')
            ->where('id_feedback', $id)
            ->get();

        return response()->json($get);
    }
    public function countFeedback()
    {
        $count = DB::table('feedback')->count('id_feedback');
        return response()->json($count);
    }

    function sendFeedbackToUser(Request $req)
    {
        $id_transaksi = $req->id_transaksi;
        $balasan = $req->balasan;

        $sendMail = new FeedbackMail($id_transaksi, $balasan);
        Mail::to($req->email)->send($sendMail);

        if ($sendMail) {
            return response()->json(['msg' => 'Berhasil Mengirim Email'], 200);
        }
        return response()->json(['msg' => 'Gagal mengirim email'], 500);
    }
}
