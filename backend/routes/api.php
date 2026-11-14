<?php

declare(strict_types=1);

use App\Http\Controllers\Api\V1\PracticeAreaController;
use App\Http\Controllers\Api\V1\PrefectureController;
use Illuminate\Support\Facades\Route;

// ベースURL: /api/v1(bootstrap/app.php の apiPrefix)

Route::get('/prefectures', [PrefectureController::class, 'index'])->name('prefectures.index');
Route::get('/practice-areas', [PracticeAreaController::class, 'index'])->name('practice-areas.index');
