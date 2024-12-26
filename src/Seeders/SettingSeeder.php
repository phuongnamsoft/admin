<?php

namespace PNS\Admin\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingSeeder extends Seeder
{
    public function run()
    {
        // Seed setting_groups first
        $groups = [
            ['name' => 'General', 'status' => 1, 'settings' => [
                ['name' => 'default_meta_title', 'label' => 'Default Title', 'value' => null,    'cast_type' => 'text', 'sort' => 2, 'status' => 1],
                ['name' => 'default_meta_description', 'label' => 'Default Meta Description', 'value' => null, 'cast_type' => 'text', 'sort' => 3, 'status' => 1],
                ['name' => 'default_meta_keywords', 'label' => 'Default Keywords', 'value' => null,  'cast_type' => 'text', 'sort' => 4, 'status' => 1],
                ['name' => 'default_app_name', 'label' => 'Default App Name', 'value' => null, 'cast_type' => 'text', 'sort' => 0, 'status' => 1],
                ['name' => 'default_app_image', 'label' => 'Default App Image', 'value' => null, 'cast_type' => 'image', 'sort' => 1, 'status' => 1],
            ]],
            ['name' => 'Upload', 'status' => 1, 'settings' => [
                ['name' => 'upload_temp_file', 'label' => 'Upload Temp File', 'value' => null, 'cast_type' => 'boolean', 'sort' => 10, 'status' => 1],
            ]],
            ['name' => 'Login', 'status' => 1, 'settings' => [
                ['name' => 'login_with_google', 'label' => 'Login With Google', 'value' => null, 'cast_type' => 'boolean', 'sort' => 1, 'status' => 1],
                ['name' => 'login_with_facebook', 'label' => 'Login With Facebook', 'value' => null, 'cast_type' => 'boolean', 'sort' => 3, 'status' => 1],
                ['name' => 'google_recaptcha_enable', 'label' => 'Google Recaptcha Enable', 'value' => null, 'cast_type' => 'boolean', 'sort' => 4, 'status' => 1],
                ['name' => 'google_recaptcha_key', 'label' => 'Google Recaptcha Site Key', 'value' => null, 'cast_type' => 'text', 'sort' => 6, 'status' => 1],
                ['name' => 'google_recaptcha_secret', 'label' => 'Google Recaptcha Site Secret', 'value' => null, 'cast_type' => 'text', 'sort' => 7, 'status' => 1],
            ]],
            ['name' => 'Email Settings', 'status' => 1, 'settings' => [
                ['name' => 'smtp_enabled', 'label' => 'SMTP Enabled', 'value' => null, 'cast_type' => 'boolean', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_host', 'label' => 'SMTP Host', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_port', 'label' => 'SMTP Port', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_username', 'label' => 'SMTP Username', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_password', 'label' => 'SMTP Password', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_encryption', 'label' => 'SMTP Encryption', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_from_address', 'label' => 'SMTP From Address', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_from_name', 'label' => 'SMTP From Name', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_reply_to', 'label' => 'SMTP Reply To', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'smtp_reply_to_name', 'label' => 'SMTP Reply To Name', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
            ]],
            ['name' => 'Internal CSS, JS', 'status' => 1, 'settings' => [
                ['name' => 'minified_css_path', 'label' => 'Minified CSS Path', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'minified_js_path', 'label' => 'Minified JS Path', 'value' => null, 'cast_type' => 'text', 'sort' => 1, 'status' => 1],
                ['name' => 'css_inline', 'label' => 'CSS Inline', 'value' => null, 'cast_type' => 'css', 'sort' => 1, 'status' => 1],
                ['name' => 'js_inline_head', 'label' => 'JS Inline (head)', 'value' => null, 'cast_type' => 'js', 'sort' => 1, 'status' => 1],
                ['name' => 'js_inline_body', 'label' => 'JS Inline (body)', 'value' => null, 'cast_type' => 'js', 'sort' => 4, 'status' => 1],
                ['name' => 'js_files', 'label' => 'JS Files', 'value' => null, 'cast_type' => 'json_assoc', 'sort' => 4, 'status' => 1],
                ['name' => 'css_files', 'label' => 'CSS Files', 'value' => null, 'cast_type' => 'json_assoc', 'sort' => 4, 'status' => 1],
            ]],
        ];

        foreach ($groups as $group) {
            $groupId = DB::table('setting_groups')->insertGetId([
                'name' => $group['name'],
                'status' => $group['status'],
            ]);

            foreach ($group['settings'] as $setting) {
                if (DB::table('settings')->where('name', $setting['name'])->exists()) {
                    $this->command->info("Setting {$setting['label']} already exists");
                    continue;
                }

                $setting['group_id'] = $groupId;
                DB::table('settings')->insert($setting);

                $this->command->info("Setting {$setting['label']} created");
            }
        }
    }
}
