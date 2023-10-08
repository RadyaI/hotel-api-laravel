<?php

use App\Http\Controllers\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\KamarController;
use App\Http\Controllers\TransaksiController;
use App\Http\Controllers\noKamarController;
use App\Http\Controllers\tesDataController;
// use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Buat tes Api ini
// Buat tes Api ini

Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login',[AuthController::class,'login']);

// Route::group(['middleware' => ['jwt.verify']], function () {

Route::group(['middleware' => ['jwt.verify']], function () {

    Route::group(['middleware' => ['api.admin']], function () {

        // Admin-Feedback
        Route::get('/getFeedback', [TransaksiController::class, 'getFeedback']);
        Route::get('/selectFeedback/{id}', [TransaksiController::class, 'selectFeedback']);
        Route::get('/countFeedback', [TransaksiController::class, 'countFeedback']);
        Route::post('/sendFeedbackToUser', [TransaksiController::class, 'sendFeedbackToUser']);

        // Admin-User
        Route::get('/getUser', [UserController::class, 'getuser']);
        Route::post('/createUser', [UserController::class, 'createuser']);
        Route::get('/getUser/{id}', [UserController::class, 'getsatuuser']);
        Route::post('/updateUser/{id}', [UserController::class, 'updateuser']);
        Route::delete('/deleteUser/{id}', [UserController::class, 'deleteuser']);

        // Admin-Room
        Route::post('/uploadFoto/{id}', [KamarController::class, 'uploadFoto']);
        Route::post('/createkamar', [KamarController::class, 'createkamar']);
        Route::put('/updatekamar/{id}', [KamarController::class, 'updatekamar']);
        Route::delete('/deletekamar/{id}', [KamarController::class, 'deletekamar']);
    });

    Route::group(['middleware' => ['api.resepsionis']], function () {
        Route::get('/history', [TransaksiController::class, 'history']);
        Route::get('/notconfirmed', [TransaksiController::class, 'notconfirmed']);
    });
});

//USER
Route::post('/feedback', [TransaksiController::class, 'feedback']);
Route::get('/user/history/{id}',[UserController::class, 'userHistory']);

//KAMAR  
Route::get('/getkamar', [KamarController::class, 'getkamar']);
Route::get('/filterKamar/{person}', [KamarController::class, 'filterKamar']);
Route::get('/getkamar/{id}', [KamarController::class, 'kamarbyid']);

Route::get('/getAllNoKamar', [noKamarController::class, 'getAllKamar']);
Route::get('/getNoKamar', [noKamarController::class, 'getKamar']);
Route::get('/selectKamar/{id}', [noKamarController::class, 'selectKamar']);
Route::post('/addNumber', [noKamarController::class, 'addNumber']);
Route::put('/updateKamar/{id}', [noKamarController::class, 'updateKamar']);
Route::put('/chooseRoom/{noKamar}', [noKamarController::class, 'chooseKamar']);
Route::delete('/deleteRoom/{id}', [noKamarController::class, 'deleteKamar']);

//TRANSAKSI
Route::get('/gettransaksi', [TransaksiController::class, 'gettransaksi']);
Route::get('/cekbooking/{id}', [TransaksiController::class, 'cekbooking']);

Route::get('/gettransaksi/{id}', [TransaksiController::class, 'pilihtransaksibynama']);
Route::get('/gettransaksibyid/{id}', [TransaksiController::class, 'pilihtransaksibyid']);
Route::post('/createtransaksi', [TransaksiController::class, 'createtransaksi']);
Route::put('/updatetransaksi/{id}', [TransaksiController::class, 'updatetransaksi']);
Route::put('/konfirmasi/{id}', [TransaksiController::class, 'konfirmasi']);
Route::delete('/deletetransaksi/{id}', [TransaksiController::class, 'deletetransaksi']);
Route::delete('/deletetransaksi', [TransaksiController::class, 'deletealltransaksi']);
// RESEPSIONIS
Route::get('/confirmed', [TransaksiController::class, 'confirmed']);
Route::get('/ongoing', [TransaksiController::class, 'ongoing']);
Route::get('/dibersihkan', [TransaksiController::class, 'dibersihkan']);

// Route::get('/cekbooking', [TransaksiController::class, 'cekbooking']);

Route::put('/checkin/{id}', [TransaksiController::class, 'checkin']);
Route::put('/checkout/{id}/{id_kamar}', [TransaksiController::class, 'checkout']);
Route::put('kamar_done/{id}/{id_kamar}', [TransaksiController::class, 'kamardone']);
// });

// tes
Route::get('/getTesData',[tesDataController::class,'get']);
Route::post('/TambahData',[tesDataController::class,'createData']);
Route::delete('/deleteDataTes/{id}',[tesDataController::class,'deleteData']);