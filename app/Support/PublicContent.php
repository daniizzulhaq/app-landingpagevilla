<?php

namespace App\Support;

class PublicContent
{
    public static function demoEnabled(): bool
    {
        return app()->environment(['local', 'testing']) || self::hostedDemoEnabled();
    }

    public static function hostedDemoEnabled(): bool
    {
        return config('propertyme.demo_content_enabled', false)
            || app(\App\Services\Settings::class)->enabled(\App\Services\BundledPropertyInstaller::PREVIEW_KEY);
    }

    public static function description($record): string
    {
        $value = $record->description ?? '';
        if (!$record->is_demo) return $value;
        $paragraphs=preg_split('/\R\s*\R/',$value);
        $value = implode("\n\n",array_filter($paragraphs,fn($paragraph)=>!preg_match('/^(DEMO\b|For a genuine property inquiry|Replace this example)/i',trim($paragraph))));
        return str_replace([
            'Explore the gallery and specifications to preview the property browsing experience.',
            'Use this example to explore land listing specifications and property inquiries.',
            'An illustrative landscape concept with',
        ], [
            'Explore the gallery and property specifications.',
            'Contact our team for property details and inquiries.',
            'A landscape with',
        ], $value);
    }
}
