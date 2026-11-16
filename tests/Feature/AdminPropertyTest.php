<?php
namespace Tests\Feature;
use App\Models\{User, Property, PropertyType, Location, Amenity};
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\PropertymeTestCase;
class AdminPropertyTest extends PropertymeTestCase
{
    private function payload(array $extra=[]): array
    {
        return array_merge(['title'=>'Automated Test Villa','slug'=>'automated-test-villa','property_code'=>'AUTO-TEST-001','property_type_id'=>PropertyType::where('slug','villa')->value('id'),'location_id'=>Location::where('slug','canggu')->value('id'),'listing_type'=>'sale','description'=>'Approved test description.','price'=>'1250000000.00','currency'=>'IDR','show_price'=>1,'show_exact_location'=>0,'status'=>'available','publication_status'=>'published','is_featured'=>1],$extra);
    }
    public function test_create_edit_unpublish_republish_and_soft_delete_property(): void
    {
        $this->actingAs(User::factory()->create());
        $this->get('/admin/properties/create')->assertOk()->assertSee('Basic information');
        $this->post('/admin/properties',$this->payload())->assertSessionHasNoErrors()->assertRedirect(); $property=Property::where('property_code','AUTO-TEST-001')->firstOrFail();
        $this->assertNotNull($property->published_at); $this->get('/properties/'.$property->slug)->assertOk(); $this->get('/admin/properties/'.$property->id.'/edit')->assertOk();
        $this->put('/admin/properties/'.$property->id,$this->payload(['title'=>'Updated Villa','publication_status'=>'draft']))->assertSessionHasNoErrors(); $this->get('/properties/'.$property->slug)->assertNotFound();
        $this->put('/admin/properties/'.$property->id,$this->payload(['slug'=>'new-test-slug']))->assertSessionHasNoErrors(); $this->get('/properties/automated-test-villa')->assertRedirect('/properties/new-test-slug')->assertStatus(301); $this->get('/properties/new-test-slug')->assertOk();
        $this->delete('/admin/properties/'.$property->id)->assertRedirect('/admin/properties'); $this->assertSoftDeleted('properties',['id'=>$property->id]); $this->get('/properties/new-test-slug')->assertNotFound();
        $this->assertDatabaseHas('audit_logs',['action'=>'created','subject_type'=>'Property','subject_id'=>$property->id]);
    }
    public function test_gallery_upload_metadata_order_primary_and_removal(): void
    {
        Storage::fake('public'); $this->actingAs(User::factory()->create()); $amenity=Amenity::first();
        $this->post('/admin/properties',$this->payload(['gallery'=>[UploadedFile::fake()->image('cover.jpg',400,300),UploadedFile::fake()->image('second.png',400,300)],'amenities'=>[$amenity->id]]))->assertSessionHasNoErrors();
        $property=Property::where('property_code','AUTO-TEST-001')->firstOrFail(); $images=$property->images; $this->assertCount(2,$images); $this->assertTrue($images[0]->is_primary); Storage::disk('public')->assertExists($images[0]->image_path); $this->assertStringEndsWith('.webp',$images[0]->image_path); $this->assertTrue($property->amenities->contains($amenity));
        $this->get('/admin/properties/'.$property->id.'/edit')->assertOk()->assertSee('Use as cover image');
        $this->get('/properties/'.$property->slug)->assertOk()->assertSee('Close gallery')->assertSee('View 2');
        $this->get('/media/'.$images[0]->image_path)->assertOk()->assertHeader('Content-Type','image/webp');
        $this->put('/admin/properties/'.$property->id,$this->payload(['primary_image'=>$images[1]->id,'images'=>[$images[1]->id=>['alt_text'=>'Pool courtyard','sort_order'=>0]],'remove_images'=>[$images[0]->id]]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('property_images',['id'=>$images[0]->id]); Storage::disk('public')->assertMissing($images[0]->image_path); $this->assertDatabaseHas('property_images',['id'=>$images[1]->id,'is_primary'=>true,'alt_text'=>'Pool courtyard']);
    }
    public function test_executable_upload_and_invalid_values_are_rejected(): void
    {
        $this->actingAs(User::factory()->create())->post('/admin/properties',$this->payload(['gallery'=>[UploadedFile::fake()->create('attack.php',1,'application/x-php')],'price'=>-1,'latitude'=>100]))->assertSessionHasErrors(['gallery.0','price','latitude']); $this->assertDatabaseCount('properties',0);
    }
    public function test_duplicate_slug_and_cross_property_gallery_updates_are_prevented(): void
    {
        Storage::fake('public'); $this->actingAs(User::factory()->create()); $other=Property::factory()->create(); $image=$other->images()->create(['image_path'=>'images/test.webp','alt_text'=>'Private original']);
        $this->post('/admin/properties',$this->payload(['slug'=>$other->slug]))->assertSessionHasErrors('slug');
        $this->post('/admin/properties',$this->payload(['images'=>[$image->id=>['alt_text'=>'Changed','sort_order'=>1]],'remove_images'=>[$image->id]]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('property_images',['id'=>$image->id,'alt_text'=>'Private original']);
    }
    public function test_land_and_sale_listings_clear_irrelevant_specifications(): void
    {
        $this->actingAs(User::factory()->create())->post('/admin/properties',$this->payload(['property_type_id'=>PropertyType::where('slug','land')->value('id'),'bedrooms'=>4,'max_guests'=>8]))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('properties',['property_code'=>'AUTO-TEST-001','bedrooms'=>null,'max_guests'=>null]);
    }
}
