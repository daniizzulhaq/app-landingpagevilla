<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('services', function (Blueprint $table) { $table->text('workflow_steps')->nullable(); $table->text('faqs')->nullable(); }); }
    public function down(): void { Schema::table('services', fn (Blueprint $table) => $table->dropColumn(['workflow_steps', 'faqs'])); }
};
