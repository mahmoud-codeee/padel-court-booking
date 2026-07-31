<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'Padel Booking API', 'status' => 'ok']);
});
