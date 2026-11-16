<?php
namespace App\Support;
class SettingFields
{
    public static function groups(): array
    {
        $groups = [
            'company' => [
                'company.name' => ['Company name', 'text'], 'company.tagline' => ['Tagline', 'text'], 'company.description' => ['Company description', 'textarea'],
                'company.logo' => ['Logo (transparent PNG or WebP)', 'image'], 'company.favicon' => ['Favicon', 'image'],
                'company.address' => ['Office address', 'textarea'], 'company.email' => ['Email', 'email'], 'company.whatsapp' => ['WhatsApp (international format)', 'tel'],
                'company.instagram' => ['Instagram profile URL', 'url'], 'company.office_hours' => ['Office hours', 'text'],
                'company.latitude' => ['Office latitude', 'decimal'], 'company.longitude' => ['Office longitude', 'decimal'],
                'company.support_24_7' => ['Our operations provide verified 24/7 support', 'checkbox'],
                'company.legal_name'=>['Registered company name (if available)','text'],
                'company.registration_number'=>['Company registration number (if available)','text'],
                'company.registration_verified'=>['Company registration details are verified for public display','checkbox'],
                'company.founded_year'=>['Year established (verified, optional)','number'],
                'company.mission'=>['Company mission','textarea'],'company.approach'=>['Our approach','textarea'],
            ],
            'homepage' => [
                'homepage.hero_eyebrow' => ['Hero location label', 'text'], 'homepage.hero_headline' => ['Hero headline', 'text'],
                'homepage.hero_subheadline' => ['Hero introduction', 'textarea'], 'homepage.hero_image' => ['Hero image', 'image'],
                'homepage.hero_cta_label' => ['Primary button label', 'text'], 'homepage.hero_cta_url' => ['Primary button URL (internal path or https URL)', 'text'],
                'homepage.about_title' => ['About heading', 'text'], 'homepage.about_body' => ['About paragraph', 'textarea'], 'homepage.about_image' => ['About image', 'image'],
                'homepage.featured_title' => ['Featured properties heading', 'text'], 'homepage.featured_body' => ['Featured properties introduction', 'textarea'],
                'homepage.investment_title' => ['Investment heading', 'text'], 'homepage.investment_body' => ['Investment introduction', 'textarea'],
                'homepage.management_title' => ['Management heading', 'text'], 'homepage.management_body' => ['Management introduction', 'textarea'],
                'homepage.final_title' => ['Final call to action heading', 'text'], 'homepage.final_body' => ['Final call to action introduction', 'textarea'],
            ],
            'seo' => ['seo.title' => ['Default title', 'text'], 'seo.description' => ['Default meta description', 'textarea'], 'seo.og_image' => ['Social share image', 'image']],
        ];
        foreach (['company','homepage','seo'] as $group) {
            $fields=[];
            foreach ($groups[$group] as $key=>[$label,$type]) if (\App\Services\Settings::translatable($key) && in_array($type,['text','textarea'],true)) $fields[$key.'.id']=[$label.' · Indonesia',$type];
            $groups[$group.'-id']=$fields;
        }
        return $groups;
    }
}
