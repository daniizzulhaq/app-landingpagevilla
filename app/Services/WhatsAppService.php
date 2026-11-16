<?php

namespace App\Services;

use App\Models\Property;

class WhatsAppService
{
    public function __construct(private Settings $settings) {}

    public static function normalize(?string $number): ?string
    {
        $digits = preg_replace('/\D/', '', $number ?? '');
        if (! preg_match('/[1-9]/', $digits)) { return null; }
        if (str_starts_with($digits, '00')) { $digits = substr($digits, 2); }
        if (str_starts_with($digits, '0')) { $digits = '62'.substr($digits, 1); }
        return preg_match('/^[1-9][0-9]{7,14}$/', $digits) ? $digits : null;
    }

    public function phone(): ?string
    {
        return self::normalize($this->settings->get('company.whatsapp'));
    }

    public function message(string $type = 'general', ?Property $property = null): string
    {
        if ($property) {
            $link = route('properties.show', app()->getLocale()==='id' ? ['slug'=>$property->slug,'lang'=>'id'] : $property->slug);
            if (in_array($property->status, ['sold', 'rented', 'reserved'])) {
                return __("Hello PropertyME, I saw :property is :status. Could you help me find similar available properties? Property link: :link",['property'=>$property->title,'status'=>__($property->status),'link'=>$link]);
            }
            if ($property->listing_type === 'rent') {
                return __("Hello PropertyME, I'd like to inquire about renting :property (For Rent). Could you help me check available dates, rental rates, guest capacity, and the booking arrangements? Property link: :link",['property'=>$property->title,'link'=>$link]);
            }
            return __("Hello PropertyME, I'm interested in :property (For Sale). Could you provide more information about the price, availability, and viewing arrangements? Property link: :link",['property'=>$property->title,'link'=>$link]);
        }

        return __(match ($type) {
            'investment', 'invest-build' => "Hello PropertyME, I'd like to discuss land sourcing, villa design and development in Bali. Could we arrange an investment consultation?",
            'list-sell-rent' => "Hello PropertyME, I'd like to list my property for sale or rent in Bali. Could you explain the listing process?",
            'property-management' => "Hello PropertyME, I'm interested in your property management services for my villa in Bali. Could you share more information?",
            'buy' => "Hello PropertyME, I'm interested in buying a property in Bali. Could you help me explore suitable properties?",
            'buy-rent' => "Hello PropertyME, I'm interested in buying or renting a property in Bali. Could you help me explore suitable options?",
            'rent' => "Hello PropertyME, I'd like to rent a villa in Bali. Could you help me check dates, prices and availability?",
            default => "Hello PropertyME, I'd like to discuss my property plans in Bali. Could you help me arrange a consultation?",
        });
    }

    public function url(string $type = 'general', ?Property $property = null): ?string
    {
        $phone = $this->phone();
        return $phone ? 'https://wa.me/'.$phone.'?text='.rawurlencode($this->message($type, $property)) : null;
    }
}
