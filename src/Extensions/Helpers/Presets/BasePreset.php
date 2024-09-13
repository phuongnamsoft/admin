<?php 

namespace PNS\Admin\Extensions\Helpers\Presets;

abstract class BasePreset {
    protected $options = [];
    const STUBS = [];

    protected function moveModelFiles($name)
    {
        $stub = static::STUBS[$name];
        if (isset($stub['models'])) {
            foreach ($stub['models'] as $model) {
                copy(__DIR__ . "/stubs/Models/{$model}.php.stub", app_path("Models/{$model}.php"));
            }
        }
    }

    protected function moveControllerFiles($name)
    {
        $stub = static::STUBS[$name];
        if (isset($stub['controllers'])) {
            foreach ($stub['controllers'] as $controller) {
                copy(__DIR__ . "/stubs/Controllers/{$controller}.php.stub", app_path("Admin/Controllers/{$controller}.php"));
            }
        }
    }

    protected function moveMigrationFiles($name)
    {
        $stub = static::STUBS[$name];
        if (isset($stub['migrations'])) {
            foreach ($stub['migrations'] as $migration) {
                copy(__DIR__ . "/stubs/Migrations/{$migration}.php.stub", database_path("migrations/{$this->getDatePrefix()}_{$migration}.php"));
            }
        }
    }

    protected function moveSeedFiles($name)
    {
        $stub = static::STUBS[$name];
        if (isset($stub['seeds'])) {
            foreach ($stub['seeds'] as $seed) {
                copy(__DIR__ . "/stubs/Seeds/{$seed}.php.stub", database_path("seeds/{$seed}.php"));
            }
        }
    }

    protected function moveRouteFiles($name)
    {
        $stub = static::STUBS[$name];
        if (isset($stub['routes'])) {
            $routes = implode("\n", $stub['routes']);
            file_put_contents(base_path('routes/admin.php'), $routes, FILE_APPEND);
        }
    }

    protected function getDatePrefix()
    {
        return date('Y_m_d_His');
    }

    protected function runMigrations()
    {
        // \Artisan::call('migrate');
    }

    protected function runSeeds()
    {
        // \Artisan::call('migrate');
    }
}