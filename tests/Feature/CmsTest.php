<?php
namespace Tests\Feature;
use App\Models\{User, Portfolio, PortfolioCategory, WebsiteSetting, Property};
use App\Services\{Settings, WhatsAppService};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\PropertymeTestCase;
class CmsTest extends PropertymeTestCase
{
    public function test_admin_screens_render(): void
    {
        $this->actingAs(User::factory()->create());
        foreach(['/admin','/admin/properties','/admin/locations','/admin/locations/create','/admin/property-types','/admin/portfolio','/admin/portfolio/create','/admin/services','/admin/services/'.\App\Models\Service::first()->id.'/edit','/admin/testimonials/create','/admin/pages','/admin/settings/company','/admin/settings/homepage','/admin/settings/seo','/admin/profile'] as $path) $this->get($path)->assertOk();
    }
    public function test_portfolio_crud_and_gallery(): void
    {
        Storage::fake('public'); $this->actingAs(User::factory()->create());
        $data=['title'=>'CMS Test Project','slug'=>'cms-test-project','category_id'=>PortfolioCategory::first()->id,'description'=>'Approved project description.','publication_status'=>'published','is_featured'=>1,'gallery'=>[UploadedFile::fake()->image('project.jpg',500,300)]];
        $this->post('/admin/portfolio',$data)->assertSessionHasNoErrors()->assertRedirect(); $project=Portfolio::where('slug','cms-test-project')->firstOrFail(); $this->assertCount(1,$project->images);
        unset($data['gallery']); $data['title']='Updated Project'; $this->put('/admin/portfolio/'.$project->id,$data)->assertSessionHasNoErrors(); $this->get('/portfolio/'.$project->slug)->assertOk()->assertSee('Updated Project');
        $this->delete('/admin/portfolio/'.$project->id)->assertRedirect('/admin/portfolio'); $this->assertDatabaseMissing('portfolios',['id'=>$project->id]);
    }
    public function test_reference_crud_and_restrictive_foreign_keys(): void
    {
        $this->actingAs(User::factory()->create()); $this->post('/admin/locations',['name'=>'Test Area','slug'=>'test-area','province'=>'Bali'])->assertSessionHasNoErrors()->assertRedirect(); $this->assertDatabaseHas('locations',['slug'=>'test-area']);
        $property=Property::factory()->create(); $this->delete('/admin/locations/'.$property->location_id)->assertSessionHas('error'); $this->assertDatabaseHas('locations',['id'=>$property->location_id]);
    }
    public function test_settings_normalize_whatsapp_and_update_public_contact(): void
    {
        $this->actingAs(User::factory()->create())->put('/admin/settings/company',['company__name'=>'PropertyME','company__whatsapp'=>'+62 812-3456-7890','company__email'=>'team@example.test','company__support_24_7'=>0])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('website_settings',['key'=>'company.whatsapp','value'=>'6281234567890']);
        $this->get('/contact')->assertOk()->assertSee('team@example.test')->assertSee('https://wa.me/6281234567890',false);
        $this->put('/admin/settings/homepage',['homepage__hero_headline'=>'Updated hero headline','homepage__hero_cta_url'=>'javascript:alert(1)'])->assertSessionHasErrors('homepage__hero_cta_url');
    }
    public function test_testimonials_only_appear_after_admin_publication(): void
    {
        $this->actingAs(User::factory()->create()); $data=['name'=>'Test Person','content'=>'An explicitly isolated test testimonial.','is_active'=>0];
        $this->post('/admin/testimonials',$data)->assertSessionHasNoErrors(); $this->get('/')->assertDontSee($data['content']); $id=\App\Models\Testimonial::first()->id;
        $this->put('/admin/testimonials/'.$id,array_merge($data,['is_active'=>1]))->assertSessionHasNoErrors(); $this->get('/')->assertSee($data['content']);
    }
    public function test_whatsapp_templates_are_encoded_and_sold_property_requests_similar_options(): void
    {
        WebsiteSetting::where('key','company.whatsapp')->update(['value'=>'6281234567890']); $settings=new Settings; $wa=new WhatsAppService($settings); $property=Property::factory()->create(['title'=>'Villa & Garden / Bali']);
        $url=$wa->url('general',$property); $this->assertStringStartsWith('https://wa.me/6281234567890?text=',$url); parse_str(parse_url($url,PHP_URL_QUERY),$query); $this->assertStringContainsString('Villa & Garden / Bali',$query['text']); $this->assertStringContainsString(route('properties.show',$property->slug),$query['text']);
        $property->status='sold'; $this->assertStringContainsString('similar available properties',rawurldecode($wa->url('general',$property)));
        foreach(['investment','list-sell-rent','buy','rent','property-management'] as $type) $this->assertStringStartsWith('https://wa.me/6281234567890?text=',$wa->url($type));
    }
    public function test_page_cms_controls_publication_and_search_indexing(): void
    {
        $this->actingAs(User::factory()->create());
        $data=['title'=>'An Editorial Page','slug'=>'editorial-test','content'=>'Plain text content <script>alert(1)</script>','publication_status'=>'published','is_indexable'=>0];
        $this->post('/admin/pages',$data)->assertSessionHasNoErrors()->assertRedirect(); $page=\App\Models\Page::where('slug','editorial-test')->firstOrFail();
        $this->get('/editorial-test')->assertOk()->assertSee('noindex,follow')->assertDontSee('<script>alert(1)</script>',false); $this->get('/sitemap.xml')->assertDontSee('editorial-test');
        $this->put('/admin/pages/'.$page->id,array_merge($data,['is_indexable'=>1]))->assertSessionHasNoErrors(); $this->get('/sitemap.xml')->assertSee('editorial-test');
        $this->put('/admin/pages/'.$page->id,array_merge($data,['publication_status'=>'draft']))->assertSessionHasNoErrors(); $this->get('/editorial-test')->assertNotFound();
    }
}
