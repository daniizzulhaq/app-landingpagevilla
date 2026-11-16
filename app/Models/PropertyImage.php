<?php
namespace App\Models;
class PropertyImage extends ContentModel
{
    protected function casts(): array { return ['is_primary' => 'boolean']; }
    public function property() { return $this->belongsTo(Property::class); }
}
