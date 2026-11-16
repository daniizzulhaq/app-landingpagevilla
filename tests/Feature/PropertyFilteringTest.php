<?php
namespace Tests\Feature;
use App\Models\{Property, Location};
use Tests\PropertymeTestCase;
class PropertyFilteringTest extends PropertymeTestCase
{
    public function test_combined_filters_and_price_ordering_query_the_database(): void
    {
        $a=Property::factory()->create(['title'=>'Test Low Price','price'=>1000000,'bedrooms'=>3]);
        $b=Property::factory()->create(['title'=>'Test High Price','price'=>2000000,'bedrooms'=>4]);
        $wrongCurrency=Property::factory()->create(['title'=>'USD Property','currency'=>'USD','price'=>1500000]);
        $rent=Property::factory()->create(['title'=>'Rental Only','listing_type'=>'rent']);
        $this->get('/properties?listing_type=sale&min_price=900000&max_price=2100000&bedrooms=3&sort=price_asc')->assertOk()->assertSeeInOrder([$a->title,$b->title])->assertDontSee($wrongCurrency->title)->assertDontSee($rent->title);
        $this->get('/properties?sort=price_desc&currency=IDR')->assertOk()->assertSeeInOrder([$b->title,$a->title]);
        $this->get('/properties?q=Test+Low')->assertOk()->assertSee($a->title)->assertDontSee($b->title);
        $this->get('/properties?location='.$a->location_id.'&property_type='.$a->property_type_id)->assertOk()->assertSee($a->title);
    }
    public function test_hidden_prices_are_not_exposed_through_price_filters(): void
    {
        $hidden=Property::factory()->create(['show_price'=>false,'price'=>1000]);
        $this->get('/properties?min_price=500&max_price=1500')->assertOk()->assertDontSee($hidden->title);
        $this->get('/properties/'.$hidden->slug)->assertOk()->assertSee('Price on request');
    }
    public function test_pagination_preserves_filter_parameters(): void
    {
        Property::factory()->count(12)->create(['listing_type'=>'rent']);
        $response=$this->get('/properties?listing_type=rent&q=Test')->assertOk(); $response->assertSee('listing_type=rent',false)->assertSee('q=Test',false)->assertSee('page=2',false);
        $this->get('/properties?listing_type=rent&q=Test&page=2')->assertOk()->assertViewHas('properties',fn($p)=>$p->count()===3);
    }
    public function test_invalid_filters_are_rejected(): void { $this->getJson('/properties?sort=arbitrary&min_price=-1')->assertUnprocessable()->assertJsonValidationErrors(['sort','min_price']); }
}
