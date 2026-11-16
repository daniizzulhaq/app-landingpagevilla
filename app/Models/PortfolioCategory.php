<?php
namespace App\Models;
class PortfolioCategory extends ContentModel
{
    public function portfolios() { return $this->hasMany(Portfolio::class, 'category_id'); }
}
