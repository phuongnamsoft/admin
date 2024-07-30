<?php

namespace App\Admin;

use Illuminate\Support\ServiceProvider;
use PNS\Admin;

class ExtensionServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        if (Extensions\MediaManager\MediaManager::boot()) {
            $extension = new Extensions\MediaManager\MediaManager;
            if ($views = $extension->views()) {
                $this->loadViewsFrom($views, 'laravel-admin-media');
            }
            Admin::extend('media-manager', Extensions\MediaManager\MediaManager::class);
        }
    }
}
