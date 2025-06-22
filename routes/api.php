<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// ... (mungkin ada route lain) ...

// Route untuk Health Check Railway
Route::get('/health', function () {
    return response()->json(['status' => 'ok']);
});