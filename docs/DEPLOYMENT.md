# Deployment PropertyME

## Persiapan

- PHP 8.3+ dengan ekstensi pada README, Composer 2, dan MySQL/MariaDB yang kompatibel.
- Domain, SSL aktif, dan document root ke direktori `public/` proyek. Jangan arahkan document root ke root repository.
- User database khusus aplikasi. Buat database kosong untuk instalasi baru; untuk upgrade gunakan database yang ada dan backup dahulu.
- `storage/` dan `bootstrap/cache/` dapat ditulis oleh user PHP. Hindari permission `777`.
- Hasil build di `public/build/` termasuk manifest disertakan dalam Git. Jalankan `npm run build` di lokal setelah perubahan frontend dan commit hasilnya; unggah seluruh folder tersebut jika shared hosting tidak memiliki Node.js.
- Atur `upload_max_filesize=8M`, `post_max_size` yang cukup untuk galeri (misalnya `180M`), `max_file_uploads=20`, dan memory PHP setidaknya `256M`. Sesuaikan juga batas request Nginx/Apache atau panel hosting.

## Environment

Gunakan `.env.production.example` sebagai acuan, isi rahasia langsung di server atau secret store. Jangan commit `.env` dan jangan menyalin `.env` pengembangan ke produksi.

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-perusahaan-anda
SESSION_SECURE_COOKIE=true
SESSION_ENCRYPT=true
LOG_CHANNEL=daily
LOG_LEVEL=warning
```

Isi `DB_*` dari hosting. Buat `APP_KEY` dengan `php artisan key:generate` hanya pada instalasi baru; pertahankan key pada update. Perubahan key membatalkan session terenkripsi dan data terenkripsi lain.

## Instalasi

```sh
composer install --no-dev --prefer-dist --optimize-autoloader
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan propertyme:admin
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

`npm` tidak perlu dijalankan di server jika aset produksi sudah dibangun dan diunggah. Jangan menjalankan `key:generate` ulang saat update. Jangan menjalankan perintah reset database pada data produksi.

Seeder tidak menimpa data yang sudah ada. Perubahan copy editorial berikutnya dilakukan melalui CMS. Seluruh akun aplikasi adalah admin dan hanya dibuat melalui command; tidak ada registrasi publik.

Repository menyertakan snapshot khusus listing lokal dan gambar galerinya; akun, session, rahasia dan database mentah tidak disertakan. `DemoContentSeeder` tersedia secara opsional, terpisah dari `DatabaseSeeder`. Alur otomatis berikut tidak memanggil seeder demo. Akun demo tetap ditolak oleh autentikasi/Gate. Buat akun admin riil dengan email perusahaan melalui command sebelum peluncuran.

## Data villa contoh di hosting

Untuk hosting yang sudah menjalankan aplikasi dan memiliki database beserta tabelnya, cukup push semua perubahan dari lokal, kemudian jalankan di hosting:

```sh
git pull
```

Lalu buka website. Pada kunjungan HTTP pertama dengan `APP_ENV=production`, middleware membaca `database/content/local-properties/`, memasukkan salinan **12 properti lokal**, fasilitas dan terjemahan, serta menyalin **30 gambar galeri** ke `storage/app/public/images/`. Gambar identik disimpan sekali di Git dan disalin sesuai nama file aslinya saat pemasangan. Media disajikan lewat `/media/images/{uuid}.webp`, tanpa wajib symlink.

Tidak perlu mengimpor SQL, mengubah `.env`, menjalankan seeder, memasang Git hook, atau memperbarui cache konfigurasi. Visibilitas record demo diaktifkan melalui setting database, sehingga konfigurasi hosting yang sudah di-cache tetap dapat dipakai. Pertahankan `APP_DEBUG=false` dan izin tulis Laravel yang sudah ada untuk `storage/`.

Installer menyimpan checksum versi di database. Kunjungan berikutnya hanya memeriksa versi, tanpa mengulang impor. Lock file dan lock database mencegah dua kunjungan mengimpor bersamaan. Media diverifikasi dan disalin sebelum transaksi database ditandai selesai; pemasangan yang gagal dapat dicoba lagi pada kunjungan berikutnya.

Record dengan kode **atau** slug yang sudah ada dilewati beserta relasinya. Edit admin, draft, dan soft delete dipertahankan; ID referensi dipetakan ulang sesuai database hosting. Installer tidak membuat atau mengubah akun, pengaturan perusahaan, portfolio, layanan atau testimoni. Label dan pemberitahuan demo tidak ditampilkan pada halaman publik. Penanda internal dan `noindex` tetap berlaku; listing demo tetap dikecualikan dari sitemap dan schema properti.

Pembaruan nama terarah untuk empat villa sewa mengganti nama preview lama dengan Avora, Sofi 1, Sofi 2 dan Casa Daluca pada record demo yang masih memakai nama bawaan. Nama yang sudah disunting di hosting tetap dipertahankan. Lihat [pemetaan dan sumber nama](PROPERTY-NAMES.md).

Untuk memasukkan properti lokal tambahan pada pembaruan berikutnya, jalankan **di lokal sebelum push**:

```sh
php artisan propertyme:export-properties
```

Sertakan seluruh perubahan `database/content/local-properties/` saat commit/push; di hosting tetap cukup pull dan buka website. Data untuk listing yang sudah ada di hosting dikelola melalui admin; snapshot tidak menimpanya. Snapshot dapat diekspor hanya pada environment `local`/`testing`.

