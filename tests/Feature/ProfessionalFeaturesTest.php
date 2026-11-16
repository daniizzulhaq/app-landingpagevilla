<?php

namespace Tests\Feature;

use App\Models\{Page, Portfolio, Property, TeamMember, User, WebsiteSetting};
use App\Services\WhatsAppService;
use Tests\PropertymeTestCase;

class ProfessionalFeaturesTest extends PropertymeTestCase
{
    public function test_public_language_switch_translates_content_and_preserves_admin_language(): void
    {
        $this->get('/?lang=id')->assertOk()->assertHeader('Content-Language','id')
            ->assertSee('<html lang="id">',false)->assertSee('Mitra Tepercaya')
            ->assertSee('hreflang="en"',false)->assertSee('hreflang="id"',false)->assertCookie('propertyme_locale','id');
        $this->withCookie('propertyme_locale','id')->get('/contact')->assertOk()->assertHeader('Content-Language','id');
        $this->get('/admin/login?lang=id')->assertOk()->assertSee('Email or username');
        $this->get('/?lang=unsupported')->assertStatus(400);
        $this->get('/?lang[]=id')->assertStatus(400);
    }

    public function test_translation_uses_english_fallback_and_retains_property_identity(): void
    {
        $property=Property::factory()->create(['title'=>'An approved villa','description'=>'Approved English description.']);
        $property->saveTranslation(['title'=>'Villa yang disetujui','description'=>'Deskripsi dalam bahasa Indonesia.','slug'=>'must-not-change']);
        $originalSlug=$property->slug;
        $this->get('/properties/'.$originalSlug.'?lang=id')->assertOk()->assertSee('Villa yang disetujui')->assertSee('Deskripsi dalam bahasa Indonesia.');
        $localized=Property::findOrFail($property->id);
        $this->assertTrue($localized->relationLoaded('contentTranslations'));
        $this->assertSame($originalSlug,$localized->slug);
        $this->assertSame($property->getRawOriginal('short_description'),$localized->short_description);
        $message=app(WhatsAppService::class)->message('general',$localized);
        $this->assertStringContainsString('Villa yang disetujui',$message);
        $this->assertStringContainsString('lang=id',$message);
        $this->get('/properties?lang=id&q=Villa%20yang%20disetujui')->assertOk()->assertViewHas('properties',fn($items)=>$items->contains('id',$property->id));
        $this->get('/properties/'.$originalSlug.'?lang=en')->assertOk()->assertSee('An approved villa')->assertDontSee('Villa yang disetujui');
    }

    public function test_cms_saves_translations_separately_and_escapes_their_content(): void
    {
        $this->actingAs(User::factory()->create());
        $page=Page::where('slug','about-us')->firstOrFail();
        $data=['title'=>$page->title,'slug'=>$page->slug,'content'=>'Approved English story.','publication_status'=>'published','is_indexable'=>1,
            'translations'=>['title'=>'Tentang perusahaan','content'=>'Cerita perusahaan <script>alert(1)</script>']];
        $this->put('/admin/pages/'.$page->id,$data)->assertSessionHasNoErrors();
        $this->assertSame('Approved English story.',$page->fresh()->content);
        $this->get('/about-us?lang=id')->assertOk()->assertSee('Cerita perusahaan')->assertDontSee('<script>alert(1)</script>',false);
        $this->put('/admin/pages/'.$page->id,array_replace($data,['translations'=>['publication_status'=>'draft']]))->assertSessionHasErrors('translations');
    }

