<?php

use App\Http\Controllers\AkunController;
use App\Http\Controllers\BahanBakuController;
use App\Http\Controllers\BahanKeluarController;
use App\Http\Controllers\BahanMasukController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TransaksiController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return 'Halo Dunia. Tes berhasil.';
});

// // Route Login

// Route::get('/',[LoginController::class,'index'])->name('login');
// Route::post('/login-proses',[LoginController::class,'login_proses'])->name('login-proses');
// Route::get('/logout',[LoginController::class,'logout'])->name('logout');
// // End Route Login

// //middleware Owner
// Route::group(['prefix' => 'owner', 'middleware' => ['auth'], 'as' => 'owner.'], function() {

//     // Dashboard
//     Route::get('/dashboard',[HomeController::class,'dashboard'])->name('dashboard');
//     // End Dashboard

//     // --- KELOMPOK RUTE KHUSUS OWNER ---
//     // Middleware 'owner' hanya diterapkan pada grup ini
//     Route::group(['middleware' => ['owner']], function () {
//         // Route Akun Pengguna (HANYA BISA DIAKSES OWNER)
//         Route::get('/Akun_pengguna', [AkunController::class, 'akun'])->name('akun');
//         Route::get('/akun.create', [AkunController::class, 'create'])->name('akun.create');
//         Route::post('/akun.store', [AkunController::class, 'store'])->name('akun.store');
//         Route::get('/akun.edit/{id}', [AkunController::class, 'edit'])->name('akun.edit');
//         Route::put('/akun.update/{id}', [AkunController::class, 'update'])->name('akun.update');
//         Route::delete('/akun.delete/{id}', [AkunController::class, 'delete'])->name('akun.delete');
//     });
//     // --- AKHIR DARI KELOMPOK KHUSUS OWNER ---

//     // Route Bahan Masuk
//     Route::get('/bahan_masuk',[BahanMasukController::class,'index'])->name('bahan_masuk');
//     Route::get('/masuk.create',[BahanMasukController::class,'create'])->name('masuk.create');
//     Route::post('/masuk.store',[BahanMasukController::class,'store'])->name('masuk.store');

//     Route::delete('/masuk.delete/{id}',[BahanMasukController::class,'delete'])->name('masuk.delete');
//     // End Bahan Masuk

//     // Route Bahan Keluar
//     Route::get('/bahan_keluar',[BahanKeluarController::class,'index'])->name('bahan_keluar');
//     Route::get('/keluar.create',[BahanKeluarController::class,'create'])->name('keluar.create');
//     Route::post('/keluar.store',[BahanKeluarController::class,'store'])->name('keluar.store');

//     Route::delete('/keluar.delete/{id}',[BahanKeluarController::class,'delete'])->name('keluar.delete');
//     // End Bahan Keluar

//     // Route Menu
//     Route::get('/menu',[MenuController::class,'index'])->name('menu');
//     Route::get('/menu.create',[MenuController::class,'create'])->name('menu.create');
//     Route::post('/menu.store',[MenuController::class,'store'])->name('menu.store');
//     Route::get('/menu.show/{id}',[MenuController::class,'show'])->name('menu.show');

//     Route::get('/menu.edit/{id}',[MenuController::class,'edit'])->name('menu.edit');
//     Route::put('/menu.update/{id}',[MenuController::class,'update'])->name('menu.update');

//     Route::delete('/menu.delete/{id}',[MenuController::class,'delete'])->name('menu.delete');
//     // End Menu

//     // Route Stok Bahan Baku
//     Route::get('/bahan_baku',[BahanBakuController::class,'index'])->name('bahan_baku');
//     Route::get('/stok.create',[BahanBakuController::class,'create'])->name('stok.create');
//     Route::post('/stok.store',[BahanBakuController::class,'store'])->name('stok.store');

//     Route::get('/stok.edit/{id}',[BahanBakuController::class,'edit'])->name('stok.edit');
//     Route::put('/stok.update/{id}',[BahanBakuController::class,'update'])->name('stok.update');

//     Route::delete('/stok.delete/{id}',[BahanBakuController::class,'delete'])->name('stok.delete');
//     // End Stok Bahan Baku

//     // Route untuk Kasir
//     Route::get('/kasir', [TransaksiController::class, 'index'])->name('kasir.index');
//     Route::post('/kasir/order', [TransaksiController::class, 'store'])->name('kasir.store');

//     // ROUTE UNTUK LAPORAN
//     Route::get('/laporan', [LaporanController::class, 'index'])->name('laporan.index');
//     Route::get('/laporan/detail/{id}', [LaporanController::class, 'show'])->name('laporan.show');

//     Route::delete('/laporan/{id}/soft-delete', [LaporanController::class, 'softDelete'])->name('laporan.softdelete');
//     Route::delete('/laporan/{id}/force-delete', [LaporanController::class, 'forceDelete'])->name('laporan.forcedelete');

//     Route::get('/laporan/stok', [LaporanController::class, 'laporanStok'])->name('laporan.stok');

//     // Route untuk mengubah status transaksi menjadi lunas
//     Route::post('/transaksi/{id}/mark-as-paid', [TransaksiController::class, 'markAsPaid'])->name('transaksi.markAsPaid');

// });

