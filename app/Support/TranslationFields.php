<?php

namespace App\Support;

class TranslationFields
{
    public static function for(string $model): array
    {
        return match (class_basename($model)) {
            'Property' => ['title','short_description','description','furnishing','seo_title','meta_description'],
            'Portfolio' => ['title','description','project_status','seo_title','meta_description'],
            'Service' => ['title','short_description','hero_headline','content','cta_label','workflow_steps','faqs'],
            'Page' => ['title','content','seo_title','meta_description'],
            'TeamMember' => ['role','bio'],
            'Testimonial' => ['role','content'],
            'PropertyType','PortfolioCategory','Amenity' => ['name'],
            default => [],
        };
    }

    public static function rules(string $model): array
    {
        $fields=self::for($model);
        if (!$fields) return [];
        $rules=['translations'=>['sometimes','array:'.implode(',',$fields)]];
        foreach ($fields as $field) {
            $limit = match ($field) {'seo_title'=>70,'meta_description'=>170,'title','name','role','cta_label','furnishing','hero_headline','project_status'=>255, default=>50000};
            $rules['translations.'.$field]=['nullable','string','max:'.$limit];
        }
        return $rules;
    }
}
