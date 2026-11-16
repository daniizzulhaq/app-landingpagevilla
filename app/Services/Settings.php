<?php
namespace App\Services;
use App\Models\WebsiteSetting;
class Settings
{
    private ?array $values = null;
    public function get(string $key, mixed $default = null): mixed
    {
        $this->values ??= WebsiteSetting::query()->pluck('value', 'key')->all();
        if (app()->getLocale()==='id' && self::translatable($key) && !empty($this->values[$key.'.id'])) return $this->values[$key.'.id'];
        return $this->values[$key] ?? $default;
    }
    public static function translatable(string $key): bool
    {
        return (str_starts_with($key,'homepage.') && !str_ends_with($key,'_url') && !str_ends_with($key,'_image')) || in_array($key,['company.description','company.tagline','company.mission','company.approach','company.office_hours','seo.title','seo.description'],true);
    }
    public function enabled(string $key): bool { return filter_var($this->get($key, false), FILTER_VALIDATE_BOOLEAN); }
    public function forget(): void { $this->values = null; }
}
