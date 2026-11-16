<?php

namespace Tests\Feature;

use App\Models\{Property, WebsiteSetting};
use App\Services\{Settings, WhatsAppService};
use Tests\PropertymeTestCase;

class WhatsAppConversionTest extends PropertymeTestCase
{
    private function configurePhone(): void
    {
        WebsiteSetting::where('key','company.whatsapp')->update(['value'=>'6281234567890']);
    }

    public function test_each_property_card_has_a_direct_whatsapp_inquiry(): void
    {
        $this->configurePhone(); $property=Property::factory()->create(['title'=>'Palm & Garden Villa','is_featured'=>true]);
        $this->get('/properties')->assertOk()->assertSee('Ask on WhatsApp')->assertSee('https://wa.me/6281234567890?text=',false)->assertSee(rawurlencode(route('properties.show',$property->slug)),false)->assertSee('data-contact-channel="whatsapp"',false);
        $this->get('/')->assertOk()->assertSee('Ask on WhatsApp');
    }

    public function test_rental_messages_ask_about_dates_rates_and_guest_capacity(): void
    {
        $this->configurePhone(); $property=Property::factory()->create(['listing_type'=>'rent']);
        $service=new WhatsAppService(new Settings); parse_str(parse_url($service->url('general',$property),PHP_URL_QUERY),$query);
        foreach(['available dates','rental rates','guest capacity',route('properties.show',$property->slug)] as $part) $this->assertStringContainsString($part,$query['text']);
        $this->get('/properties')->assertOk()->assertSee('Check Dates on WhatsApp');
    }

    public function test_sold_property_ctas_only_offer_similar_properties(): void
    {
        $this->configurePhone(); $property=Property::factory()->create(['status'=>'sold']);
        $this->get('/properties')->assertOk()->assertSee('Ask for Similar Properties');
        $service=new WhatsAppService(new Settings); $this->assertStringContainsString('similar available properties',rawurldecode($service->url('general',$property)));
    }

    public function test_contact_has_clickable_whatsapp_icons_and_all_inquiry_types(): void
    {
        $this->configurePhone(); $response=$this->get('/contact')->assertOk()->assertSee('Start a WhatsApp Conversation')->assertSee('Open WhatsApp chat with PropertyME')->assertSee('https://wa.me/6281234567890?text=',false);
        foreach(['Find your next property','Plan your Bali stay','Invest &amp; build','List your property','Manage your villa'] as $title) $response->assertSee($title,false);
        foreach(['invest-build','list-sell-rent','property-management','buy-rent'] as $slug) $this->get('/services/'.$slug)->assertOk()->assertSee('data-contact-channel="whatsapp"',false);
    }

    public function test_support_widget_uses_property_context_but_home_widget_stays_general(): void
    {
        $this->configurePhone(); $property=Property::factory()->create(['is_featured'=>true]);
        $this->get('/properties/'.$property->slug)->assertOk()->assertSee('propertyme-support-panel')->assertSee('Quick help')->assertSee('History')->assertSee('Continue on WhatsApp');
        $this->get('/')->assertOk()->assertSee('propertyme-support-panel');
        $component=view('components.contact-widget',['property'=>null])->render();
        $this->assertStringNotContainsString($property->title,$component);
        $this->assertStringContainsString('This is not a record of sent or delivered messages.',$component);
    }

    public function test_unconfigured_phone_shows_real_contact_options_without_a_fake_number(): void
    {
        $property=Property::factory()->create();
        $this->get('/properties')->assertOk()->assertSee('Contact Our Team')->assertDontSee('https://wa.me/',false);
        $this->get('/contact')->assertOk()->assertSee('Our direct contact details are being prepared.')->assertDontSee('https://wa.me/',false);
    }
}
