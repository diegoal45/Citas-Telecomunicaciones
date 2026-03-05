<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('quotations', function (Blueprint $table) {
            $table->integer(column: 'id')->autoIncrement()->nullable(value: false);
            $table->integer('appointment_id')->unique()->nullable(value: false)->onDelete('cascade');
            $table->string('materials')->nullable(value: false);
            $table->string('labor_hours')->nullable(value: false);
            $table->string('required_staff')->nullable(value: false);
            $table->string('price')->unique()->nullable(value: true);   
            $table->string('approved_at')->nullable(value: false);  
            $table->string('rejected_at')->nullable(value: false); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quotations');
    }
};