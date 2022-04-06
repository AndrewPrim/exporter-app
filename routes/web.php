<?php

declare(strict_types=1);

use App\Http\Controllers\DataController;
use Illuminate\Support\Facades\Route;


Route::get('/', [DataController::class, 'index'])->name('home');
Route::post('/export', [DataController::class, 'exportData'])->name('post.data');
Route::get('/download/{filename}', [DataController::class, 'download'])->name('file.download');

