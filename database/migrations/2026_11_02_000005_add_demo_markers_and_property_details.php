<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['users', 'properties', 'portfolios', 'testimonials'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->boolean('is_demo')->default(false)->index());
        }
        Schema::table('properties', function (Blueprint $table) {
            $table->string('development_status')->nullable()->index();
            $table->unsignedSmallInteger('year_built')->nullable();
            $table->unsignedSmallInteger('lease_years')->nullable();
            $table->string('handover')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('properties', fn (Blueprint $table) => $table->dropColumn(['development_status', 'year_built', 'lease_years', 'handover']));
        foreach (['testimonials', 'portfolios', 'properties', 'users'] as $name) {
            Schema::table($name, fn (Blueprint $table) => $table->dropColumn('is_demo'));
        }
    }
};
