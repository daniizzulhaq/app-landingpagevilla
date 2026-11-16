<?php
namespace App\Models;
class Location extends ContentModel
{
    public function properties() { return $this->hasMany(Property::class); }
}
