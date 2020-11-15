<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\KiviController;

Route::get('/start/{hashOrId}/{eventId?}', [KiviController::class, "moderationStart"]);
Route::get('/join/{hashOrId}/{eventId?}', [KiviController::class, "participantJoin"]);