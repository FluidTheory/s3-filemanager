<?php

namespace Fluidtheory\Filemanager;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider{

    public function boot(){
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/views', 'filemanager');

        $this->mergeConfigFrom(
            __DIR__.'/config/path.php', 'path'
        );

        $this->loadMigrationsFrom(__DIR__.'/database/migrations');

        $this->publishes([
            __DIR__.'/css/' => public_path('/css/'),
        ], 'public');

        $this->publishes([
            __DIR__.'/js/' => public_path('/js/'),
        ], 'public');

        $this->publishes([
            __DIR__.'/config/path.php' => config_path('path.php'),
        ]);
    }

    public function register()
    {

    }

}