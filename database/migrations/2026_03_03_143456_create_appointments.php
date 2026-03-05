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
            $table->increments('id');
            $table->integer('client_id');
            $table->integer('team_id')->nullable();
            $table->string('appointment_type');
            $table->string('status');
            $table->timestamp('scheduled_date');
            $table->string('address');
            $table->string('description');
            $table->timestamp('cancelled_at')->nullable();
            $table->integer('cancelled_by')->nullable();            $table->text('cancellation_reason')->nullable();

            // foreign keys must be added after the columns are defined
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('team_id')->references('id')->on('teams')->onDelete('cascade');
            $table->foreign('cancelled_by')->references('id')->on('users');

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