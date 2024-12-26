<?php

namespace PNS\Admin\Extensions\Settings;

use PNS\Admin\Extension;
use PNS\Admin\Extensions\Settings\Models\Setting;
use PNS\Admin\Facades\Admin;

/**
 * Class Setting.
 */
class SettingExtension extends Extension
{
    public $name = 'settings';

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    protected static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->resource('settings', 'PNS\Admin\Extensions\Settings\Controllers\SettingController');
        });
    }

        /**
     * Load configure into laravel from database.
     *
     * @return void
     */
    public static function load()
    {
        foreach (Setting::all(['name', 'value']) as $config) {
            config([$config['name'] => $config['value']]);
        }
    }

    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('settings', __CLASS__);
    }

    /**
     * {@inheritdoc}
     */
    public static function import()
    {
        parent::createMenu('Settings', 'settings', 'fa-toggle-on');

        parent::createPermission('Admin Settings', 'ext.settings', 'settings*');
    }
}
