<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('category_id')->unsigned()->nullable()->default(null);
            $table->string('code')->unique();
            $table->string('eng_name')->nullable()->default(null);
            $table->string('mm_name')->nullable()->default(null);
            $table->string('model')->nullable()->default(null);
            $table->integer('qty')->default(0);
            $table->string('price')->nullable()->default(null);
            $table->string('percentage')->nullable()->default(0);
            $table->string('location')->nullable()->default(null);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->foreign('category_id')->references('id')->on('category')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
