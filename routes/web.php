<?php

use Illuminate\Support\Facades\Route;
use Magan\FilamentBlog\Http\Controllers\BlogController;

Route::middleware(config('filamentblog.route.middleware'))
    ->prefix(config('filamentblog.route.prefix'))
    ->group(function () {
        Route::get('/', [BlogController::class, 'index'])->name('post.index');
        Route::get('/{post:slug}', [BlogController::class, 'show'])->name('post.show');
    });
