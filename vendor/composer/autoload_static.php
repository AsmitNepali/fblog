<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit7d1bab5fe6e830f004cac86481542f4b
{
    public static $prefixLengthsPsr4 = [
        'M' => [
            'Magan\\FilamentBlog\\' => 19,
        ],
    ];

    public static $prefixDirsPsr4 = [
        'Magan\\FilamentBlog\\' => [
            0 => __DIR__.'/../..'.'/src',
        ],
    ];

    public static $classMap = [
        'Composer\\InstalledVersions' => __DIR__.'/..'.'/composer/InstalledVersions.php',
    ];

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit7d1bab5fe6e830f004cac86481542f4b::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit7d1bab5fe6e830f004cac86481542f4b::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInit7d1bab5fe6e830f004cac86481542f4b::$classMap;

        }, null, ClassLoader::class);
    }
}