<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ApiController;

Route::match(['get', 'post'], '/', [ApiController::class, 'index'])->name('form');
