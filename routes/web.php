<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UmkmController;

Route::get('/', [UmkmController::class, 'index'])->name('home');
Route::get('umkm', [UmkmController::class, 'index'])->name('umkm');
Route::post('umkm', [UmkmController::class, 'index'])->name('umkm.post');

// Route::post('umkm', [UmkmController::class, 'index'])->name('umkm.search'); // For AJAX search


Route::middleware(['auth'])->group(function () {
    Route::resource('umkm2', UmkmController::class);
    Route::post('umkm/auto-categorize', [UmkmController::class, 'autoCategorize'])->name('umkm.auto-categorize');
    Route::post('umkm/import-csv', [UmkmController::class, 'importCsv'])->name('umkm.import-csv');
});

Route::get('view', function () {
    return view('umkm');
});

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/umkm/load-data', [UmkmController::class, 'loadData'])->name('umkm.loadData');
