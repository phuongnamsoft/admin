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

            $router->get('settings-ui', 'PNS\Admin\Extensions\Settings\Controllers\SettingUiController@index');
            $router->post('settings-ui/save', 'PNS\Admin\Extensions\Settings\Controllers\SettingUiController@save');
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
        parent::createMenu('Settings', 'settings', 'fa-cogs');

        parent::createMenu('Settings UI', 'settings-ui', 'fa-toggle-on');

        parent::createPermission('Settings', 'ext.settings', 'settings*');
        parent::createPermission('Settings UI', 'ext.settings-ui', 'settings-ui*');
        parent::createPermission('Settings UI Save', 'ext.settings-ui-save', 'settings-ui/save*');
    }

    public static function install() {
        return self::import();
    }

    public static function uninstall() {
        
    }
}
