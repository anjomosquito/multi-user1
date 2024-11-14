<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->boolean('admin_pickup_verified')->default(false);
            $table->boolean('user_pickup_verified')->default(false);
            $table->timestamp('admin_verified_at')->nullable();
            $table->timestamp('user_verified_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'admin_pickup_verified',
                'user_pickup_verified',
                'admin_verified_at',
                'user_verified_at'
            ]);
        });
    }
}; 