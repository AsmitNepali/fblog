<?php

namespace Magan\FilamentBlog;

use Magan\FilamentBlog\Components\HeroChildPost;
use Magan\FilamentBlog\Components\HeroPost;
use Magan\FilamentBlog\Components\Layout;
use Magan\FilamentBlog\Components\RecentPost;
use Magan\FilamentBlog\Components\RelatedPost;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class FilamentBlogServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-blog')
            ->hasConfigFile(['filamentblog'])
            ->hasMigration('create_blog_tables')
            ->hasViewComponents('blog', Layout::class, HeroPost::class, HeroChildPost::class, RecentPost::class, RelatedPost::class)
            ->hasViews('filament-blog')
            ->hasRoute('web')
            ->hasInstallCommand(function (InstallCommand $installCommand) {
                $installCommand
                    ->startWith(function (InstallCommand $command) {
                        $command->info('Hello, and welcome to my great new package!');
                    })
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->endWith(function (InstallCommand $installCommand) {
                        $installCommand->info('Congratulations! Your package has been installed!');
                    });
            });
    }
}
