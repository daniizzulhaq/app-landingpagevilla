<?php
namespace App\Models;
class Page extends ContentModel
{
    use Concerns\RedirectsSlugs;
    protected function casts(): array { return ['is_indexable' => 'boolean']; }
    public function publicPrefix(): string { return ''; }
}
