<?php
namespace Tests\Feature;
use App\Models\{Property, Portfolio, Testimonial};
use Illuminate\Support\Facades\Route;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\PropertymeTestCase;
class PublicWebsiteTest extends PropertymeTestCase
{
    public static function publicRoutes(): array { return array_map(fn($path)=>[$path], ['/', '/about-us', '/services', '/services/invest-build', '/services/list-sell-rent', '/services/buy-rent', '/services/property-management', '/properties', '/buy-rent', '/portfolio', '/contact', '/privacy-policy', '/admin/login']); }
    #[DataProvider('publicRoutes')]
    public function test_public_pages_render_without_login(string $path): void { $this->get($path)->assertOk()->assertSee('PropertyME'); }
    public function test_home_does_not_invent_listings_testimonials_or_support_claims(): void
    {
        $this->assertDatabaseCount('properties',0); $this->assertDatabaseCount('testimonials',0); $this->get('/')->assertOk()->assertSee('Your next chapter is taking shape.')->assertDontSee('Dedicated Support, 24/7')->assertDontSee('In their own words');
    }
    public function test_only_published_properties_are_visible_and_drafts_are_not_routable(): void
    {
        $visible=Property::factory()->create(['title'=>'Visible Published Property']); $draft=Property::factory()->draft()->create(['title'=>'Secret Draft Property']);
        $inactive=Property::factory()->create(['status'=>'inactive']); $future=Property::factory()->create(['published_at'=>now()->addDay()]);
        $this->get('/properties')->assertOk()->assertSee($visible->title)->assertDontSee($draft->title)->assertDontSee($inactive->title)->assertDontSee($future->title);
        $this->get('/properties/'.$draft->slug)->assertNotFound(); $this->get('/properties/'.$inactive->slug)->assertNotFound();
    }
    public function test_property_details_hide_exact_coordinates_and_handle_sold_status(): void
    {
        $property=Property::factory()->create(['status'=>'sold','latitude'=>'-8.6543210','longitude'=>'115.1234567','show_exact_location'=>false]);
        $this->get('/properties/'.$property->slug)->assertOk()->assertSee('Ask About Similar Properties')->assertDontSee('Inquire About This Property')->assertDontSee('115.1234567')->assertDontSee('maps.google.com');
        $property->update(['show_exact_location'=>true]); $this->get('/properties/'.$property->slug)->assertOk()->assertSee('maps.google.com');
    }
    public function test_portfolio_publication_and_details(): void
    {
        $project=Portfolio::factory()->create(); $draft=Portfolio::factory()->create(['publication_status'=>'draft']);
        $this->get('/portfolio')->assertOk()->assertSee($project->title)->assertDontSee($draft->title); $this->get('/portfolio/'.$project->slug)->assertOk(); $this->get('/portfolio/'.$draft->slug)->assertNotFound();
    }
    public function test_seo_and_sitemap_only_include_published_content(): void
    {
        $property=Property::factory()->create(); $draft=Property::factory()->draft()->create();
        $this->get('/properties/'.$property->slug)->assertOk()->assertSee('rel="canonical"',false)->assertSee('application/ld+json',false)->assertSee('og:title',false);
        $this->get('/sitemap.xml')->assertOk()->assertHeader('Content-Type','application/xml; charset=UTF-8')->assertSee($property->slug)->assertDontSee($draft->slug)->assertDontSee('/admin');
        $this->get('/robots.txt')->assertOk()->assertSee('Disallow: /admin'); $this->get('/properties?q=test')->assertOk()->assertSee('noindex,follow');
    }
    public function test_plain_text_cms_and_jsonld_escape_script_content(): void
    {
        $property=Property::factory()->create(['title'=>'<script>alert("xss")</script>','description'=>'<img src=x onerror=alert(1)>']);
        $response=$this->get('/properties/'.$property->slug)->assertOk(); $response->assertDontSee('<script>alert("xss")</script>',false)->assertSee('&lt;script&gt;',false)->assertDontSee('<img src=x onerror=alert(1)>',false);
    }
    public function test_custom_errors_and_security_headers(): void
    {
        $this->get('/not-a-real-page')->assertNotFound()->assertSee('A different path awaits.')->assertHeader('X-Content-Type-Options','nosniff');
        config(['app.debug'=>false]); Route::get('/__test-error',fn()=>throw new \RuntimeException('private diagnostic'));
        $this->get('/__test-error')->assertStatus(500)->assertSee('A brief interruption.')->assertDontSee('private diagnostic');
    }
}
