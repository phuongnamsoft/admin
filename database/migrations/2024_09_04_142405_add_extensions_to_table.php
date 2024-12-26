<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use PNS\Admin\Helpers\AdminHelper;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $table = AdminHelper::getExtensionsTable();
        Schema::table($table, function (Blueprint $table) {
            $table->tinyInteger('install_status')->nullable()->default(0)->after('enabled');
            $table->text('install_logs')->nullable()->after('install_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $table = AdminHelper::getExtensionsTable();
        Schema::table($table, function (Blueprint $table) {
            $table->dropColumn(['install_status', 'install_logs']);
        });

        $model = AdminHelper::getExtensionsModelClass();
        $model::whereIn('slug', ['helpers', 'log-viewer', 'media-manager', 'scheduling'])->delete();
    }
};
