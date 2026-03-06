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
        Schema::table('users', function (Blueprint $table) {
            // Agregar campos si no existen
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }
            if (!Schema::hasColumn('users', 'profile_photo_path')) {
                $table->string('profile_photo_path')->nullable();
            }
            if (!Schema::hasColumn('users', 'timezone')) {
                $table->string('timezone')->default('America/Bogota');
            }
            if (!Schema::hasColumn('users', 'notifications_enabled')) {
                $table->boolean('notifications_enabled')->default(true);
            }
            if (!Schema::hasColumn('users', 'email_notifications_enabled')) {
                $table->boolean('email_notifications_enabled')->default(true);
            }
            if (!Schema::hasColumn('users', 'dark_mode')) {
                $table->boolean('dark_mode')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumnIfExists(['address', 'profile_photo_path', 'timezone', 'notifications_enabled', 'email_notifications_enabled', 'dark_mode']);
        });
    }
};
