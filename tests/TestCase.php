<?php

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Laravel\BrowserKitTesting\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
    protected $baseUrl = 'http://localhost:8000';

    /**
     * Boots the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../vendor/laravel/laravel/bootstrap/app.php';

        $laravelConfigDir = dirname(__DIR__).'/vendor/laravel/laravel/config';
        $packageAdminConfig = dirname(__DIR__).'/config/admin.php';
        if (is_readable($packageAdminConfig)) {
            if (! is_dir($laravelConfigDir)) {
                mkdir($laravelConfigDir, 0755, true);
            }
            copy($packageAdminConfig, $laravelConfigDir.'/admin.php');
        }

        $app->booting(function () {
            $loader = \Illuminate\Foundation\AliasLoader::getInstance();
            $loader->alias('Admin', \PNS\Admin\Facades\Admin::class);
        });

        $app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

        $app->register(\Illuminate\Database\Eloquent\LegacyFactoryServiceProvider::class);

        $app->register('PNS\Admin\AdminServiceProvider');

        return $app;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $packageAdmin = require dirname(__DIR__).'/config/admin.php';
        $adminConfig = require __DIR__.'/config/admin.php';
        $mergedAdmin = array_replace_recursive($packageAdmin, $adminConfig);

        $this->app['config']->set('database.default', env('DB_CONNECTION', 'mysql'));
        $this->app['config']->set('database.connections.mysql.host', env('MYSQL_HOST', 'localhost'));
        $this->app['config']->set('database.connections.mysql.database', env('MYSQL_DATABASE', 'laravel_admin_test'));
        $this->app['config']->set('database.connections.mysql.username', env('MYSQL_USER', 'root'));
        $this->app['config']->set('database.connections.mysql.password', env('MYSQL_PASSWORD', ''));
        $this->app['config']->set('app.key', 'AckfSECXIvnK5r28GVIWUAxmbBSjTsmF');
        $this->app['config']->set('filesystems', require __DIR__.'/config/filesystems.php');
        $this->app['config']->set('admin', $mergedAdmin);

        foreach (Arr::dot(Arr::get($mergedAdmin, 'auth'), 'auth.') as $key => $value) {
            $this->app['config']->set($key, $value);
        }

        $this->artisan('vendor:publish', ['--provider' => 'PNS\Admin\AdminServiceProvider', '--force' => true]);

        Schema::defaultStringLength(191);

        $this->artisan('admin:install');

        $this->migrateTestTables();

        if (file_exists($routes = admin_path('routes.php'))) {
            require $routes;
        }

        require __DIR__.'/routes.php';

        require __DIR__.'/seeds/factory.php';

//        \PNS\Admin\Admin::$css = [];
//        \PNS\Admin\Admin::$js = [];
//        \PNS\Admin\Admin::$script = [];
    }

    protected function tearDown(): void
    {
        (new CreateAdminTables())->down();

        (new CreateTestTables())->down();

        DB::table('migrations')->where('migration', '2016_01_04_173148_create_admin_tables')->delete();

        parent::tearDown();
    }

    /**
     * run package database migrations.
     *
     * @return void
     */
    public function migrateTestTables()
    {
        $fileSystem = new Filesystem();

        $fileSystem->requireOnce(__DIR__.'/migrations/2016_11_22_093148_create_test_tables.php');

        (new CreateTestTables())->up();
    }

    /**
     * Skip when Intervention/image cannot use GD or Imagick (common in minimal CI or local PHP builds).
     */
    protected function requiresImageDriver(): void
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            $this->markTestSkipped('PHP gd or imagick extension is required for this test.');
        }
    }
}
