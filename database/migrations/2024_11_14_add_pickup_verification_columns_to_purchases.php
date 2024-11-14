<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            if (!Schema::hasColumn('purchases', 'user_pickup_verified')) {
                $table->boolean('user_pickup_verified')->default(false);
            }
            if (!Schema::hasColumn('purchases', 'admin_pickup_verified')) {
                $table->boolean('admin_pickup_verified')->default(false);
            }
            if (!Schema::hasColumn('purchases', 'user_verified_at')) {
                $table->timestamp('user_verified_at')->nullable();
            }
            if (!Schema::hasColumn('purchases', 'admin_verified_at')) {
                $table->timestamp('admin_verified_at')->nullable();
            }
            if (!Schema::hasColumn('purchases', 'completed_at')) {
                $table->timestamp('completed_at')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn([
                'user_pickup_verified',
                'admin_pickup_verified',
                'user_verified_at',
                'admin_verified_at',
                'completed_at'
            ]);
        });
    }
}; 