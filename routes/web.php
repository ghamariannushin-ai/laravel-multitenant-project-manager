<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tenant-test', function () {
    return response()->json([
        'id' => tenant()?->id,
        'name' => tenant()?->name,
        'domain' => tenant()?->domain,
        'database' => tenant()?->database,
    ]);
});
