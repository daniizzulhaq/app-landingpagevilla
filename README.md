# PropertyME Bali

Website perusahaan real estate, investasi, pengembangan villa, dan property management dengan katalog database dan admin CMS. Pengunjung tidak memerlukan akun. Semua inquiry diarahkan ke WhatsApp setelah nomor perusahaan dikonfigurasi; tidak ada checkout, payment gateway, atau booking otomatis.

## Stack

- Laravel 12, PHP 8.3+, MySQL 8 / MariaDB 10.4+.
- Blade, Tailwind CSS 3.4, Alpine.js 3, Vite 5.
- Autentikasi session Laravel, Form Requests, Gates/Policies, Eloquent, Laravel Storage.
- Ekstensi PHP: `pdo_mysql`, `gd` dengan WebP, `intl`, `fileinfo`, `mbstring`, `openssl`, `curl`, `xml`, `dom`, `zip`.
- Node.js 20.19+ atau 22+ dan Composer 2.

Dependensi Composer dikunci dengan platform PHP 8.3. Lingkungan implementasi menggunakan PHP 8.5.10; pengujian langsung pada binary PHP 8.3 belum dilakukan.

## Menjalankan checkout ini

Repository tidak menyertakan `.env`, `.env.testing`, database sementara, unggahan demo, log, session, atau cache lokal. Gunakan template `.env.example` untuk pengembangan dan `.env.production.example` untuk produksi; keduanya tidak berisi key aplikasi atau password. Ikuti langkah instalasi pada mesin baru di bawah sebelum menjalankan aplikasi.

Setelah environment dan database dikonfigurasi, aktifkan MySQL lalu jalankan:

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

Helper opsional `tools/Start-Local.ps1` juga memeriksa koneksi database dan menjalankan server jika lingkungan Anda mengizinkan eksekusi script PowerShell.

- Website: http://127.0.0.1:8000
- Login admin: http://127.0.0.1:8000/admin/login
- Sitemap: http://127.0.0.1:8000/sitemap.xml

Tidak ada akun atau password admin yang disertakan dalam repository. Buat akun dengan email asli secara interaktif:

```powershell
php artisan propertyme:admin
```

Masukkan email dan password minimal 12 karakter dengan huruf besar, huruf kecil, angka, dan simbol. Password dimasukkan tersembunyi, dikonfirmasi, lalu di-hash. Command menolak menimpa akun yang sudah ada.

## Instalasi pada mesin baru

1. Buat database MySQL kosong dan user khusus dengan akses hanya ke database aplikasi.
2. Salin `.env.example` menjadi `.env`; sesuaikan `APP_URL` dan semua `DB_*`.
3. Jalankan:

```sh
composer install
php artisan key:generate
php artisan migrate
php artisan db:seed
npm ci
npm run build
php artisan storage:link
php artisan propertyme:admin
php artisan serve
```

Seeder bersifat idempotent (`firstOrCreate`) dan tidak menimpa konten admin. Seeder hanya berisi kategori, lokasi, amenities, layanan dari brief, konten editorial, dan konfigurasi awal. **Tidak membuat properti, proyek, testimoni, statistik, nomor telepon, alamat kantor, ataupun akun admin fiktif.**

## Data demo dan desain katalog

