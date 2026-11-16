<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContentTranslation extends Model
{
    protected $guarded = ['id'];
    protected function casts(): array { return ['fields' => 'array']; }
    public function translatable() { return $this->morphTo(); }
}
