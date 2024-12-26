<?php

namespace PNS\Admin\Console;

use PNS\Admin\Admin;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;
use PNS\Admin\Seeders\ExtensionSeeder;
use PNS\Admin\Seeders\SettingSeeder;

class AdminToolsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:tools {cmd}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Admin Tools';

    /**
     * @var string
     */
    public static $logo = <<<LOGO
    __                                __                __          _     
   / /   ____ __________ __   _____  / /     ____ _____/ /___ ___  (_)___ 
  / /   / __ `/ ___/ __ `/ | / / _ \/ /_____/ __ `/ __  / __ `__ \/ / __ \
 / /___/ /_/ / /  / /_/ /| |/ /  __/ /_____/ /_/ / /_/ / / / / / / / / / /
/_____/\__,_/_/   \__,_/ |___/\___/_/      \__,_/\__,_/_/ /_/ /_/_/_/ /_/ 
                                                                          
LOGO;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->line(static::$logo);
        $this->line(Admin::getLongVersion());

        $cmd = $this->argument('cmd');

        if ($cmd == 'seed-settings') {
            $this->seedSettings();
        }

        if ($cmd == 'seed-extensions') {
            $this->seedExtensions();
        }
    }

    protected function seedSettings()
    {
        $this->call('db:seed', ['--class' => SettingSeeder::class]);
    }

    protected function seedExtensions()
    {
        $this->call('db:seed', ['--class' => ExtensionSeeder::class]);
    }

}
