<?php

namespace App\Models\Concerns;

use App\Models\ContentTranslation;
use App\Support\TranslationFields;

trait HasTranslations
{
    protected static function bootHasTranslations(): void
    {
        static::addGlobalScope('localized-content', function ($builder) {
            if (app()->getLocale() !== 'en' && TranslationFields::for(static::class)) $builder->with('contentTranslations');
        });
        static::deleted(function ($model) {
            if (method_exists($model,'isForceDeleting') && !$model->isForceDeleting()) return;
            $model->contentTranslations()->delete();
        });
    }

    public function contentTranslations() { return $this->morphMany(ContentTranslation::class,'translatable'); }

    public function translationFields(string $locale = 'id'): array
    {
        return $this->contentTranslations->firstWhere('locale',$locale)?->fields ?? [];
    }

    public function saveTranslation(array $fields, string $locale = 'id'): void
    {
        $fields = array_filter(array_intersect_key($fields,array_flip(TranslationFields::for(static::class))),fn($value)=>is_string($value) && trim($value)!=='');
        if ($fields) $this->contentTranslations()->updateOrCreate(['locale'=>$locale],['fields'=>$fields]);
        else $this->contentTranslations()->where('locale',$locale)->delete();
        $this->unsetRelation('contentTranslations');
    }

    public function getAttribute($key)
    {
        $value=parent::getAttribute($key);
        if (app()->getLocale()==='en' || !is_string($key) || !in_array($key,TranslationFields::for(static::class),true)) return $value;
        return $this->translationFields(app()->getLocale())[$key] ?? $value;
    }
}
