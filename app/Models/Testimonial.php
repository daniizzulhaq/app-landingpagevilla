<?php
namespace App\Models;
class Testimonial extends ContentModel
{
    protected function casts(): array { return ['is_demo' => 'boolean', 'is_active' => 'boolean']; }
}
