<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

abstract class ContentModel extends Model
{
    use Concerns\HasTranslations;
    protected $guarded = ['id'];
}
