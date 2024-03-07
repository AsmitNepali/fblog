<?php

return [
    'route' => [
        'prefix' => 'blog',
        'middleware' => ['web'],
    ],
    'user_model' => \App\Models\User::class,
    'user_resource' => \App\Filament\UserResource::class,
];
