<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSecondItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mysql2')->create('second_items', function (Blueprint $table) {
            $table->id();
            $table->string('m_code')->unique();
            $table->string('m_name')->nullable();
            $table->string('m_photo')->nullable();
            $table->integer('m_qty')->nullable();
            $table->string('price_code')->nullable();
            $table->string('sell_percentage')->nullable();
            $table->string('location')->nullable();
            $table->string('c_date')->nullable();
            $table->string('m_active')->nullable();
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
        Schema::connection('mysql2')->dropIfExists('second_items');
    }
}
