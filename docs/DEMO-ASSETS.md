# Ilustrasi untuk preview PropertyME

Gambar dibuat menggunakan **built-in image_gen**, tanpa mengambil foto atau watermark dari situs referensi. Semua adegan merupakan visualisasi fiktif untuk konten demo, bukan foto listing yang sudah diverifikasi.

| File lokal | Isi |
| --- | --- |
| `public/images/demo/hero.webp` | Villa tepi tebing, infinity pool dan laut saat matahari terbenam |
| `public/images/demo/courtyard.webp` | Villa courtyard tropis dengan kolam pribadi |
| `public/images/demo/interior.webp` | Interior villa dengan sofa, teak dan batu alam |
| `public/images/demo/jungle.webp` | Pavilion dan kolam dengan latar hutan tropis |
| `public/images/demo/land.webp` | Ilustrasi lahan hijau, sawah dan pegunungan |

Seluruh prompt ada di [image-prompts.json](../resources/demo/image-prompts.json). Versi WebP mempertahankan komposisi hasil generasi dan hanya dikompresi untuk penggunaan website. Hasil sumber tetap disimpan pada direktori generated images milik alat; website memakai salinan di workspace.

`DemoContentSeeder` menyalin tiap penggunaan ke path media UUID yang berbeda. Menghapus foto satu properti tidak menghapus file galeri properti lain. Penanda internal `is_demo` mengendalikan indeks dan visibilitas produksi. Sesuai permintaan, badge dan pemberitahuan demo tidak tampil pada preview publik lokal; contoh testimoni tetap disembunyikan.

Sebelum peluncuran, ganti listing demo dengan data asli dan unggah foto yang diizinkan melalui admin. Seeder ini menolak berjalan pada production.
