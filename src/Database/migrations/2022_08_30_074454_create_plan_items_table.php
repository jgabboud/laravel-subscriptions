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
        Schema::create('plan_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('plan_id');
            $table->string('slug');
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('value');
            $table->integer('resettable_period')->default(0);
            $table->string('resettable_interval')->default('month');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['plan_id', 'slug']);
            $table->foreign('plan_id')->references('id')->on('plans');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('plan_items');
    }
};
