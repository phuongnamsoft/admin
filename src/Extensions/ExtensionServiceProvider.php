<?php

namespace PNS\Admin\Extensions;

use Illuminate\Support\ServiceProvider;
use PNS\Admin\Extensions\Helpers\HelpersServiceProvider;
use PNS\Admin\Extensions\LogViewer\LogViewerServiceProvider;
use PNS\Admin\Extensions\Scheduling\SchedulingServiceProvider;

class ExtensionServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        // $this->app->register(MediaManagerServiceProvider::class);
        $this->app->register(LogViewerServiceProvider::class);
        $this->app->register(HelpersServiceProvider::class);
        $this->app->register(SchedulingServiceProvider::class);
    }
}
