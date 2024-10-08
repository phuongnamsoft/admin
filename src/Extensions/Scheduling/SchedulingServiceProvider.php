<?php

namespace PNS\Admin\Extensions\Scheduling;

use Illuminate\Support\ServiceProvider;

class SchedulingServiceProvider extends ServiceProvider
{
    /**
     * {@inheritdoc}
     */
    public function boot()
    {
        $this->loadViewsFrom(__DIR__.'/resources/views', 'laravel-admin-scheduling');

        Scheduling::boot();
    }
}
