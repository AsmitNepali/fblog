<?php

use Illuminate\Support\Facades\Route;
use Magan\FilamentBlog\Http\Controllers\TestController;

Route::get('/filament-blog', [TestController::class, 'index']);
