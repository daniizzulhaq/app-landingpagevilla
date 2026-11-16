<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Property extends ContentModel
{
    use HasFactory, SoftDeletes, Concerns\RedirectsSlugs;

    protected function casts(): array
    {
        return ['is_demo' => 'boolean', 'price' => 'decimal:2', 'land_area' => 'decimal:2', 'building_area' => 'decimal:2', 'show_price' => 'boolean', 'show_exact_location' => 'boolean', 'is_featured' => 'boolean', 'published_at' => 'datetime'];
    }

    public function propertyType() { return $this->belongsTo(PropertyType::class); }
    public function location() { return $this->belongsTo(Location::class); }
    public function amenities() { return $this->belongsToMany(Amenity::class); }
    public function images() { return $this->hasMany(PropertyImage::class)->orderByDesc('is_primary')->orderBy('sort_order')->orderBy('id'); }
    public function publicPrefix(): string { return '/properties'; }
    public function scopePublished($query) { return $query->where('publication_status', 'published')->whereNotNull('published_at')->where('published_at', '<=', now())->where('status', '!=', 'inactive')->when(! app()->environment(['local', 'testing']), fn ($q) => $q->where('is_demo', false)); }
    public function getCoverUrlAttribute(): string { return $this->images->first() ? Storage::disk('public')->url($this->images->first()->image_path) : asset('images/architecture.svg'); }
    public function getFormattedPriceAttribute(): string
    {
        if (! $this->show_price || $this->price === null) { return __('Price on request'); }
        $formatter = new \NumberFormatter(app()->getLocale()==='id' ? 'id_ID' : 'en_US', \NumberFormatter::CURRENCY);
        $formatter->setAttribute(\NumberFormatter::FRACTION_DIGITS, $this->currency === 'IDR' ? 0 : 2);
        return $formatter->formatCurrency((float) $this->price, $this->currency).($this->price_unit ? ' / '.__($this->price_unit) : '');
    }
}
