<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('notification_settings')->nullable();
        });

        // Set default values for existing users
        DB::table('users')->update([
            'notification_settings' => json_encode([
                'purchase_updates' => true,
                'pickup_reminders' => true
            ])
        ]);
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_settings');
        });
    }
}; 