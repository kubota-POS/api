<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('invoice_id')->unique()->unsigned()->nullable()->default(null);
            $table->foreignId('customer_id')->nullable();
            $table->longtext('invoice_data')->nullable();
            $table->float('total_amount');
            $table->float('discount')->nullable();
            $table->float('cash_back')->nullable();
            $table->float('pay_amount');
            $table->softDeletes();
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
        Schema::dropIfExists('invoice');
    }
}
