<?php
namespace App\Support;
use Illuminate\Support\Facades\Storage;
class Images
{
    public static function url(?string $path, string $fallback = 'exterior'): string
    {
        if ($path) { return Storage::disk('public')->url($path); }
        $local = match ($fallback) { 'interior'=>'interior', 'pool'=>'jungle', 'architecture'=>'courtyard', default=>'hero' };
        if (is_file(public_path('images/demo/'.$local.'.webp'))) { return asset('images/demo/'.$local.'.webp'); }
        $id = match ($fallback) { 'interior' => '1600210492486-724fe5c67fb0', 'pool' => '1613977257363-707ba9348227', 'architecture' => '1600607687920-4e2a09cf159d', default => '1613490493576-7fde63acd811' };
        return 'https://images.unsplash.com/photo-'.$id.'?auto=format&fit=crop&w=2000&q=85';
    }
}
