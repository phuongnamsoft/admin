<?php

namespace App\Admin\Extensions\MediaManager;

use PNS\Admin\Admin;

trait BootExtension {

    /**
     * {@inheritdoc}
     */
    public static function boot() {
        static::registerRoutes();
        return TRUE;
    }

    /**
     * Register routes for laravel-admin.
     *
     * @return void
     */
    protected static function registerRoutes() {
        parent::routes(function ($router) {
            /* @var \Illuminate\Routing\Router $router */
            $router->get('media', 'App\Admin\Extensions\MediaManager\MediaController@index')->name('media-index');
            $router->get('media/download', 'App\Admin\Extensions\MediaManager\MediaController@download')->name('media-download');
            $router->delete('media/delete', 'App\Admin\Extensions\MediaManager\MediaController@delete')->name('media-delete');
            $router->put('media/move', 'App\Admin\Extensions\MediaManager\MediaController@move')->name('media-move');
            $router->post('media/upload', 'App\Admin\Extensions\MediaManager\MediaController@upload')->name('media-upload');
            $router->post('media/folder', 'App\Admin\Extensions\MediaManager\MediaController@newFolder')->name('media-new-folder');
        });
    }

    /**
     * {@inheritdoc}
     */
    public static function import() {
        parent::createMenu('Media manager', 'media', 'fa-file');

        parent::createPermission('Media manager', 'ext.media-manager', 'media*');
    }

}
