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
        Schema::table('quotations', function (Blueprint $table) {
            // Cambiar approved_at y rejected_at a timestamp nullable
            $table->timestamp('approved_at')->nullable()->change();
            $table->timestamp('rejected_at')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('approved_at')->nullable(false)->change();
            $table->string('rejected_at')->nullable(false)->change();
        });
    }
};
