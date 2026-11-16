<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_types', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->timestamps();
        });
        Schema::create('locations', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->string('province')->default('Bali'); $table->timestamps();
        });
        Schema::create('amenities', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('icon')->nullable(); $table->timestamps();
        });
        Schema::create('properties', function (Blueprint $table) {
            $table->id(); $table->string('property_code')->unique(); $table->string('title'); $table->string('slug')->unique();
            $table->foreignId('property_type_id')->constrained()->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->enum('listing_type', ['sale', 'rent'])->index();
            $table->text('short_description')->nullable(); $table->longText('description');
            $table->decimal('price', 20, 2)->nullable()->index(); $table->char('currency', 3)->default('IDR');
            $table->string('price_unit')->nullable(); $table->boolean('show_price')->default(true);
            $table->unsignedSmallInteger('bedrooms')->nullable()->index(); $table->unsignedSmallInteger('bathrooms')->nullable();
            $table->decimal('land_area', 12, 2)->nullable(); $table->decimal('building_area', 12, 2)->nullable();
            $table->unsignedSmallInteger('floors')->nullable(); $table->unsignedSmallInteger('max_guests')->nullable();
            $table->string('furnishing')->nullable(); $table->string('ownership_type')->nullable();
            $table->text('location_description')->nullable();
            $table->decimal('latitude', 10, 7)->nullable(); $table->decimal('longitude', 10, 7)->nullable();
            $table->boolean('show_exact_location')->default(false);
            $table->enum('status', ['available', 'reserved', 'sold', 'rented', 'inactive'])->default('available')->index();
            $table->enum('publication_status', ['draft', 'published'])->default('draft');
            $table->boolean('is_featured')->default(false); $table->string('seo_title')->nullable();
            $table->text('meta_description')->nullable(); $table->string('canonical_url')->nullable();
            $table->timestamp('published_at')->nullable(); $table->timestamps(); $table->softDeletes();
            $table->index(['publication_status', 'published_at']); $table->index(['publication_status', 'is_featured']);
        });
        Schema::create('amenity_property', function (Blueprint $table) {
            $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->foreignId('amenity_id')->constrained()->cascadeOnDelete();
            $table->primary(['property_id', 'amenity_id']);
        });
        Schema::create('property_images', function (Blueprint $table) {
            $table->id(); $table->foreignId('property_id')->constrained()->cascadeOnDelete();
            $table->string('image_path'); $table->string('alt_text')->nullable(); $table->boolean('is_primary')->default(false);
            $table->unsignedInteger('sort_order')->default(0); $table->timestamps(); $table->index(['property_id', 'sort_order']);
        });
        Schema::create('portfolio_categories', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('slug')->unique(); $table->timestamps();
        });
        Schema::create('portfolios', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique();
            $table->foreignId('category_id')->constrained('portfolio_categories')->restrictOnDelete();
            $table->string('location')->nullable(); $table->longText('description'); $table->unsignedSmallInteger('project_year')->nullable();
            $table->string('project_status')->nullable(); $table->string('featured_image')->nullable();
            $table->boolean('is_featured')->default(false); $table->enum('publication_status', ['draft', 'published'])->default('draft')->index();
            $table->string('seo_title')->nullable(); $table->text('meta_description')->nullable(); $table->timestamps();
        });
        Schema::create('portfolio_images', function (Blueprint $table) {
            $table->id(); $table->foreignId('portfolio_id')->constrained()->cascadeOnDelete();
            $table->string('image_path'); $table->string('alt_text')->nullable(); $table->unsignedInteger('sort_order')->default(0); $table->timestamps();
        });
        Schema::create('services', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->text('short_description');
            $table->longText('content'); $table->string('featured_image')->nullable(); $table->string('icon')->nullable();
            $table->string('cta_label')->default('Talk to Our Team'); $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true)->index(); $table->timestamps();
        });
        Schema::create('testimonials', function (Blueprint $table) {
            $table->id(); $table->string('name'); $table->string('role')->nullable(); $table->text('content');
            $table->unsignedTinyInteger('rating')->nullable(); $table->string('source')->nullable();
            $table->string('photo')->nullable(); $table->boolean('is_active')->default(false)->index(); $table->timestamps();
        });
        Schema::create('website_settings', function (Blueprint $table) {
            $table->id(); $table->string('key')->unique(); $table->longText('value')->nullable(); $table->string('type')->default('text'); $table->timestamps();
        });
        Schema::create('pages', function (Blueprint $table) {
            $table->id(); $table->string('title'); $table->string('slug')->unique(); $table->longText('content');
            $table->string('featured_image')->nullable(); $table->string('seo_title')->nullable(); $table->text('meta_description')->nullable();
            $table->enum('publication_status', ['draft', 'published'])->default('draft')->index(); $table->timestamps();
        });
        // Preserve public URLs when an editor changes a published slug.
        Schema::create('slug_redirects', function (Blueprint $table) {
            $table->id(); $table->string('from_path')->unique(); $table->string('to_path'); $table->timestamps();
        });
        // Operational audit trail: no passwords or request payloads are recorded.
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id(); $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action'); $table->string('subject_type'); $table->unsignedBigInteger('subject_id')->nullable();
            $table->timestamps(); $table->index(['subject_type', 'subject_id']);
        });
    }

    public function down(): void
    {
        foreach (['audit_logs', 'slug_redirects', 'pages', 'website_settings', 'testimonials', 'services', 'portfolio_images', 'portfolios', 'portfolio_categories', 'property_images', 'amenity_property', 'properties', 'amenities', 'locations', 'property_types'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
