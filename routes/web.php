<?php

use Illuminate\Support\Facades\Route;
use Magan\FilamentBlog\Http\Controllers\PostController;

Route::middleware(config('filamentblog.route.middleware'))
    ->prefix(config('filamentblog.route.prefix'))
    ->group(function () {
        Route::get('/', [PostController::class, 'index'])->name('post.index');
        Route::get('/{post:slug}', [PostController::class, 'show'])->name('post.show');
    });
