<?php
namespace Database\Seeders;
use App\Models\{PropertyType, Location, Amenity, PortfolioCategory, Service, Page, WebsiteSetting};
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Villa', 'Land', 'Apartment', 'Commercial'] as $name) { PropertyType::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]); }
        foreach (['Canggu', 'Seminyak', 'Ubud', 'Uluwatu', 'Sanur', 'Jimbaran', 'Pererenan', 'Umalas'] as $name) { Location::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name, 'province' => 'Bali']); }
        foreach (['Swimming pool', 'WiFi', 'Parking', 'Air conditioning', 'Kitchen', 'Garden', 'Security'] as $name) { Amenity::firstOrCreate(['name' => $name]); }
        foreach (['Villa Development', 'Completed Projects', 'Ongoing Projects', 'Managed Properties', 'Architecture & Interior'] as $name) { PortfolioCategory::firstOrCreate(['slug' => Str::slug($name)], ['name' => $name]); }
        $services = [
            ['Invest & Build', 'invest-build', 'From finding the perfect land to designing and building your dream investment property.', 'Start Your Investment',
                "A considered approach. From the ground up.\n\nEvery property journey begins with understanding your goals. We help you explore land, shape your architectural brief, and coordinate planning, construction and handover.\n\nOur scope can include land sourcing, architectural design, project supervision and operational planning. Legal due diligence and ownership advice should be obtained from appropriately qualified independent professionals.\n\nInvestment returns depend on market conditions, costs and individual circumstances. We do not guarantee returns or appreciation.",
                "Consultation\nLand Selection\nPlanning\nDesign\nConstruction\nHandover\nManagement",
                "Where do we start? | Start with a conversation about your goals, preferred areas, timeline and budget.\nCan you help after construction? | We can discuss ongoing villa management and operational services as part of your project scope.\nAre investment returns guaranteed? | No. Property investment involves risk, and outcomes vary. Obtain independent financial and legal advice."],
            ['List & Sell / Rent', 'list-sell-rent', 'List your property with our team and connect with qualified buyers or renters.', 'List Your Property',
                "Your property, thoughtfully presented.\n\nWe work with owners to prepare a clear property profile, coordinate photography, and present the details that matter to prospective buyers and renters.\n\nOur listing services can include property marketing, inquiry handling, viewing coordination and assistance through the sales or rental process.\n\nSend us your property details on WhatsApp. Our team will discuss the scope, required information and your approval before publishing a listing.",
                "Share Your Property\nAgree the Listing Scope\nPhotography & Presentation\nMarketing\nInquiries & Viewings\nSales or Rental Assistance",
                "Can I upload my property directly? | Listings are prepared and published by our team after receiving the information and the owner's approval.\nHow do viewings work? | Our team coordinates suitable viewing arrangements with owners and interested buyers or renters."],
            ['Buy & Rent', 'buy-rent', 'Discover exclusive villas and real estate opportunities across Bali.', 'Explore Properties',
                "Find a place that fits your vision.\n\nExplore published villas, land and other properties across Bali. Filter by location, property type and budget, then speak directly with our team about the details.\n\nFor rentals, contact us to confirm dates, rates and current availability. All viewings, negotiations and arrangements are coordinated personally through WhatsApp.",
                "Explore Properties\nDiscuss Your Preferences\nConfirm Availability\nArrange a Viewing\nAgree Next Steps",
                "Can I book a villa online? | Contact our team on WhatsApp to confirm dates, prices and availability.\nCan you help me find a property? | Share your preferred area, budget and requirements, and our team can discuss suitable options."],
            ['Property Management', 'property-management', 'Professional villa management with dedicated hospitality, maintenance and security support.', 'Manage My Villa',
                "Exceptional Property Management. Peace of Mind for Every Owner.\n\nA well-managed villa is about attention to the everyday details. We coordinate guest communication, check-in and check-out, staff and housekeeping to help create a considered hospitality experience.\n\nOur services cover pool and garden maintenance, property inspections, preventive maintenance and private security coordination, with financial and operational reporting.\n\nMarketing and listing coordination, operational oversight and a clearly agreed scope help owners stay informed. Contact our team to discuss your villa's specific needs.",
                "Property Assessment\nTailored Management Scope\nTeam & Operations Setup\nGuest Hospitality\nMaintenance & Inspection\nOwner Reporting",
                "What is included? | The scope is agreed for each property and may include hospitality, housekeeping, maintenance, security coordination and reporting.\nWill I receive reports? | Financial and operational reporting can be included in your agreed management scope.\nIs support available 24/7? | Our team will confirm the actual support hours and escalation arrangements for your villa."],
        ];
        foreach ($services as $i => [$title, $slug, $short, $cta, $content, $steps, $faqs]) {
            Service::firstOrCreate(['slug' => $slug], ['title' => $title, 'short_description' => $short, 'cta_label' => $cta, 'content' => $content, 'workflow_steps' => $steps, 'faqs' => $faqs, 'sort_order' => $i, 'is_active' => true]);
        }
        Page::firstOrCreate(['slug' => 'about-us'], [
            'title' => 'More Than Property. Your Complete Investment Partner.',
            'content' => "PropertyME brings together real estate, investment planning, villa development and property management in Bali.\n\nOur one-stop approach connects the stages of your property journey: from understanding your objectives and sourcing land to design, construction, buying, selling and ongoing villa operations.\n\nWe work with investors, property owners, buyers and renters who value clear communication and a considered approach. Each engagement starts with a conversation about your needs and a clearly defined scope.\n\nA home in Bali. A vision for the future. A team to help you take the next step.",
            'seo_title' => 'About PropertyME | Your Bali Property Partner', 'meta_description' => 'Discover PropertyME, your partner for property investment, villa development, real estate and property management in Bali.', 'publication_status' => 'published',
        ]);
        Page::firstOrCreate(['slug' => 'privacy-policy'], [
            'title' => 'Privacy Policy', 'publication_status' => 'published', 'is_indexable' => false,
            'content' => "Editorial draft — company review required before production publication.\n\nThis website provides property information and links to contact PropertyME. Public visitors do not need an account. Consultation and property inquiries take place through WhatsApp, whose own privacy terms apply.\n\nInformation you voluntarily share may be used to respond to your request and coordinate property services. Contact the company using the details on the Contact page for privacy requests.\n\nEssential session cookies support administrator authentication and website security. No analytics or advertising tracker is installed by default. Server logs may contain technical request information needed for security and diagnostics. Illustrative photographs and fonts may load from Unsplash and Google Fonts, which receive connection information.\n\nBefore launch, the company must complete its legal identity, controller contact, lawful processing basis, retention periods, data sharing, international transfers and applicable data subject rights. This draft is not a statement of a verified company policy.",
        ]);
        $settings = [
            'company.name' => 'PropertyME', 'company.tagline' => 'Your Bali. Your Vision. Our Expertise.',
            'company.description' => 'Your complete property partner in Bali. From investment and development to real estate and villa management.',
            'company.whatsapp' => null, 'company.email' => null, 'company.address' => null, 'company.instagram' => null, 'company.office_hours' => null, 'company.support_24_7' => '0',
            'homepage.hero_eyebrow' => 'BALI, INDONESIA · REAL ESTATE & BEYOND',
            'homepage.hero_headline' => 'Your Trusted Partner in Bali Property Investment',
            'homepage.hero_subheadline' => 'From land sourcing and villa development to real estate and property management — we turn your vision into lasting value.',
            'homepage.hero_cta_label' => 'Explore Our Services', 'homepage.hero_cta_url' => '/services',
            'homepage.about_title' => 'More Than Property. Your Complete Investment Partner.',
            'homepage.about_body' => 'A remarkable property starts with a clear vision. At PropertyME, we bring together local understanding and a complete range of property services to help you shape yours. From the first conversation to the details of everyday villa management, we connect every part of your Bali property journey.',
            'homepage.featured_title' => 'Find Your Place in Bali.', 'homepage.featured_body' => 'A considered collection of properties, selected for the way you want to live and invest.',
            'homepage.investment_title' => 'From Vision to Villa.', 'homepage.investment_body' => 'A seamless path from your first idea to a place you can call your own. Explore the possibilities with a team that connects every stage.',
            'homepage.management_title' => 'Your Villa. Our Attention to Every Detail.', 'homepage.management_body' => 'Thoughtful hospitality. Carefully coordinated operations. A property that is looked after, so you can focus on what matters to you.',
            'homepage.final_title' => 'Your Property Journey Starts Here.', 'homepage.final_body' => "Whether you're looking to invest, buy, rent, or manage a property in Bali, our team is ready to help.",
            'seo.title' => 'PropertyME | Bali Real Estate, Investment & Villa Management',
            'seo.description' => 'Explore Bali real estate, villa development and property management with PropertyME. Your complete partner for investing, buying, renting and managing property.',
        ];
        foreach ($settings as $key => $value) { WebsiteSetting::firstOrCreate(['key' => $key], ['value' => $value, 'type' => 'text']); }
        $this->call(TranslationSeeder::class);
    }
}
