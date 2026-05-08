<?php

use Illuminate\Support\Facades\Schema;
use LARAVEL\DatabaseCore\Migrations\Migration;
use LARAVEL\DatabaseCore\Schema\Blueprint;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_embeddings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_id');
            $table->string('locale', 10)->default('vi');
            $table->string('embedding_model', 120);
            $table->unsignedInteger('embedding_dimensions')->default(384);
            $table->double('embedding_norm')->nullable();
            $table->unsignedBigInteger('product_updated_at')->nullable();
            $table->string('source_hash', 64);
            $table->longText('source_text')->nullable();
            $table->longText('embedding');
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'locale'], 'product_embeddings_product_locale_unique');
            $table->index(['locale', 'embedding_model', 'embedding_dimensions'], 'product_embeddings_lookup_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_embeddings');
    }
};
