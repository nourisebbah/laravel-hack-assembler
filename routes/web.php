<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AseemblerController;  



Route::get('/', function () {
    return view('assembler');
});


Route::post('assemble',[AseemblerController::class,'translate'])->name('assemble');