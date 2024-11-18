<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('inventory_logs')) {
            Schema::create('inventory_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('medicine_id')->constrained()->onDelete('cascade');
                $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
                $table->string('action_type');
                $table->integer('quantity_change')->nullable();
                $table->integer('old_quantity')->nullable();
                $table->integer('new_quantity')->nullable();
                $table->decimal('old_price', 10, 2)->nullable();
                $table->decimal('new_price', 10, 2)->nullable();
                $table->string('old_status')->nullable();
                $table->string('new_status')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('inventory_logs');
    }
}; 