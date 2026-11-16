<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('team_members', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('role');
            $table->text('bio')->nullable(); $table->string('photo')->nullable();
            $table->unsignedInteger('sort_order')->default(0); $table->boolean('is_active')->default(false)->index(); $table->timestamps();
        });
        Schema::create('content_translations', function (Blueprint $table) {
            $table->id(); $table->string('translatable_type', 100); $table->unsignedBigInteger('translatable_id');
            $table->char('locale', 2); $table->json('fields'); $table->timestamps();
            $table->unique(['translatable_type','translatable_id','locale'], 'content_translation_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('content_translations');
        Schema::dropIfExists('team_members');
    }
};
