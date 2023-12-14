<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('product', function (Blueprint $table) {
            $table->increments('product_id');
            $table->unsignedInteger('category_id');
            $table->string('product_code')->unique();
            $table->string('product_name')->unique();
            $table->string('brand')->nullable();
            $table->integer('purchase_price');
            $table->tinyInteger('discount')->default(0);
            $table->integer('selling_price');
            $table->integer('stock');
            $table->timestamps();

            // Foreign key constraint
            $table->foreign('category_id')
                ->references('category_id')
                ->on('category')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('product');
    }
}
