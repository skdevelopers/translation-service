<?php

use App\Http\Controllers\TranslationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

        // Static routes first or add constraints to avoid conflict
        Route::get('/translations/search', [TranslationController::class, 'search']);
        Route::get('/translations/export', [TranslationController::class, 'export']);

        // Add a constraint so {id} only matches numbers
        Route::get('/translations/{id}', [TranslationController::class, 'show'])
            ->where('id', '[0-9]+');
        Route::post('/translations', [TranslationController::class, 'store']);
        Route::put('/translations/{id}', [TranslationController::class, 'update'])
            ->where('id', '[0-9]+');
        Route::delete('/translations/{id}', [TranslationController::class, 'destroy'])
            ->where('id', '[0-9]+');


});
