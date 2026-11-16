<?php
namespace App\Models;
class PortfolioImage extends ContentModel
{
    public function portfolio() { return $this->belongsTo(Portfolio::class); }
}
