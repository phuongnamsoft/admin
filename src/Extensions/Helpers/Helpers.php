<?php

namespace PNS\Admin\Extensions\Helpers;

use PNS\Admin\Admin;
use PNS\Admin\Auth\Database\Menu;
use PNS\Admin\Extension;

class Helpers extends Extension
{
    /**
     * Bootstrap this package.
     *
     * @return void
     */
    public static function boot()
    {
        static::registerRoutes();

        Admin::extend('helpers', __CLASS__);
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    public static function registerRoutes()
    {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('helpers/terminal/database', 'PNS\Admin\Extensions\Helpers\Controllers\TerminalController@database');
            $router->post('helpers/terminal/database', 'PNS\Admin\Extensions\Helpers\Controllers\TerminalController@runDatabase');
            $router->get('helpers/terminal/artisan', 'PNS\Admin\Extensions\Helpers\Controllers\TerminalController@artisan');
            $router->post('helpers/terminal/artisan', 'PNS\Admin\Extensions\Helpers\Controllers\TerminalController@runArtisan');
            $router->get('helpers/scaffold', 'PNS\Admin\Extensions\Helpers\Controllers\ScaffoldController@index');
            $router->post('helpers/scaffold', 'PNS\Admin\Extensions\Helpers\Controllers\ScaffoldController@store');
            $router->get('helpers/routes', 'PNS\Admin\Extensions\Helpers\Controllers\RouteController@index');
        });
    }

    public static function install()
    {
        $lastOrder = Menu::max('order') ?: 0;

        $root = [
            'parent_id' => 0,
            'order'     => $lastOrder++,
            'title'     => 'Helpers',
            'icon'      => 'fa-gears',
            'uri'       => '',
        ];

        $menuIds = [];

        $root = Menu::create($root);
        $menuIds [] = $root->id;

        $menus = [
            [
                'title'     => 'Scaffold',
                'icon'      => 'fa-keyboard-o',
                'uri'       => 'helpers/scaffold',
            ],
            [
                'title'     => 'Database terminal',
                'icon'      => 'fa-database',
                'uri'       => 'helpers/terminal/database',
            ],
            [
                'title'     => 'Laravel artisan',
                'icon'      => 'fa-terminal',
                'uri'       => 'helpers/terminal/artisan',
            ],
            [
                'title'     => 'Routes',
                'icon'      => 'fa-list-alt',
                'uri'       => 'helpers/routes',
            ],
        ];

        foreach ($menus as $menu) {
            $menu['parent_id'] = $root->id;
            $menu['order'] = $lastOrder++;

            $childMenu = Menu::create($menu);
            $menuIds [] = $childMenu->id;
        }

        parent::createPermission('Admin helpers', 'ext.helpers', 'helpers/*');

        
    }
}
