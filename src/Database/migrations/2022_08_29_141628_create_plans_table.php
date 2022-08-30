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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('slug');
            $table->string('name');
            $table->string('description')->nullable();
            $table->decimal('price')->default('0.00');
            $table->string('currency', 3);             
            $table->integer('trial_period')->default(0); #free trial period
            $table->string('trial_interval')->default('day');
            $table->integer('invoice_period')->default(0); #how a much the subscription lasts
            $table->string('invoice_interval')->default('month');
            $table->integer('grace_period')->default(0); #notice period for late payment
            $table->string('grace_interval')->default('day');
            $table->integer('active_subscribers_limit')->nullable();
            $table->date('issue_date')->nullable();
            $table->integer('sort_order')->default(0);    
            $table->booleam('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plans');
    }
};
