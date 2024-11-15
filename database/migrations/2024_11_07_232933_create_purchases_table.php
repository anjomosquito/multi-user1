    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    class CreatePurchasesTable extends Migration
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
                $table->timestamp('purchase_date')->default(DB::raw('CURRENT_TIMESTAMP'));
                $table->string('status')->default('pending');

                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('medicine_id')->references('id')->on('medicines')->onDelete('cascade');
            });
        }

        public function down()
        {
            Schema::dropIfExists('purchases');
        }
    }
