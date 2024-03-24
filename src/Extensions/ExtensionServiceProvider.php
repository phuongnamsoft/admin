<?php

namespace App\Admin;

use Illuminate\Support\ServiceProvider;
use PNS\Admin\Extensions\CKEditor\CKEditorServiceProvider;

class ExtensionServiceProvider extends ServiceProvider {

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register() {
        $this->app->register(CKEditorServiceProvider::class);
    }
}