Salinan 12 properti dari database lokal, fasilitas, terjemahan, dan 30 gambar galeri disertakan di `database/content/local-properties/`. Lima berkas gambar unik dipakai ulang untuk 30 entri galeri. Setelah kode di-push dan di-pull pada hosting yang sudah terpasang, kunjungan web pertama dengan `APP_ENV=production` memasukkan data dan menyalin gambar secara otomatis. Tidak perlu impor SQL, mengubah `.env`, atau menjalankan seeder. Installer mempertahankan properti hosting yang sudah ada, termasuk edit dan soft delete, serta menampilkan data demo melalui pengaturan database yang tetap berlaku saat konfigurasi di-cache. Lihat [panduan hosting](docs/DEPLOYMENT.md#data-villa-contoh-di-hosting).

Untuk menyertakan properti lokal tambahan pada pembaruan berikutnya, jalankan `php artisan propertyme:export-properties` **di lokal**, lalu sertakan perubahan `database/content/local-properties/` saat push. Installer menambah listing yang belum ada; perubahan pada listing hosting tetap dikelola lewat admin. Akun, session, pengaturan perusahaan, dan database mentah tidak disertakan dalam snapshot. Seeder default sendiri tetap tidak membuat listing.

Seeder demo tidak dipanggil oleh seeder default. Untuk menambah contoh pada instalasi development lain:

```sh
php artisan db:seed --class=DemoContentSeeder
```

Seeder menerima environment `local`/`testing` atau hosting preview yang telah diaktifkan, mempertahankan edit admin dan tidak menggandakan data saat dijalankan lagi. Alur pull otomatis memakai snapshot database lokal, tanpa memanggil seeder demo. Listing serta portfolio demo tidak masuk sitemap dan tidak memiliki schema properti. Akun demo tetap ditolak pada login dan Gate admin produksi. Buat akun dan listing riil sebelum peluncuran.

Hero memakai tab Buy/Sell/Rent/Management dan pencarian database. Bagian featured memiliki filter For sale/For rent serta All/Leasehold/Freehold/Land. Kartu katalog menampilkan cover, harga, badge ownership/development, spesifikasi, dan carousel foto. Tombol berbagi menggunakan native share atau menyalin link; tombol hati menyimpan pilihan di `localStorage` browser tanpa akun pelanggan.

Lima ilustrasi AI original tersimpan di `public/images/demo/` dan disalin oleh seeder ke Laravel Storage untuk galeri serta konten halaman. Prompt dan asal aset dicatat di [resources/demo/image-prompts.json](resources/demo/image-prompts.json). Gambar dibuat menggunakan built-in `image_gen`; gambar ini tidak mendokumentasikan properti atau proyek nyata.

`tools/configure-local-database.php` hanya untuk bootstrap MySQL XAMPP lokal pada port 3306 dengan user root tanpa password. Script membuat dua database bernama khusus proyek jika belum ada, tanpa mengubah database lain atau akun MySQL. Script menolak mengubah `.env` yang ada kecuali flag `--reconfigure` diberikan secara eksplisit. Tidak digunakan pada server produksi.

Untuk pengembangan frontend, jalankan `npm run dev` di terminal kedua. Build produksi menggunakan JavaScript API Vite melalui `tools/build.mjs` agar tidak bergantung pada bundling config esbuild yang dibatasi sandbox Windows. File `vite.config.js` tetap tersedia untuk alur pengembangan biasa. Folder `public/build/` beserta manifest disertakan dalam Git; jalankan `npm run build` setelah perubahan frontend dan sertakan hasilnya saat commit.

## Mengelola website

Urutan pengisian yang disarankan:

1. **Website Settings**: nama, logo, favicon, deskripsi, kontak, WhatsApp, Instagram, jam kantor, dan koordinat kantor. Nomor WhatsApp dinormalisasi ke format internasional. Nomor lokal berawalan `0` diasumsikan Indonesia (`62`). Gunakan kode negara lengkap untuk negara lain.
2. **Homepage Content**: hero, CTA dan URL, gambar, about, featured collection, pengantar investasi dan management, serta CTA akhir.
3. **Property Categories / Locations / Amenities**: kelola referensi sebelum memasukkan listing.
4. **Properties**: isi informasi yang diverifikasi, legal/ownership bila tersedia, harga dan mata uang, galeri, status, SEO, dan publikasi. Draft tidak dapat diakses melalui slug publik.
5. **Portfolio / Portfolio Categories**: masukkan proyek yang disetujui perusahaan. Tentukan cover, galeri, kategori, dan status publikasi.
6. **Services**: ubah ringkasan, hero opsional, konten, foto, urutan, workflow, dan FAQ. Format workflow adalah satu langkah per baris. Format FAQ adalah `Pertanyaan | Jawaban`, satu pasangan per baris.
7. **Testimonials**: masukkan hanya ulasan asli dengan persetujuan yang sesuai. Publikasikan dengan checkbox verifikasi; rating tidak wajib.
8. **Pages**: kelola About Us, Privacy Policy, dan halaman editorial tambahan. Tersedia toggle indeks pencarian. Pertahankan slug inti bila memungkinkan.
9. **Company Team**: isi nama, jabatan, bio dan foto yang disetujui. Hanya anggota dengan checkbox publikasi aktif yang tampil di About dan profil perusahaan.
10. **SEO Settings / My Profile**: lengkapi metadata default dan kelola akun.

Semua konten panjang diperlakukan sebagai **plain text** dan di-escape oleh Blade. Pisahkan paragraf dengan satu baris kosong. Tidak ada editor HTML yang mengizinkan script. Konten JSON-LD menggunakan encoding aman.

## Bahasa, profil perusahaan dan pilihan properti

Pilihan **EN / ID** tersedia pada navbar. Bahasa dipilih lewat parameter `?lang=id` atau `?lang=en` dan diingat dengan cookie. Admin selalu menggunakan bahasa Inggris. Tampilan Indonesia mencakup navigasi, pencarian, informasi layanan, label properti dan pesan WhatsApp. Isi yang belum diterjemahkan memakai teks Inggris sebagai fallback, tanpa menerjemahkan otomatis harga, alamat atau identitas properti.

Form Properties, Portfolio, Services, Pages, Testimonials dan Company Team memiliki bagian **Indonesian Content**. Website Settings, Homepage Content dan SEO Settings menyediakan tab Indonesia. Terjemahan disimpan terpisah di `content_translations` / kunci settings berakhiran `.id`; slug, media, harga dan nomor kontak tetap sama. Pencarian katalog Indonesia juga mencari judul/ringkasan terjemahan.

Halaman **About Us** memuat cerita perusahaan, misi, pendekatan, layanan dan tim yang disetujui. Detail legal hanya tampil setelah checkbox verifikasi di Website Settings diaktifkan. Tidak ada anggota tim atau legalitas yang dikarang. **Company Profile** (`/company-profile`) menyediakan tombol **Print / Save PDF**, memakai dialog cetak browser. Hanya proyek asli yang published dimasukkan ke profil cetak.

Tombol hati membuka pilihan tersimpan di **Saved Properties** (`/favorites`). Tombol **Compare** menambahkan maksimal tiga properti untuk dibandingkan di `/compare`: harga beserta mata uang/unit asli, lokasi, spesifikasi dan fasilitas. Pilihan disimpan pada browser tanpa akun; halaman mengambil ulang data published dari database, mempertahankan urutan pilihan dan memberi opsi menghapus properti yang sudah tidak tersedia. Draft, harga tersembunyi dan koordinat pribadi tidak dibocorkan oleh endpoint `/property-selection`. Halaman pilihan dan endpoint tidak dimasukkan ke sitemap.

## WhatsApp, Contact dan widget chat

Isi nomor aktif pada **Admin → Website Settings → WhatsApp** (`/admin/settings/company`). Satu nomor dari database digunakan untuk kartu properti, detail, layanan, Contact, navbar dan widget. Tidak ada nomor WhatsApp bawaan. Selama nomor belum diisi, tombol menampilkan opsi kontak dan admin melihat petunjuk pengaturan; aplikasi tidak mengarang penerima chat.

Kartu properti memiliki CTA WhatsApp langsung. Properti rental menyiapkan pesan untuk tanggal, tarif dan jumlah tamu; properti dijual menyertakan nama serta link untuk harga/viewing. Status sold/rented/reserved menanyakan alternatif. Halaman Contact memiliki logo WhatsApp yang bisa ditekan setelah nomor diisi, serta enam topik konsultasi yang menyiapkan pesan berbeda.

Widget **Quick help / History** merupakan asisten pilihan cepat yang melanjutkan percakapan ke WhatsApp. Pengunjung dapat memilih topik dan menambahkan pertanyaan. Widget mengikuti konteks properti ketika dibuka dari halaman detail. History hanya mencatat inquiry yang dibuka pengguna pada browser tersebut, maksimal 10 item; tidak menyatakan pesan sudah terkirim, dibaca atau dijawab. Tidak ada inbox customer, booking otomatis, chatbot AI berbayar, atau WhatsApp Business API.

Teks pertanyaan dan riwayat disimpan lokal di browser, bukan pada database website. Pengguna bisa menghapus riwayat dari widget. Jika browser menolak localStorage, komposisi pesan tetap berfungsi dengan riwayat sementara.

Tes logika widget dapat dijalankan tanpa dependency tambahan:

```sh
node --test tests/js/*.test.mjs
```

Galeri mendukung unggah beberapa foto, preview, alt text, urutan angka, cover utama properti, dan penghapusan. Hanya JPEG/PNG/WebP yang diterima; maksimal 8 MB, dimensi 6000×6000, dan 16 megapiksel per gambar. Foto di-decode ulang, diubah ke WebP dengan sisi terpanjang maksimal 2200 px, lalu disimpan dengan UUID. Metadata file sumber tidak dipertahankan.

Media memakai URL `/media/images/{uuid}.webp` dengan validasi path dan header cache, sehingga berfungsi tanpa symlink hosting. `storage:link` tetap didukung. File asli unggahan tidak dipublikasikan. Properties memakai soft delete; media properti yang dihapus dipertahankan untuk kebutuhan pemulihan/retensi dan harus disertakan dalam kebijakan retensi perusahaan.

Filter harga dan pengurutan numerik menggunakan satu mata uang; default IDR jika belum dipilih. Harga dengan unit berbeda (malam/bulan/tahun) tetap ditampilkan dengan unit aslinya. Tidak ada konversi kurs atau penyamaan unit otomatis.

## Konten sebelum peluncuran

- Lengkapi nomor WhatsApp dan detail kontak nyata. Sebelum diisi, CTA mengarah ke halaman kontak yang menjelaskan bahwa kontak belum tersedia.
- Gambar default sekarang mengutamakan ilustrasi AI lokal pada `public/images/demo/`, **bukan** dokumentasi properti atau pekerjaan PropertyME. Fallback eksternal dari `images.unsplash.com` hanya digunakan jika ilustrasi lokal tidak tersedia. Unggah foto yang telah disetujui perusahaan untuk listing riil. Untuk aset fallback eksternal, rujuk [lisensi Unsplash](https://unsplash.com/license) dan verifikasi hak penggunaan sebelum produksi.
- Font Cormorant Garamond dan Inter dimuat dari Google Fonts. Jika koneksi eksternal tidak tersedia, fallback lokal Georgia/Arial tetap bekerja. Foto eksternal memiliki fallback ilustrasi arsitektur SVG original di `public/images/architecture.svg`.
- Privacy Policy yang disertakan ditandai **editorial draft** dan tidak diindeks. Perusahaan harus meninjau identitas hukum, retensi, transfer data dan hak subjek data sebelum mengaktifkan indeks. Ini bukan dokumen hukum final.
- Klaim dukungan 24/7 tidak aktif secara default. Aktifkan hanya jika operasional memang menyediakannya. Tidak ada badge Superhost, janji ROI, statistik, atau ulasan yang dibuat-buat.
- Jalankan audit visual dan aksesibilitas di perangkat nyata sebelum peluncuran. Daftar viewport: 320, 375, 390, 430, 768, 1024, 1280, 1440 px.

## Pengujian

Buat database pengujian **terpisah** dengan nama yang berakhiran `_test`, lalu salin `.env` ke `.env.testing` dan sesuaikan `DB_DATABASE`. Gunakan user dengan akses hanya ke database development/test yang dibutuhkan.

```sh
php artisan migrate --env=testing
php artisan test
npm run build
composer check-platform-reqs
```

File `.env.testing` harus dibuat sendiri secara lokal dan tetap diabaikan Git. Suite memakai transaksi dan rollback; **tidak** menggunakan `migrate:fresh` atau menghapus database. Base test menolak konfigurasi database yang tidak berakhiran `_test`. Fixtures hanya dibuat dalam transaksi database pengujian.

Tes mencakup halaman publik, autentikasi dan rate limit, CSRF, pengaturan akun, password reset, proteksi admin, CRUD properti dan portfolio, upload galeri, metadata gambar, validasi executable, slug redirect, publish/unpublish, filter dan pagination, kerahasiaan harga/lokasi, CMS, normalisasi WhatsApp, XSS, sitemap, structured data, header keamanan, error 404/500, seed demo idempotent, featured tabs, pembatasan akun dan data demo pada produksi, pilihan bahasa, terjemahan CMS, CRUD tim, profil cetak, seleksi published, privasi data dan batas perbandingan.

Hasil pengujian implementasi dicatat di [docs/VERIFICATION.md](docs/VERIFICATION.md). Jangan menyimpulkan kesiapan visual, Core Web Vitals, SMTP, atau HTTPS dari Feature Tests saja.

## Deployment

Lihat [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md) untuk shared hosting/VPS, konfigurasi produksi, cache, SSL, backup, dan rollback. [Dokumentasi Laravel 12](https://laravel.com/docs/12.x) menjadi referensi framework.
