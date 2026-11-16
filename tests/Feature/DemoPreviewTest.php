<?php

namespace Tests\Feature;

use App\Models\{Portfolio, Property, Testimonial, User};
use Database\Seeders\DemoContentSeeder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\{Hash, Storage};
use Tests\PropertymeTestCase;

class DemoPreviewTest extends PropertymeTestCase
{
    public function test_explicit_demo_seed_is_complete_labeled_and_idempotent(): void
    {
        Storage::fake('public'); $this->seed(DemoContentSeeder::class);
        $this->assertDatabaseCount('properties',12);
        $this->assertDatabaseCount('portfolios',6);
        $this->assertDatabaseCount('testimonials',3);
        $this->assertSame(12,Property::where('is_demo',true)->count());
        foreach(Property::with('images')->get() as $property){
            $this->assertNotEmpty($property->images);
            foreach($property->images as $image) Storage::disk('public')->assertExists($image->image_path);
        }
        $property=Property::first(); $property->update(['title'=>'An editor changed this title']);
        $this->seed(DemoContentSeeder::class);
        $this->assertDatabaseCount('properties',12); $this->assertDatabaseCount('portfolios',6); $this->assertDatabaseCount('testimonials',3);
        $this->assertSame('An editor changed this title',$property->fresh()->title);
        $this->get('/')->assertOk()->assertDontSee('Demo listing')->assertDontSee('Testimonial layout preview')->assertDontSee('Local demo')->assertDontSee('Architectural inspiration · illustrative imagery')->assertSee('Search Properties');
        $this->get('/properties')->assertOk()->assertDontSee('Demo listing')->assertSee('12 properties found');
        $this->get('/properties/'.$property->slug)->assertOk()->assertDontSee('Demo property.')->assertDontSee('DEMO PROPERTY')->assertSee('noindex,follow')->assertDontSee('"@type":"House"',false);
        $this->get('/portfolio/'.Portfolio::first()->slug)->assertOk()->assertDontSee('Demo portfolio.')->assertDontSee('DEMO PORTFOLIO');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/properties/demo-')->assertDontSee('/portfolio/demo-');
    }

    public function test_featured_tabs_and_catalogue_ownership_filters_use_database_results(): void
    {
        Storage::fake('public'); $this->seed(DemoContentSeeder::class);
        $this->get('/?featured_listing=rent')->assertOk()->assertViewHas('properties',fn($items)=>$items->isNotEmpty() && $items->every(fn($p)=>$p->listing_type==='rent'));
        $this->get('/?featured_collection=leasehold')->assertOk()->assertViewHas('properties',fn($items)=>$items->isNotEmpty() && $items->every(fn($p)=>$p->ownership_type==='Leasehold'));
        $this->get('/?featured_collection=land')->assertOk()->assertViewHas('properties',fn($items)=>$items->isNotEmpty() && $items->every(fn($p)=>$p->propertyType->slug==='land'));
        $this->get('/properties?ownership_type=Freehold')->assertOk()->assertViewHas('properties',fn($items)=>$items->isNotEmpty() && $items->every(fn($p)=>$p->ownership_type==='Freehold'));
        $this->get('/properties?development_status=off-plan')->assertOk()->assertSee('Jungle Pavilion Retreat')->assertDontSee('Palm Courtyard Villa');
        $this->get('/?featured_listing=invalid')->assertSessionHasErrors('featured_listing');
    }

    public function test_local_admin_alias_authenticates_with_a_hashed_password(): void
    {
        $user=new User; $user->forceFill(['name'=>'Local Admin','email'=>'admin@propertyme.test','password'=>Hash::make('password'),'is_demo'=>true])->save();
        $this->get('/admin/login')->assertOk()->assertSee('Email or username');
        $this->post('/admin/login',['email'=>'admin','password'=>'password'])->assertRedirect('/admin'); $this->assertAuthenticatedAs($user);
        $this->assertNotSame('password',$user->password);
    }

    public function test_demo_account_is_blocked_in_production_even_using_its_full_email(): void
    {
        $user=new User; $user->forceFill(['name'=>'Local Admin','email'=>'admin@propertyme.test','password'=>Hash::make('password'),'is_demo'=>true])->save();
        $this->app['env']='production'; $this->withoutMiddleware([VerifyCsrfToken::class,\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $this->post('/admin/login',['email'=>'admin@propertyme.test','password'=>'password'])->assertSessionHasErrors('email'); $this->assertGuest();
        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->assertFalse($user->can('create',Property::class));
    }

    public function test_demo_properties_portfolio_and_reviews_are_hidden_in_production(): void
    {
        config(['propertyme.demo_content_enabled'=>false]);
        Storage::fake('public'); $this->seed(DemoContentSeeder::class); $property=Property::first(); $project=Portfolio::first();
        $this->app['env']='production';
        $this->assertSame(0,Property::published()->count()); $this->assertSame(0,Portfolio::published()->count());
        $this->get('/properties/'.$property->slug)->assertNotFound(); $this->get('/portfolio/'.$project->slug)->assertNotFound();
        $this->get('/')->assertOk()->assertDontSee('Demo listing')->assertDontSee('Demo text')->assertDontSee('Local demo');
    }

    public function test_demo_seeder_refuses_production(): void
    {
        config(['propertyme.demo_content_enabled'=>false]);
        $this->app['env']='production'; $this->expectException(\RuntimeException::class); $this->expectExceptionMessage('local/testing');
        (new DemoContentSeeder)->run();
    }

    public function test_hosted_preview_can_install_and_display_demo_content_without_overwriting_edits(): void
    {
        Storage::fake('public');
        $this->app['env']='production';
        config(['propertyme.demo_content_enabled'=>true]);
        $this->artisan('db:seed',['--class'=>DemoContentSeeder::class,'--force'=>true])->assertExitCode(0);
        $property=Property::with('images')->first(); $project=Portfolio::first();
        $property->update(['title'=>'Edited hosted villa']);
        $this->artisan('db:seed',['--class'=>DemoContentSeeder::class,'--force'=>true])->assertExitCode(0);
        $this->assertDatabaseCount('properties',12);
        $this->assertSame(12,Property::published()->count());
        $this->assertSame(6,Portfolio::published()->count());
        $this->assertSame('Edited hosted villa',$property->fresh()->title);
        foreach ($property->images as $image) Storage::disk('public')->assertExists($image->image_path);
        $this->get('/properties')->assertOk()->assertSee('Edited hosted villa')->assertDontSee('Demo content.')->assertSee('noindex,follow');
        $this->get('/properties/'.$property->slug)->assertOk()->assertDontSee('AI-generated')->assertDontSee('Demo content.')->assertDontSee('"@type":"House"',false);
        $this->get('/portfolio/'.$project->slug)->assertOk();
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/properties/demo-')->assertDontSee('/portfolio/demo-');

        $user=new User; $user->forceFill(['name'=>'Local Admin','email'=>'admin@propertyme.test','password'=>Hash::make('password'),'is_demo'=>true])->save();
        $this->withoutMiddleware([VerifyCsrfToken::class,\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
        $this->post('/admin/login',['email'=>$user->email,'password'=>'password'])->assertSessionHasErrors('email');
        $this->assertGuest();

        config(['propertyme.demo_content_enabled'=>false]);
        $this->assertSame(0,Property::published()->count());
        $this->assertSame(0,Portfolio::published()->count());
        $this->assertDatabaseCount('properties',12);
        $this->get('/properties/'.$property->slug)->assertNotFound();
    }
}
