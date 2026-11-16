<?php
namespace App\Models;
class PropertyType extends ContentModel
{
    public function properties() { return $this->hasMany(Property::class); }
}
