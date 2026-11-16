<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\DB;

trait RedirectsSlugs
{
    protected static function bootRedirectsSlugs(): void
    {
        static::updated(function ($model) {
            if ($model->wasChanged('slug')) {
                $prefix = $model->publicPrefix();
                $old = $prefix.'/'.$model->getOriginal('slug');
                $new = $prefix.'/'.$model->slug;
                DB::table('slug_redirects')->where('from_path', $new)->delete();
                DB::table('slug_redirects')->where('to_path', $old)->update(['to_path' => $new]);
                DB::table('slug_redirects')->updateOrInsert(['from_path' => $old], ['to_path' => $new, 'created_at' => now(), 'updated_at' => now()]);
            }
        });
    }
}
