# Nama villa pada preview

Empat nama villa sewa disesuaikan dengan data aplikasi PropertyME yang terkait
`propertymevillas.com`: Avora, Sofi 1, Sofi 2, dan Casa Daluca (nama lokal: Daluca).
Sumber pemetaan adalah database aplikasi PropertyME lokal dan dokumentasi
`app-villa/docs/hostex-four-villas-hosting.md` yang mencatat verifikasi 11 November 2026.
Website hosting memerlukan login dan tidak diakses untuk mengambil data pribadi.

| Kode preview | Nama sebelumnya | Nama baru |
|---|---|---|
| DEMO-PM-007 | Tropical Courtyard Stay | Avora |
| DEMO-PM-008 | Ubud Forest Hideaway | Sofi 1 |
| DEMO-PM-009 | Cliffside Ocean Villa | Sofi 2 |
| DEMO-PM-010 | Seminyak Living Villa | Casa Daluca |

Ini penyesuaian nama pada slot preview. Foto AI, harga, lokasi, status, dan
spesifikasi contoh tidak diverifikasi sebagai detail villa tersebut. Record tetap
`is_demo` dan `noindex` secara internal. Label dan pemberitahuan demo tidak
ditampilkan pada halaman publik.

Setelah push/pull dan kunjungan berikutnya, installer mengganti hanya nama lama
yang masih persis sama pada record demo yang belum dihapus. Judul SEO dan alt
gambar bawaan ikut disesuaikan bila masih memakai teks asli. Nama/SEO/alt yang
telah disunting di hosting, record non-demo, dan soft delete dipertahankan.
Slug lama dipertahankan agar URL tetap berfungsi. Pemetaan tetap tersimpan dalam
`database/content/local-properties/title-updates.json` dan disertakan otomatis
ketika menjalankan `php artisan propertyme:export-properties` lagi.
