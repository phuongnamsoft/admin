<?php

namespace PNS\Admin\Extensions\Settings;

use Illuminate\Support\ServiceProvider;

class SettingServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->loadViewsFrom(__DIR__ . '/resources/views', 'laravel-admin-settings');
        SettingExtension::boot();
    }
}
