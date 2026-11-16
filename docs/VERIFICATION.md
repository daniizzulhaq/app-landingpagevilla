# Catatan verifikasi implementasi

Status ini membedakan pemeriksaan yang dijalankan dan yang memerlukan lingkungan/perangkat lain.

## Lingkungan

- Windows, PHP 8.5.10, Laravel 12, Node.js 24.
- MariaDB 10.4 XAMPP pada 127.0.0.1:3306, instance yang sama dengan phpMyAdmin pengguna.
- Database development `propertyme_bali_website`; database pengujian `propertyme_bali_website_test`.
- User lokal `root` dengan password database kosong, sesuai permintaan.
- Composer memakai platform PHP 8.3 untuk pemilihan dependensi. Runtime PHP 8.3 sendiri belum tersedia untuk diuji.

## Dijalankan

- Migrasi development dan testing, seeder referensi idempotent.
- Laravel Feature dan Unit Tests, dengan database transaksi terpisah.
- Build Vite produksi.
- Kompilasi view Blade dan cache route Laravel.
- `composer check-platform-reqs`.
- Pemeriksaan HTTP homepage, robots dan sitemap lokal: status 200.

Suite terbaru: **65 tests passed, 512 assertions** pada database pengujian XAMPP `propertyme_bali_website_test`, user root dengan password kosong. **12 tes JavaScript** berhasil melalui `node --test tests/js/*.test.mjs`. Build Vite berhasil. CSS produksi 60.30 kB (10.86 kB gzip), JavaScript 62.06 kB (22.09 kB gzip). Build dan cache view diselesaikan sebelum suite pengujian.

Request kernel pada database development berhasil untuk homepage, katalog, filter rental, detail properti, portfolio, detail portfolio, layanan, login, sitemap dan file media WebP (seluruhnya status 200). Hash akun lokal juga diverifikasi sesuai kredensial yang diminta pengguna.

Development berisi 12 properti demo, 6 konsep portfolio, 3 contoh layout testimoni, 4 layanan, dan 1 akun admin lokal. Semua gambar demo disimpan lokal. Banner/badge demo serta caption ?Architectural inspiration ? illustrative imagery? dihapus dari publik sesuai permintaan. Contoh testimoni tidak ditampilkan. Tes memverifikasi seed idempotent, pembersihan label publik, noindex, pengecualian sitemap, featured filters, dan penolakan akun serta konten contoh pada mode produksi.

Halaman Contact sudah memiliki kartu WhatsApp, ikon yang dapat ditekan, dan enam topik inquiry. Nomor yang sekarang terisi di Website Settings dipakai oleh semua CTA. Pemeriksaan markup pada data development menemukan 13 tautan WhatsApp di Contact, 13 di katalog, 5 di detail contoh, dan 6 di masing-masing halaman layanan investasi/management. Semua mengarah ke nomor konfigurasi yang sama. Sembilan kartu di halaman katalog pertama memiliki CTA WhatsApp masing-masing beserta nama propertinya. Pemeriksaan ini tidak mengirim pesan.

Widget Quick help/History mengomposisi pesan sesuai topik atau properti. Tes JavaScript memeriksa encoding, nomor kosong/invalid, pemilihan topik, riwayat hanya setelah membuka inquiry, batas 10 riwayat, perubahan nomor perusahaan, dan fallback localStorage. Riwayat tidak mengklaim pesan terkirim atau dibaca.


## Peningkatan perusahaan dan navigasi

- Menu desktop kembali mendatar pada navbar mulai lebar 1024 px. Menu layar kecil memakai dropdown ringkas dengan Escape, click outside, indikator expanded, dan penutupan saat beralih ke desktop. Styling desktop/mobile belum dinilai melalui browser nyata.
- Pilihan EN/ID, cookie preferensi, konten Indonesia terpisah, fallback Inggris, pesan WhatsApp sesuai bahasa, serta pencarian judul/ringkasan terjemahan. Admin selalu memakai bahasa Inggris.
- CRUD Company Team, publikasi anggota yang disetujui, misi/pendekatan dan detail legal opsional. Tidak membuat data orang/legalitas contoh. Profil perusahaan dapat dicetak/disimpan PDF melalui browser dan hanya memuat proyek asli yang published.
- Saved Properties dan perbandingan maksimal tiga properti memakai localStorage, API published, urutan pilihan, penanganan item tidak tersedia dan fallback storage. Harga tersembunyi serta koordinat privat tidak dikirim oleh API.
- Delapan Feature Tests baru memverifikasi bahasa, terjemahan CMS/settings, validasi field terjemahan, output aman, seleksi properti, batas API, tim, profil cetak dan markup navigasi. Enam tes JavaScript baru memeriksa seleksi, batas, storage tertolak, retry dan respons request lama.
- Smoke test kernel development: **26 halaman (13 route ? EN/ID)** memberi status 200 tanpa label yang diminta dihapus. Endpoint seleksi juga berhasil dengan tautan WhatsApp ke nomor konfigurasi saat ini. Tidak ada pesan dikirim.

## Belum diverifikasi

- Render visual browser dan pengukuran overflow per viewport. Peluncuran Edge otomatis ditolak kebijakan izin sandbox karena membutuhkan approval yang dinonaktifkan.
- Interaksi browser nyata untuk menu, widget chat, dialog lightbox, preview upload, konfirmasi delete, fokus dan reduced motion. Implementasi, logika widget dan render HTML diuji, tetapi bukan pengganti pengujian browser.
- Skor Lighthouse/Core Web Vitals, network image/font eksternal, dan tampilan pada perangkat nyata.
- Pengiriman SMTP, domain produksi, SSL, konfigurasi web server hosting dan backup restore.
- Audit vulnerability daring karena jaringan terminal dibatasi.

## Konten yang membutuhkan perusahaan

Nomor WhatsApp, detail kontak, legalitas, logo final, foto yang disetujui, properti, portfolio dan testimoni asli harus dimasukkan lewat admin. Tidak ada klaim bahwa data tersebut sudah diverifikasi. Privacy Policy masih berupa draft editorial.
