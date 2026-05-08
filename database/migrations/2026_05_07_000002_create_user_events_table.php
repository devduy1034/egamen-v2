<?php

use Illuminate\Support\Facades\Schema;
use LARAVEL\DatabaseCore\Migrations\Migration;
use LARAVEL\DatabaseCore\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_events', function (Blueprint $table) {
            $table->id();
            $table->string('event_id', 64)->unique();
            $table->string('event_type', 40);
            $table->string('user_id', 64)->nullable();
            $table->string('anonymous_id', 64)->nullable();
            $table->string('session_id', 128);
            $table->string('source', 20)->default('web');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->index(['user_id', 'created_at'], 'table_user_events_user_created_idx');
            $table->index(['anonymous_id', 'created_at'], 'table_user_events_anonymous_created_idx');
            $table->index(['session_id'], 'table_user_events_session_idx');
            $table->index(['event_type', 'created_at'], 'table_user_events_type_created_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_events');
    }
};
