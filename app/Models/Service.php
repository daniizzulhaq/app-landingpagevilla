<?php
namespace App\Models;
class Service extends ContentModel
{
    use Concerns\RedirectsSlugs;
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function publicPrefix(): string { return '/services'; }
}
