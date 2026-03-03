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
        Schema::create('appointments', function (Blueprint $table) {
            $table->integer(column: 'id')->autoIncrement()->nullable(value: false);
            $table->integer('client_id')->unique()->nullable(value: false)->onDelete('cascade');
            $table->integer('team_id')->nullable(value: false)->onDelete('cascade');
            $table->string('appointment_type')->nullable(value: false);
            $table->string('status')->nullable(value: false);
            $table->string('scheduled_date')->unique()->nullable(value: false);   
            $table->string('address')->nullable(value: false);  
            $table->string('description')->nullable(value: false); 
            $table->string('cancelled_at')->nullable(value: true);
            $table->string('cancelled_by')->nullable(value: true);
            $table->string('cancellation_reason')->nullable(value: false);
    
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};