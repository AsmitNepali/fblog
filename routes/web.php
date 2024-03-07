<?php

use Illuminate\Support\Facades\Route;
use Magan\FilamentBlog\Http\Controllers\BlogController;

Route::get('/filament-blog', [BlogController::class, 'index']);
