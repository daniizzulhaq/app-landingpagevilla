<?php
namespace App\Http\Requests;
use App\Services\{MediaService, WhatsAppService};
use App\Support\SettingFields;
use Illuminate\Foundation\Http\FormRequest;
class SettingRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('access-admin') ?? false; }
    public function rules(): array
    {
        $group = $this->route('group', 'company'); $rules = [];
        foreach (SettingFields::groups()[$group] ?? [] as $key => [$label, $type]) {
            $name = str_replace('.', '__', $key);
            $rules[$name] = match ($type) {
                'image' => MediaService::RULES, 'checkbox' => ['sometimes', 'boolean'], 'email' => ['nullable', 'email', 'max:255'],
                'number'=>['nullable','integer','between:1900,'.date('Y')],
                'url' => ['nullable', 'url:http,https', 'max:255'], 'textarea' => ['nullable', 'string', 'max:5000'],
                'decimal' => ['nullable', 'numeric', str_contains($key, 'latitude') ? 'between:-90,90' : 'between:-180,180'],
                default => ['nullable', 'string', 'max:255'],
            };
        }
        if ($group === 'company') {
            $rules['company__name'] = ['required', 'string', 'max:100'];
            $rules['company__whatsapp'][] = function ($attribute, $value, $fail) { if ($value && ! WhatsAppService::normalize($value)) { $fail('Enter a valid international WhatsApp number (8–15 digits).'); } };
        }
        if ($group === 'homepage') {
            $rules['homepage__hero_cta_url'][] = function ($attribute, $value, $fail) { if ($value && ! preg_match('~^(https://[^\s]+|/(?!/)[a-zA-Z0-9/_?=&.\-\#]*)$~', $value)) { $fail('Use an internal /path or a complete https:// URL.'); } };
        }
        return $rules;
    }
}
