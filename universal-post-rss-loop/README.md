# 🚀 Universal Post & RSS Loop (WordPress Plugin)

**Universal Post & RSS Loop** adalah plugin WordPress modern, fleksibel, dan kaya fitur yang dirancang untuk menampilkan **WordPress Posts** maupun **External RSS Feeds** menggunakan desain card grid yang konsisten, responsif, dan elegan.

- **Penulis**: Mujaddid Halimurrosyid
- **Situs Penulis**: [https://ajidmujaddid.staff.telkomuniversity.ac.id/](https://ajidmujaddid.staff.telkomuniversity.ac.id/)
- **Versi**: 2.0.3
- **Lisensi**: GPL-2.0+
- **Repository GitHub**: [https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin](https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin)

---

## ✨ Fitur-Fitur Unggulan

### 1. 🔄 Sakelar Sumber Data (*Data Source Switch*)
- **WordPress Posts**: Otomatis mendeteksi seluruh *Public Post Types* terdaftar (`post`, `page`, `portfolio`, `product`, custom post type) dan Kategori di WordPress Anda.
- **External RSS Feeds**: Membaca dan menampilkan berita dari situs RSS luar secara *live* tanpa mengotori atau menyimpan artikel ke dalam database WordPress.
- **Aggregator Multi-Feed**: Menggabungkan beberapa URL RSS Feed sekaligus dalam satu tampilan grid seragam.

### 2. 🎨 5 Preset Desain Card Modern
- **Classic Card**: Desain bersih dengan garis tepi (*border*) halus dan bayangan lembut.
- **Modern Card**: Bayangan melayang (*floating shadow*) dengan badge kategori/sumber efek kaca buram di atas gambar.
- **Minimalist**: Desain *flat* minimalis dengan garis pembatas tipis di bagian bawah.
- **Overlay Hero**: Gambar latar penuh (*100% height backdrop*) dengan efek gradient gelap dan teks putih yang sangat tajam dan kontras.
- **Glassmorphism**: Efek transparan miring bergaya kaca buram (*frosted glass*) modern.

### 3. ⚡ Fitur Interaktif & UX Lanjutan
- **🔍 Live Search Bar**: Kolom pencarian berita *real-time* langsung saat pengunjung mengetik.
- **🗂️ Tab Filter Kategori**: Tombol filter kategori dinamis dengan efek animasi yang halus.
- **🔄 Mode Navigasi Halaman**: Pilihan tombol **Load More** (*Muat Lebih Banyak*) atau **Nomor Halaman (1, 2, 3...)**.
- **⏱️ Estimasi Waktu Baca**: Menghitung durasi baca otomatis (`⏱️ X min read`).
- **💬 Tombol Bagikan Sosial Media**: Berbagi instan ke WhatsApp, Twitter/X, Facebook, dan LinkedIn.
- **⚙️ WP-Cron Pre-caching**: Caching otomatis di latar belakang secara berkala untuk memastikan kecepatan muat halaman **0 ms (Instant Load)**.

### 4. 🛠️ Integrasi Page Builder & Shortcode
- **Gutenberg Block**: Blok kustom native (`upr/universal-post-rss-loop`) dengan *Live Server-side Preview* di dalam layar editor Gutenberg.
- **WPBakery Page Builder**: Terintegrasi penuh via `vc_map()` dengan 7 tab parameter yang tersusun rapi dan ColorPicker visual.
- **Shortcode Fleksibel**: Dapat dipasang di editor klasik, widget, atau file template PHP theme.

### 5. 🔄 Pembaruan Otomatis 1-Klik dari GitHub
- Plugin ini dilengkapi dengan sistem **Native GitHub Auto-Updater**.
- Setiap ada rilis versi baru di GitHub, notifikasi pembaruan akan **otomatis muncul di dashboard `wp-admin/plugins.php`** Anda dan dapat diperbarui cukup dengan 1x klik tombol **Update Now**.

---

## 💻 Contoh Penggunaan Shortcode

### 1. Grid Dasar untuk WordPress Posts
```shortcode
[universal_post_rss_loop source="posts" limit="6" columns="3" card_style="modern"]
```

### 2. Menampilkan RSS Feed Luar
```shortcode
[universal_post_rss_loop source="rss" feed_url="https://news.ycombinator.com/rss" limit="6" card_style="classic"]
```

### 3. Tampilan Interaktif Lengkap (Live Search, Tab Filter & Load More)
```shortcode
[universal_post_rss_loop source="posts" card_style="overlay" show_search_bar="true" show_filter_tabs="true" show_read_time="true" show_social_share="true" pagination_type="load_more" items_per_page="6"]
```

---

## 📋 Tabel Referensi Parameter Shortcode

| Parameter | Pilihan / Default | Keterangan |
|---|---|---|
| `source` | `posts` \| `rss` (Default: `posts`) | Pilihan sumber data (WP Posts atau RSS Feed) |
| `post_type` | `post`, `page`, dsb. (Default: `post`) | Tipe postingan WordPress |
| `category` | Slug Kategori | Filter postingan berdasarkan slug kategori |
| `feed_url` | URL RSS Feed | URL sumber RSS Feed tunggal |
| `feeds` | URL dipisah koma/baris | Penggabung beberapa URL RSS Feed |
| `limit` | `1` hingga `50` (Default: `6`) | Total jumlah artikel yang ditampilkan |
| `card_style` | `classic`, `modern`, `minimal`, `overlay`, `glass` | Model preset desain card |
| `layout` | `grid`, `list`, `horizontal`, `custom` | Mode tata letak tampilan |
| `columns` | `1` hingga `6` (Default: `3`) | Jumlah kolom dalam mode Grid |
| `image_ratio` | `16:9`, `4:3`, `1:1`, `3:2`, `auto` | Rasio pemotongan gambar |
| `image_hover_effect` | `zoom`, `brighten`, `none` | Animasi saat kursor diarahkan ke gambar |
| `show_search_bar` | `true` \| `false` | Menampilkan kolom pencarian live |
| `show_filter_tabs` | `true` \| `false` | Menampilkan tab filter kategori |
| `show_read_time` | `true` \| `false` | Menampilkan estimasi waktu baca |
| `show_social_share`| `true` \| `false` | Menampilkan ikon bagikan sosmed |
| `title_font_size` | `small`, `medium`, `large`, `xlarge` | Ukuran font preset judul artikel |
| `custom_title_font_size` | String CSS (misal: `18px`, `1.2rem`) | Ukuran kustom font judul |
| `excerpt_font_size` | `small`, `medium`, `large` | Ukuran font preset ringkasan |
| `custom_excerpt_font_size` | String CSS (misal: `14px`, `0.9rem`) | Ukuran kustom font ringkasan |
| `font_family` | `inherit`, `inter`, `roboto`, `poppins`, `playfair`, `monospace`, `custom` | Pilihan jenis font tipografi |
| `custom_font_family` | String CSS (misal: `'Montserrat', sans-serif`) | Jenis font kustom |
| `pagination_type` | `none`, `load_more`, `numeric` | Mode navigasi halaman |
| `items_per_page` | `1` hingga `30` (Default: `6`) | Jumlah artikel per halaman |
| `card_bg` | Kode Warna Hex / RGBA | Warna latar belakang card |
| `title_color` | Kode Warna Hex / RGBA | Warna teks judul artikel |
| `button_bg` | Kode Warna Hex / RGBA | Warna latar belakang tombol Read More |

---

## 🎨 Dukungan Theme Override

Anda dapat mengkustomisasi struktur HTML card secara bebas pada tema WordPress Anda!
Cukup salin file `templates/item.php` ke dalam folder tema Anda:
`wp-content/themes/TEMA-ANDA/universal-post-rss-loop/item.php`

---

## 👤 Informasi Penulis & Dukungan

- **Penulis**: Mujaddid Halimurrosyid
- **Halaman Penulis**: [https://ajidmujaddid.staff.telkomuniversity.ac.id/](https://ajidmujaddid.staff.telkomuniversity.ac.id/)
- **Repository GitHub**: [https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin](https://github.com/halimurrosyid/Universal-Post-RSS-Loop-WordPress-Plugin)
