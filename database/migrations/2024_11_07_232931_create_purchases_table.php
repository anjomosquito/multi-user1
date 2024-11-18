<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('medicine_id');
            $table->integer('quantity');
            $table->string('name');
            $table->decimal('lprice', 8, 2);
            $table->decimal('mprice', 8, 2);
            $table->decimal('hprice', 8, 2);
            $table->string('dosage');
            $table->date('expdate');
            $table->string('status')->default('pending');
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('purchase_date')->default(DB::raw('CURRENT_TIMESTAMP'));
            $table->string('transaction_number', 10)->unique()->nullable();
            $table->boolean('ready_for_pickup')->default(false);
            $table->timestamp('pickup_ready_at')->nullable();
            $table->timestamp('pickup_deadline')->nullable();
            $table->boolean('user_pickup_verified')->default(false);
            $table->boolean('admin_pickup_verified')->default(false);
            $table->timestamp('user_verified_at')->nullable();
            $table->timestamp('admin_verified_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('confirmed_by')->nullable();
            $table->string('payment_proof')->nullable();
            $table->string('payment_status')->default('pending');
            $table->timestamp('payment_verified_at')->nullable();
            $table->foreignId('payment_verified_by')->nullable()->constrained('admins');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            $table->foreign('confirmed_by')->references('id')->on('admins');
        });
    }

    public function down()
    {
        Schema::dropIfExists('purchases');
    }
}; 