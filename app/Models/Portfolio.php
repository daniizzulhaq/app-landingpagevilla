<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class Portfolio extends ContentModel
{
    use HasFactory, Concerns\RedirectsSlugs;
    protected function casts(): array { return ['is_demo' => 'boolean', 'is_featured' => 'boolean']; }
    public function category() { return $this->belongsTo(PortfolioCategory::class); }
    public function images() { return $this->hasMany(PortfolioImage::class)->orderBy('sort_order')->orderBy('id'); }
    public function scopePublished($query) { return $query->where('publication_status', 'published')->when(! \App\Support\PublicContent::demoEnabled(), fn ($q) => $q->where('is_demo', false)); }
    public function publicPrefix(): string { return '/portfolio'; }
}
