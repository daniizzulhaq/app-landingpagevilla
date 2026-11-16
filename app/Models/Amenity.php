<?php
namespace App\Models;
class Amenity extends ContentModel
{
    public function properties() { return $this->belongsToMany(Property::class); }
}