Saat ingin menyembunyikan preview otomatis, ubah nilai `website_settings` untuk key `demo.local_properties_enabled` menjadi `0` dan pastikan `DEMO_CONTENT_ENABLED` tidak aktif. Installer mempertahankan pilihan ini pada versi berikutnya. Data listing tidak dihapus. Instalasi hosting baru tetap mengikuti langkah instalasi aplikasi di atas untuk menyiapkan database, dependensi dan akun terlebih dahulu.

## Web server

Shared hosting Apache: document root ke `public/`, aktifkan `mod_rewrite`. `.htaccess` menyertakan Laravel rewrite dan cache aset. Media aplikasi hanya menyajikan WebP yang namanya sesuai pola UUID.

Contoh konfigurasi Nginx, sesuaikan path dan socket PHP:

```nginx
server {
    listen 443 ssl;
    server_name property.example;
    root /var/www/propertyme/public;
    index index.php;
    client_max_body_size 180M;

    # Configure ssl_certificate and ssl_certificate_key using your certificate manager.
    location / { try_files $uri $uri/ /index.php?$query_string; }
    location ~ ^/index\.php(/|$) {
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }
    location ~ \.php$ { deny all; }
    location ~ /\.(?!well-known).* { deny all; }
    location /build/ {
        try_files $uri =404;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

Alihkan HTTP ke HTTPS di server/panel. Aplikasi memaksa pembuatan URL HTTPS saat production dan menambahkan HSTS. Jika berada di belakang reverse proxy, konfigurasikan daftar proxy terpercaya sesuai infrastruktur; jangan mempercayai semua proxy tanpa kebutuhan.

Pastikan PHP tidak dapat mengeksekusi file pada direktori upload. Semua upload aplikasi diubah menjadi `.webp`, tetapi pembatasan server tetap dianjurkan. Direktori `.runtime/`, `.git/`, `vendor/`, `tools/`, `storage/logs/`, serta `.env*` harus berada di luar document root.

## Email dan reset password

Reset password dinonaktifkan secara default. Agar aktif, konfigurasi SMTP nyata, `MAIL_FROM_ADDRESS` yang valid, SPF/DKIM/DMARC sesuai provider, dan uji pengiriman ke akun admin sendiri. Setelah itu set:

```dotenv
PASSWORD_RESET_ENABLED=true
```

Jalankan ulang `php artisan config:cache`. Link reset memakai token Laravel yang memiliki masa berlaku, tersimpan hashed, dan sekali pakai. Respons permintaan reset tidak mengungkap keberadaan email. Pembersihan token kedaluwarsa dapat dijadwalkan harian dengan `php artisan auth:clear-resets`.

## Pemeriksaan peluncuran

1. Verifikasi nama perusahaan, WhatsApp, email, alamat, jam kantor, logo, favicon dan koordinat kantor.
2. Ganti foto referensi dengan aset yang diizinkan. Uji seluruh file media dari domain produksi.
3. Lengkapi Privacy Policy dan aktifkan indeks setelah disetujui perusahaan.
4. Uji login/logout, CRUD draft, galeri, publikasi, filter, detail, dan link WhatsApp dengan perangkat sungguhan.
5. Pastikan draft tidak tersedia, koordinat tidak bocor, serta Sold/Rented tidak menawarkan pembelian langsung.
6. Periksa canonical/OG, robots dan sitemap pada domain nyata. Jangan menaruh `public/robots.txt` statis karena akan menghalangi endpoint robots dinamis.
7. Uji tampilan semua viewport README, keyboard/fokus, reduced motion, galeri dan mobile sticky CTA.
8. Jalankan Lighthouse/PageSpeed pada URL produksi dan optimalkan berdasarkan hasil. Tidak ada skor Core Web Vitals yang dijanjikan tanpa pengukuran.
9. Verifikasi log hanya tersimpan di server, `APP_DEBUG=false`, cookie aman dan SSL. Hindari shared cache/CDN untuk `/admin/*` karena kontennya private.

## Backup dan update

- Backup database harian dan `storage/app/public/` secara konsisten. Backup juga rahasia environment dengan enkripsi dan pembatasan akses.
- Simpan beberapa generasi backup di lokasi terpisah; jadwalkan uji pemulihan. Waktu retensi ditentukan perusahaan.
- Property soft delete mempertahankan record dan gambar. Susun kebijakan penghapusan permanen dan retensi data/audit; tidak ada job penghapusan massal otomatis.
- Perubahan galeri dan database menggunakan transaksi database. Filesystem tidak mendukung transaksi bersama MySQL; proses terputus dapat meninggalkan file tanpa referensi. Tinjau file yang tidak direferensikan sebelum pembersihan manual dan selalu backup.
- Sebelum update: backup, siapkan release baru, `composer install` sesuai lock, build aset, jalankan tes pada database terpisah, lalu deploy dan `php artisan migrate --force`.
- Rollback aplikasi dilakukan dengan mengembalikan release kode sebelumnya yang kompatibel dengan skema saat ini. Jangan mengandalkan rollback migration destruktif terhadap data produksi; pulihkan backup pada instance terpisah bila diperlukan.
- Jalankan audit keamanan dependensi secara berkala pada lingkungan dengan jaringan, misalnya `composer audit` dan `npm audit`. Audit daring tidak dapat dijalankan dalam sandbox implementasi ini.
