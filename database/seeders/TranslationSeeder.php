<?php

namespace Database\Seeders;

use App\Models\{Amenity, Page, PortfolioCategory, Property, PropertyType, Service, WebsiteSetting};
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    private function add($model, array $fields): void
    {
        if (!$model) return;
        $existing=$model->translationFields('id');
        $model->saveTranslation($existing+$fields,'id');
    }

    public function run(): void
    {
        foreach (['Villa'=>'Vila','Land'=>'Tanah','Apartment'=>'Apartemen','Commercial'=>'Komersial'] as $source=>$translated) $this->add(PropertyType::where('name',$source)->first(),['name'=>$translated]);
        foreach (['Swimming pool'=>'Kolam renang','WiFi'=>'WiFi','Parking'=>'Parkir','Air conditioning'=>'AC','Kitchen'=>'Dapur','Garden'=>'Taman','Security'=>'Keamanan'] as $source=>$translated) $this->add(Amenity::where('name',$source)->first(),['name'=>$translated]);
        foreach (['Villa Development'=>'Pengembangan Villa','Completed Projects'=>'Proyek Selesai','Ongoing Projects'=>'Proyek Berjalan','Managed Properties'=>'Properti yang Dikelola','Architecture & Interior'=>'Arsitektur & Interior'] as $source=>$translated) $this->add(PortfolioCategory::where('name',$source)->first(),['name'=>$translated]);

        $services=[
            'invest-build'=>[
                'title'=>'Investasi & Bangun','short_description'=>'Dari menemukan lahan yang sesuai hingga merancang dan membangun properti investasi Anda.','hero_headline'=>'Dari Gagasan Menjadi Sebuah Villa.','cta_label'=>'Mulai Investasi Anda',
                'content'=>"Pendekatan terencana sejak awal.\n\nSetiap perjalanan properti dimulai dengan memahami tujuan Anda. Kami membantu menjelajahi pilihan lahan, menyusun kebutuhan arsitektur, serta mengoordinasikan perencanaan, pembangunan, dan serah terima.\n\nLingkup layanan dapat mencakup pencarian lahan, desain arsitektur, pengawasan proyek, dan persiapan operasional. Pemeriksaan legal serta nasihat mengenai kepemilikan sebaiknya diperoleh dari profesional independen yang sesuai.\n\nHasil investasi bergantung pada kondisi pasar, biaya, dan keadaan masing-masing. Kami tidak menjanjikan hasil atau kenaikan nilai yang pasti.",
                'workflow_steps'=>"Konsultasi\nPemilihan Lahan\nPerencanaan\nDesain\nPembangunan\nSerah Terima\nPengelolaan",
                'faqs'=>"Bagaimana memulainya? | Mulailah dengan mendiskusikan tujuan, lokasi pilihan, jadwal, dan anggaran Anda.\nApakah ada dukungan setelah pembangunan? | Pengelolaan villa dan dukungan operasional dapat didiskusikan dalam lingkup proyek.\nApakah hasil investasi dijamin? | Tidak. Investasi properti memiliki risiko dan hasil yang berbeda. Dapatkan nasihat keuangan serta legal secara independen.",
            ],
            'list-sell-rent'=>[
                'title'=>'Titip Jual / Sewa','short_description'=>'Titipkan properti kepada tim kami dan hubungkan dengan calon pembeli atau penyewa yang sesuai.','cta_label'=>'Titipkan Properti Anda',
                'content'=>"Properti Anda, ditampilkan dengan perhatian.\n\nKami bekerja bersama pemilik untuk menyiapkan profil properti yang jelas, mengoordinasikan fotografi, serta menyajikan informasi yang dibutuhkan calon pembeli dan penyewa.\n\nLayanan dapat meliputi pemasaran properti, penanganan pertanyaan, koordinasi kunjungan, serta pendampingan proses penjualan atau penyewaan.\n\nKirimkan informasi properti melalui WhatsApp. Tim kami akan mendiskusikan lingkup layanan, informasi yang diperlukan, dan persetujuan Anda sebelum listing dipublikasikan.",
                'workflow_steps'=>"Kirim Informasi Properti\nSepakati Lingkup Listing\nFotografi & Presentasi\nPemasaran\nPertanyaan & Kunjungan\nPendampingan Jual atau Sewa",
                'faqs'=>"Bisakah saya mengunggah properti sendiri? | Listing disiapkan dan dipublikasikan oleh admin setelah informasi serta persetujuan pemilik diterima.\nBagaimana jadwal kunjungan diatur? | Tim akan mengoordinasikan jadwal yang sesuai antara pemilik dan calon pembeli atau penyewa.",
            ],
            'buy-rent'=>[
                'title'=>'Beli & Sewa','short_description'=>'Temukan villa dan pilihan properti di berbagai kawasan Bali.','cta_label'=>'Jelajahi Properti',
                'content'=>"Temukan tempat yang sesuai dengan visi Anda.\n\nJelajahi listing villa, tanah, dan properti lainnya di Bali. Gunakan filter lokasi, jenis properti, dan anggaran, lalu diskusikan detailnya langsung dengan tim kami.\n\nUntuk penyewaan, hubungi kami untuk mengonfirmasi tanggal, harga, dan ketersediaan. Kunjungan, negosiasi, dan pengaturan berikutnya dikoordinasikan secara personal melalui WhatsApp.",
                'workflow_steps'=>"Jelajahi Properti\nDiskusikan Kebutuhan\nKonfirmasi Ketersediaan\nAtur Kunjungan\nSepakati Langkah Berikutnya",
                'faqs'=>"Bisakah villa langsung dipesan di website? | Hubungi tim melalui WhatsApp untuk mengonfirmasi tanggal, harga, dan ketersediaan.\nBisakah tim membantu mencari properti? | Sampaikan lokasi, anggaran, dan kebutuhan Anda untuk mendiskusikan pilihan yang sesuai.",
            ],
            'property-management'=>[
                'title'=>'Pengelolaan Properti','hero_headline'=>'Pengelolaan Properti yang Terarah. Ketenangan bagi Setiap Pemilik.','short_description'=>'Pengelolaan villa dengan dukungan hospitality, perawatan, dan koordinasi keamanan.','cta_label'=>'Kelola Villa Saya',
                'content'=>"Pengelolaan properti yang terarah untuk ketenangan pemilik.\n\nVilla yang dikelola dengan baik membutuhkan perhatian pada detail sehari-hari. Kami mengoordinasikan komunikasi tamu, check-in dan check-out, staf, serta housekeeping untuk mendukung pengalaman menginap.\n\nLayanan mencakup perawatan kolam dan taman, inspeksi properti, perawatan berkala, koordinasi keamanan, serta laporan keuangan dan operasional.\n\nKoordinasi pemasaran dan listing, pengawasan operasional, serta lingkup kerja yang jelas membantu pemilik tetap mendapatkan informasi. Hubungi tim kami untuk mendiskusikan kebutuhan villa Anda.",
                'workflow_steps'=>"Penilaian Properti\nLingkup Pengelolaan\nPersiapan Tim & Operasional\nPelayanan Tamu\nPerawatan & Inspeksi\nLaporan Pemilik",
                'faqs'=>"Layanan apa yang termasuk? | Lingkup disepakati untuk setiap properti, dan dapat mencakup hospitality, housekeeping, perawatan, koordinasi keamanan, dan pelaporan.\nApakah pemilik menerima laporan? | Laporan keuangan serta operasional dapat disertakan dalam lingkup pengelolaan.\nApakah dukungan tersedia 24/7? | Tim akan mengonfirmasi jam dukungan dan pengaturan eskalasi yang berlaku untuk villa Anda.",
            ],
        ];
        foreach($services as $slug=>$fields) $this->add(Service::where('slug',$slug)->first(),$fields);

        $this->add(Page::where('slug','about-us')->first(),[
            'title'=>'Lebih dari Properti. Mitra Lengkap untuk Rencana Anda.',
            'content'=>"PropertyME menghubungkan layanan real estate, perencanaan investasi, pengembangan villa, dan pengelolaan properti di Bali.\n\nPendekatan terpadu kami mencakup setiap tahap perjalanan properti: memahami tujuan, mencari lahan, merencanakan desain, membangun, membeli atau menjual, hingga mengelola operasional villa.\n\nKami melayani investor, pemilik, pembeli, dan penyewa yang menghargai komunikasi yang jelas serta pendekatan yang terencana. Setiap kerja sama dimulai dari percakapan mengenai kebutuhan dan lingkup yang disepakati.\n\nHunian di Bali. Visi untuk masa depan. Tim yang membantu Anda mengambil langkah berikutnya.",
            'seo_title'=>'Tentang PropertyME | Mitra Properti Anda di Bali','meta_description'=>'Kenali PropertyME, mitra investasi properti, pengembangan villa, real estate, dan pengelolaan properti di Bali.',
        ]);

        $baseSettings=[
            'company.mission'=>'To connect every stage of your Bali property journey through clear communication and a considered service scope.',
            'company.approach'=>'We start by understanding your objectives, explain the available services, and agree on clear next steps before moving forward.',
            'company.registration_verified'=>'0',
        ];
        foreach($baseSettings as $key=>$value) WebsiteSetting::firstOrCreate(['key'=>$key],['value'=>$value,'type'=>'text']);
        $settings=[
            'company.tagline'=>'Bali Anda. Visi Anda. Keahlian Kami.',
            'company.description'=>'Mitra properti Anda di Bali. Dari investasi dan pengembangan hingga real estate dan pengelolaan villa.',
            'company.mission'=>'Menghubungkan setiap tahap perjalanan properti Anda di Bali melalui komunikasi yang jelas dan lingkup layanan yang terencana.',
            'company.approach'=>'Kami memahami tujuan Anda, menjelaskan layanan yang tersedia, dan menyepakati langkah berikutnya sebelum memulai kerja sama.',
            'homepage.hero_eyebrow'=>'BALI, INDONESIA · PROPERTI & LEBIH DARI ITU',
            'homepage.hero_headline'=>'Mitra Tepercaya untuk Investasi Properti Anda di Bali',
            'homepage.hero_subheadline'=>'Dari pencarian lahan dan pembangunan villa hingga real estate dan pengelolaan properti — kami membantu mewujudkan visi Anda.',
            'homepage.hero_cta_label'=>'Jelajahi Layanan Kami',
            'homepage.about_title'=>'Lebih dari Properti. Mitra Lengkap untuk Rencana Anda.',
            'homepage.about_body'=>'Properti yang istimewa dimulai dengan visi yang jelas. PropertyME menghubungkan pemahaman lokal dengan layanan properti yang menyeluruh untuk membantu mewujudkannya. Dari percakapan pertama hingga detail pengelolaan villa sehari-hari, kami mendampingi perjalanan properti Anda di Bali.',
            'homepage.featured_title'=>'Properti pilihan','homepage.featured_body'=>'Pilihan properti yang dapat disesuaikan dengan cara Anda ingin tinggal dan berinvestasi.',
            'homepage.investment_title'=>'Dari Visi Menjadi Villa.','homepage.investment_body'=>'Langkah yang terencana dari gagasan awal menuju properti Anda. Jelajahi kemungkinan bersama tim yang menghubungkan setiap tahap.',
            'homepage.management_title'=>'Villa Anda. Perhatian Kami pada Setiap Detail.','homepage.management_body'=>'Hospitality yang penuh perhatian dan operasional yang terkoordinasi, sehingga properti Anda mendapatkan perawatan yang sesuai.',
            'homepage.final_title'=>'Perjalanan Properti Anda Dimulai di Sini.','homepage.final_body'=>'Baik untuk investasi, membeli, menyewa, maupun mengelola properti di Bali, tim kami siap mendiskusikan kebutuhan Anda.',
            'seo.title'=>'PropertyME | Properti, Investasi & Pengelolaan Villa di Bali','seo.description'=>'Jelajahi properti Bali, pengembangan villa, dan pengelolaan properti bersama PropertyME. Mitra Anda untuk investasi, membeli, menyewa, dan mengelola properti.',
        ];
        foreach($settings as $key=>$value) WebsiteSetting::firstOrCreate(['key'=>$key.'.id'],['value'=>$value,'type'=>'text']);
        foreach(Property::where('is_demo',true)->with('propertyType')->get() as $property) $this->add($property,[
            'short_description'=>'Pilihan desain tropis dengan material alami dan hubungan antara ruang dalam dan luar.',
            'description'=>$property->propertyType->slug==='land' ? 'Lanskap hijau dengan vegetasi tropis dan suasana pedesaan. Informasi luas, lokasi, dan kepemilikan dapat didiskusikan lebih lanjut bersama tim.' : 'Konsep hunian tropis dengan material alami, ruang keluarga yang terbuka, serta hubungan yang terencana antara interior dan area luar. Jelajahi galeri serta spesifikasi untuk memahami tata ruang dan fasilitasnya.',
            'furnishing'=>$property->propertyType->slug==='land' ? '' : 'Tata ruang dengan perabot lengkap',
        ]);
    }
}
