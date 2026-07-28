<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/app');

Route::redirect('/dashboard', '/app')->name('dashboard');

require __DIR__.'/auth.php';
