<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIpToLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->string('ip')->nullable()->after('action');
            $table->string('country')->nullable()->after('ip');
            $table->string('city')->nullable()->after('country');
            $table->string('browser')->nullable()->after('city');
            $table->string('platform')->nullable()->after('browser');
            $table->string('device')->nullable()->after('platform');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('logs', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('country');
            $table->dropColumn('city');
            $table->dropColumn('browser');
            $table->dropColumn('platform');
            $table->dropColumn('device');
        });
    }
}
