<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->string('payment_proof')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamp('payment_verified_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()->constrained('admins');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['payment_proof', 'payment_status', 'payment_verified_at', 'payment_verified_by']);
        });
    }
}; 