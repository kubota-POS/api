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
            $table->string('invoice_no')->unique();
            $table->string('customer_name')->nullable()->default(null);
            $table->string('customer_phone')->nullable()->default(null);
            $table->string('customer_email')->nullable()->default(null);
            $table->string('customer_address')->nullable()->default(null);
            $table->longtext('invoice_data');
            $table->float('total_amount');
            $table->float('pay_amount');
            $table->float('discount')->nullable()->default(0);
            $table->float('credit_mount')->nullable()->default(0);
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
