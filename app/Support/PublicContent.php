<?php

namespace App\Support;

class PublicContent
{
    public static function demoEnabled(): bool
    {
        return app()->environment(['local', 'testing']) || config('propertyme.demo_content_enabled', false);
    }

    public static function description($record): string
    {
        $value = $record->description ?? '';
        if (!$record->is_demo) return $value;
        $paragraphs=preg_split('/\R\s*\R/',$value);
        return implode("\n\n",array_filter($paragraphs,fn($paragraph)=>!preg_match('/^(DEMO\b|For a genuine property inquiry|Replace this example)/i',trim($paragraph))));
    }
}
