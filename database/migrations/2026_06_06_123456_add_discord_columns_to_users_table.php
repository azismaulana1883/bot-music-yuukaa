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
            $table->string('password')->nullable()->change();
            $table->string('discord_id')->nullable()->unique()->index();
            $table->string('avatar')->nullable();
            $table->text('discord_token')->nullable();
            $table->text('discord_refresh_token')->nullable();
            $table->timestamp('discord_token_expires_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password')->nullable(false)->change();
            $table->dropColumn([
                'discord_id',
                'avatar',
                'discord_token',
                'discord_refresh_token',
                'discord_token_expires_at'
            ]);
        });
    }
};