    public function test_translated_settings_do_not_replace_company_contacts_or_english_content(): void
    {
        $english=WebsiteSetting::where('key','company.description')->value('value');
        $phone=WebsiteSetting::where('key','company.whatsapp')->value('value');
        $this->actingAs(User::factory()->create())->put('/admin/settings/company-id',['company__description__id'=>'Deskripsi resmi perusahaan.'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('website_settings',['key'=>'company.description.id','value'=>'Deskripsi resmi perusahaan.']);
        $this->assertSame($english,WebsiteSetting::where('key','company.description')->value('value'));
        $this->assertSame($phone,WebsiteSetting::where('key','company.whatsapp')->value('value'));
        foreach(['/admin/team','/admin/team/create','/admin/settings/company-id','/admin/settings/homepage-id','/admin/settings/seo-id'] as $path) $this->get($path)->assertOk();
    }

    public function test_selection_api_preserves_order_and_only_exposes_published_public_fields(): void
    {
        $first=Property::factory()->create(['title'=>'First approved property','price'=>'87654321.00','show_price'=>false,'latitude'=>'-8.4567890','longitude'=>'115.9876543']);
        $second=Property::factory()->create(['title'=>'Second approved property','status'=>'sold']);
        $draft=Property::factory()->draft()->create();
        $inactive=Property::factory()->create(['status'=>'inactive']);
        $deleted=Property::factory()->create(); $deleted->delete();
        $ids=implode(',',[$second->id,$draft->id,$first->id,$inactive->id,$deleted->id]);
        $response=$this->getJson('/property-selection?ids='.$ids)->assertOk()->assertJsonCount(2,'properties')->assertJsonPath('unavailable',3)
            ->assertJsonPath('properties.0.id',$second->id)->assertJsonPath('properties.1.id',$first->id)
            ->assertJsonPath('properties.1.price','Price on request')->assertJsonPath('properties.0.whatsappLabel','Ask for Similar Properties');
        $response->assertDontSee('87654321')->assertDontSee('115.9876543')->assertDontSee($draft->title);
        $this->assertStringContainsString('no-store',$response->headers->get('Cache-Control'));
    }

    public function test_selection_api_validates_limits_and_translates_the_selection(): void
    {
        $property=Property::factory()->create(); $property->saveTranslation(['title'=>'Villa pilihan saya']);
        $this->getJson('/property-selection?ids='.$property->id.'&lang=id')->assertOk()->assertJsonPath('properties.0.title','Villa pilihan saya');
        $this->getJson('/property-selection?ids=1,2,3,4&mode=compare')->assertStatus(422);
        $this->getJson('/property-selection?ids=1,not-an-id')->assertStatus(422);
        $this->getJson('/property-selection?ids=1&mode=invalid')->assertStatus(422);
        $this->getJson('/property-selection')->assertOk()->assertJsonCount(0,'properties');
        $this->get('/favorites')->assertOk()->assertSee('noindex,follow');
        $this->get('/compare')->assertOk()->assertSee('noindex,follow');
        $this->get('/sitemap.xml')->assertOk()->assertDontSee('/favorites')->assertDontSee('/compare')->assertSee('/company-profile')->assertSee('?lang=id');
    }

    public function test_team_requires_approval_and_company_profile_is_printable(): void
    {
        $this->actingAs(User::factory()->create());
        $data=['name'=>'An approved team member','role'=>'Property consultant','bio'=>'Approved professional biography.','sort_order'=>1,'is_active'=>0,'translations'=>['role'=>'Konsultan properti']];
        $this->post('/admin/team',$data)->assertSessionHasNoErrors();
        $member=TeamMember::firstOrFail();
        $this->get('/about-us')->assertOk()->assertDontSee($member->name);
        $this->put('/admin/team/'.$member->id,array_replace($data,['is_active'=>1]))->assertSessionHasNoErrors();
        $this->get('/about-us?lang=id')->assertOk()->assertSee($member->name)->assertSee('Konsultan properti');
        $project=Portfolio::factory()->create(['is_demo'=>false]);
        $sample=Portfolio::factory()->create(['is_demo'=>true]);
        $this->get('/company-profile?lang=en')->assertOk()->assertSee('Print / Save PDF')->assertSee('window.print()',false)->assertSee($project->title)->assertDontSee($sample->title)->assertSee($member->name);
        $this->delete('/admin/team/'.$member->id)->assertRedirect('/admin/team');
        $this->assertDatabaseMissing('team_members',['id'=>$member->id]);
        $this->assertDatabaseMissing('content_translations',['translatable_type'=>TeamMember::class,'translatable_id'=>$member->id]);
    }

    public function test_desktop_navigation_stays_in_the_header_and_public_labels_are_clean(): void
    {
        $this->get('/')->assertOk()->assertSee('data-desktop-navigation',false)->assertSee('lg:flex',false)
            ->assertSee('Investment')->assertSee('Contact')->assertDontSee('Local demo · Illustrative listings &amp; AI images',false)
            ->assertDontSee('Architectural inspiration · illustrative imagery');
    }
}
