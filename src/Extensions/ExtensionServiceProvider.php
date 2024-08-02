<?php

namespace App\Admin;

use Illuminate\Support\ServiceProvider;
use PNS\Admin\Extensions\LogViewer\LogViewerServiceProvider;
use PNS\Admin\Extensions\MediaManager\MediaManagerServiceProvider;

class ExtensionServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->register(MediaManagerServiceProvider::class);
        $this->app->register(LogViewerServiceProvider::class);
    }
}
