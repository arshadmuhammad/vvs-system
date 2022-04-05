<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sold_pins', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('pin')->nullable(false);
            $table->string('serial')->nullable();
            $table->dateTime('expiry_date')->nullable();
            $table->dateTime('sold_date')->nullable();
            $table->string('reference')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sold_pins');
    }
};
