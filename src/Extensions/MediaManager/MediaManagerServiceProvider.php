<?php

namespace PNS\Admin\Extensions\MediaManager;

use Illuminate\Support\ServiceProvider;
use PNS\Admin\Admin;

class MediaManagerServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $extension = new MediaManager;
        if ($views = $extension->views()) {
            $this->loadViewsFrom($views, 'laravel-admin-media');
        }
        Admin::extend('media-manager', MediaManager::class);
    }
}
