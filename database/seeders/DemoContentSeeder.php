<?php

namespace Database\Seeders;

use App\Models\{Amenity, Location, Page, Portfolio, PortfolioCategory, Property, PropertyType, Service, Testimonial, WebsiteSetting};
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\{DB, Storage};
use Ramsey\Uuid\Uuid;

/** Optional preview data; never called by DatabaseSeeder. */
class DemoContentSeeder extends Seeder
{
    private function media(string $asset, string $context): string
    {
        $source = public_path('images/demo/'.$asset.'.webp');
        if (! is_file($source)) { throw new \RuntimeException('Demo illustration is missing: '.$asset); }
        $path = 'images/'.Uuid::uuid5(Uuid::NAMESPACE_URL, 'propertyme.local/demo/'.$context).'.webp';
        if (! Storage::disk('public')->exists($path)) { Storage::disk('public')->put($path, file_get_contents($source)); }
        return $path;
    }

    public function run(): void
    {
        if (! \App\Support\PublicContent::demoEnabled()) {
            throw new \RuntimeException('Demo content requires local/testing or DEMO_CONTENT_ENABLED=true on a hosted preview.');
        }
        $this->call(DatabaseSeeder::class);

        DB::transaction(function () {
            // All specifications, locations and prices below are fictional examples.
            $listings = [
                ['001','Palm Courtyard Villa','Canggu','Villa','sale','4850000000',null,'Leasehold',2,3,220,165,'completed',2026,28,null,'courtyard','available'],
                ['002','Ocean Horizon Residence','Uluwatu','Villa','sale','12800000000',null,'Freehold',4,5,650,420,'completed',2025,null,null,'hero','available'],
                ['003','Jungle Pavilion Retreat','Ubud','Villa','sale','6250000000',null,'Leasehold',3,3,480,280,'off-plan',null,30,'Q4 2027','jungle','available'],
                ['004','The Ivory Loft','Seminyak','Apartment','sale','3450000000',null,'Leasehold',2,2,null,120,'completed',2026,25,null,'interior','available'],
                ['005','Pererenan Garden House','Pererenan','Villa','sale','7150000000',null,'Freehold',3,4,320,210,'under-construction',null,null,'Q2 2027','courtyard','available'],
                ['006','Sunrise Hillside Land','Ubud','Land','sale','3600000000',null,'Freehold',null,null,1800,null,null,null,null,null,'land','available'],
                ['007','Tropical Courtyard Stay','Canggu','Villa','rent','3200000','night','Leasehold',2,3,220,165,'completed',2026,28,null,'courtyard','available'],
                ['008','Ubud Forest Hideaway','Ubud','Villa','rent','4500000','night','Leasehold',3,3,480,280,'completed',2025,25,null,'jungle','available'],
                ['009','Cliffside Ocean Villa','Uluwatu','Villa','rent','8750000','night','Freehold',4,5,650,420,'completed',2026,null,null,'hero','available'],
                ['010','Seminyak Living Villa','Seminyak','Villa','rent','38000000','month','Leasehold',2,2,250,180,'completed',2025,25,null,'interior','available'],
                ['011','Coastal Garden Plot','Jimbaran','Land','sale','4250000000',null,'Freehold',null,null,1250,null,null,null,null,null,'land','reserved'],
                ['012','Palm House Collection','Umalas','Villa','sale','5600000000',null,'Leasehold',3,3,280,190,'completed',2025,30,null,'courtyard','sold'],
            ];

            foreach ($listings as [$code,$title,$area,$type,$listing,$price,$unit,$ownership,$beds,$baths,$land,$building,$development,$year,$lease,$handover,$asset,$status]) {
                $slug = 'demo-'.\Illuminate\Support\Str::slug($title);
                $property = Property::withTrashed()->firstOrNew(['property_code'=>'DEMO-PM-'.$code]);
                if ($property->exists) { continue; } // Preserve all subsequent editor changes and soft deletions.
                $property->fill([
                    'title'=>$title,'slug'=>$slug,'property_type_id'=>PropertyType::where('name',$type)->sole()->id,
                    'location_id'=>Location::where('name',$area)->sole()->id,'listing_type'=>$listing,'price'=>$price,
                    'currency'=>'IDR','price_unit'=>$unit,'show_price'=>true,'ownership_type'=>$ownership,
                    'bedrooms'=>$beds,'bathrooms'=>$baths,'land_area'=>$land,'building_area'=>$building,
                    'floors'=>$type==='Land' ? null : 2,'max_guests'=>$listing==='rent' ? $beds*2 : null,
                    'furnishing'=>$type==='Land' ? null : 'Illustrative fully furnished layout',
                    'development_status'=>$development,'year_built'=>$year,'lease_years'=>$lease,'handover'=>$handover,
                    'status'=>$status,'publication_status'=>'published','published_at'=>now()->subMinutes((int)$code),
                    'is_featured'=>(int)$code<=10,'is_demo'=>true,'show_exact_location'=>false,
                    'short_description'=>'Demo listing with AI-generated architectural illustrations. All prices and specifications are fictional.',
                    'description'=>"DEMO PROPERTY — for website preview only. This is not a real property offered by PropertyME. Images are AI-generated illustrations; location, price, availability, ownership and specifications are examples.\n\n".
                        ($type==='Land' ? 'An illustrative landscape concept with open green space, tropical planting and a rural setting. Use this example to explore land listing specifications and property inquiries.' : 'A contemporary tropical design concept with natural materials, open living spaces and a considered connection between indoor and outdoor areas. Explore the gallery and specifications to preview the property browsing experience.').
                        "\n\nFor a genuine property inquiry, replace this example with verified listing details and approved photography through the admin CMS. No investment return or rental availability is represented by this demonstration.",
                    'seo_title'=>$title.' — Demo Listing | PropertyME','meta_description'=>'Illustrative demo property. Not a real listing. All images, specifications and prices are examples.',
                ])->save();
                $gallery = $type==='Land' ? ['land'] : array_values(array_unique([$asset,'interior',$asset==='jungle' ? 'courtyard' : 'jungle']));
                foreach ($gallery as $index=>$image) {
                    $property->images()->create(['image_path'=>$this->media($image,'property/'.$code.'/gallery/'.$index),'alt_text'=>'AI-generated illustration for '.$title.' — demo only','is_primary'=>$index===0,'sort_order'=>$index]);
                }
                $amenities = $type==='Land' ? ['Parking','Garden'] : ['Swimming pool','WiFi','Parking','Air conditioning','Kitchen','Garden','Security'];
                $property->amenities()->sync(Amenity::whereIn('name',$amenities)->pluck('id'));
            }

            foreach ([
                ['palm-courtyard-study','Palm Courtyard — Design Study','Villa Development','Canggu','courtyard'],
                ['ocean-residence-concept','Ocean Residence — Architectural Concept','Architecture & Interior','Uluwatu','hero'],
                ['forest-pavilion-study','Forest Pavilion — Spatial Study','Architecture & Interior','Ubud','jungle'],
                ['ivory-interior-concept','The Ivory Interior — Material Study','Architecture & Interior','Seminyak','interior'],
                ['garden-villa-planning','Garden Villa — Planning Example','Ongoing Projects','Pererenan','courtyard'],
                ['hospitality-operations-example','Villa Hospitality — Workflow Example','Managed Properties','Bali','jungle'],
            ] as $i=>[$slug,$title,$category,$area,$asset]) {
                if (Portfolio::where('slug','demo-'.$slug)->exists()) { continue; }
                $project=Portfolio::create(['title'=>$title,'slug'=>'demo-'.$slug,'category_id'=>PortfolioCategory::where('name',$category)->sole()->id,'location'=>$area,'description'=>"DEMO PORTFOLIO — architectural and operational concept examples only. This is not a completed or commissioned PropertyME project. All images are AI-generated illustrations.\n\nThis study explores natural materials, tropical landscaping and a considered indoor–outdoor relationship. The editorial layout demonstrates how approved project information, milestones and photography can be presented.\n\nReplace this example with a verified company project through the admin CMS.",'project_year'=>2026,'project_status'=>'Illustrative concept / demo','featured_image'=>$this->media($asset,'portfolio/'.$slug.'/cover'),'is_featured'=>$i<3,'publication_status'=>'published','is_demo'=>true,'seo_title'=>$title.' — Demo | PropertyME','meta_description'=>'Illustrative concept portfolio. Not a verified company project.']);
                foreach ([$asset,'interior'] as $index=>$image) { $project->images()->create(['image_path'=>$this->media($image,'portfolio/'.$slug.'/gallery/'.$index),'alt_text'=>'AI-generated '.$title.' concept — demo only','sort_order'=>$index]); }
            }

            foreach (['Property buyer','Villa owner','Investment consultation'] as $role) {
                Testimonial::firstOrCreate(['name'=>'Demo '.$role,'is_demo'=>true],['role'=>'Layout example — not a real client','content'=>'This is placeholder text for the testimonial layout. Replace it with a genuine, approved client review before publication.','rating'=>null,'source'=>'Demonstration content, not a customer review','is_active'=>true]);
            }

            foreach (['hero_image'=>'hero','about_image'=>'interior'] as $key=>$asset) {
                $setting=WebsiteSetting::firstOrNew(['key'=>'homepage.'.$key]);
                if (! $setting->value) { $setting->fill(['value'=>$this->media($asset,'homepage/'.$key),'type'=>'image'])->save(); }
            }
            foreach (Service::all() as $service) {
                if ($service->featured_image) { continue; }
                $asset=match($service->slug){'invest-build'=>'land','list-sell-rent'=>'interior','property-management'=>'jungle',default=>'courtyard'};
                $service->update(['featured_image'=>$this->media($asset,'service/'.$service->id)]);
            }
            $about=Page::where('slug','about-us')->first();
            if ($about && ! $about->featured_image) { $about->update(['featured_image'=>$this->media('interior','page/about')]); }
            WebsiteSetting::updateOrCreate(['key'=>'demo.content_enabled'],['value'=>'1','type'=>'boolean']);
            $heading=WebsiteSetting::where('key','homepage.featured_title')->first();
            if ($heading?->value==='Find Your Place in Bali.') { $heading->update(['value'=>'Featured properties']); }
            $this->call(TranslationSeeder::class);
        });
    }
}
