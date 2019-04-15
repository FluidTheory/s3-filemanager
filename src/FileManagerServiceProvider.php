<?php

namespace Fluidtheory\Filemanager;

use Illuminate\Support\ServiceProvider;

class FileManagerServiceProvider extends ServiceProvider{

    public function boot(){
        $this->loadRoutesFrom(__DIR__.'/routes/web.php');
        $this->loadViewsFrom(__DIR__.'/views', 'filemanager');

        $this->publishes([
            __DIR__.'/css/' => public_path('/css/'),
        ], 'public');

        $this->publishes([
            __DIR__.'/js/' => public_path('/js/'),
        ], 'public');
    }

    public function register()
    {

    }

}