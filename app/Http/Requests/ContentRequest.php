<?php
namespace App\Http\Requests;
use App\Support\Resources;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
class ContentRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('access-admin') ?? false; }
    protected function prepareForValidation(): void
    {
        if ($this->has('slug')) { $this->merge(['slug' => Str::slug($this->input('slug') ?: ($this->input('title') ?: $this->input('name')))]); }
    }
    public function rules(): array { return Resources::rules($this->route('resource'), $this->route('record') ? (int) $this->route('record') : null); }
}
