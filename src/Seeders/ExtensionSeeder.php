<?php

namespace PNS\Admin\Seeders;

use Illuminate\Database\Seeder;
use PNS\Admin\Helpers\AdminHelper;

class ExtensionSeeder extends Seeder
{
    public function run()
    {
        $this->command->info('Seeding extensions table...');
        $model = AdminHelper::getExtensionsModelClass();
        $extensions = [
            [
                'name' => 'Helpers',
                'slug' => 'helpers',
                'install_status' => 0,
                'enabled' => 0,
                'is_default' => 1,
            ],
            [
                'name' => 'Log Viewer',
                'slug' => 'log-viewer',
                'install_status' => 0,
                'enabled' => 0,
                'is_default' => 1,
            ],
            [
                'name' => 'Media Manager',
                'slug' => 'media-manager',
                'install_status' => 0,
                'enabled' => 0,
                'is_default' => 1,
            ],
            [
                'name' => 'Scheduling',
                'slug' => 'scheduling',
                'install_status' => 0,
                'enabled' => 0,
                'is_default' => 1,
            ],
            [
                'name' => 'Settings',
                'slug' => 'settings',
                'install_status' => 0,
                'enabled' => 0,
                'is_default' => 1,
            ],
        ];

        foreach ($extensions as $extension) {
            if ($model::where('slug', $extension['slug'])->exists()) {
                $this->command->info("Extension {$extension['name']} already exists. Skipping...");
                continue;
            }

            $model::create($extension);
            $this->command->info('Extension ' . $extension['name'] . ' created successfully');
        }

        $this->command->info('Extensions table seeded successfully');
    }
}
