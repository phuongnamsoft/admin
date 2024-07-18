<?php

namespace PNS\Admin\Console;

use Illuminate\Console\Command;

class InstallUpdateCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $signature = 'admin:install-update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install update the admin package';

    /**
     * Install directory.
     *
     * @var string
     */
    protected $directory = '';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->migrateDB();
        $this->publishAssets();
    }

    /**
     * Create tables and seed it.
     *
     * @return void
     */
    public function migrateDB()
    {
        $this->call('migrate');
    }

    /**
     * Publish assets.
     *
     * @return void
     */
    public function publishAssets()
    {
        $this->call('vendor:publish', ['--tag' => 'laravel-admin-assets']);
    }
}
