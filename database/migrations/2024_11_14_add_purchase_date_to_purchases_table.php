<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            // Add purchase_date column if it doesn't exist
            if (!Schema::hasColumn('purchases', 'purchase_date')) {
                $table->timestamp('purchase_date')->nullable();
            }
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn('purchase_date');
        });
    }
}; 