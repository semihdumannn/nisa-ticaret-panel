<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class FuskaProductSeeder extends Seeder
{
    /**
     * Her ürün için:
     *   - sku        → Firebase id'den türetilir
     *   - slug       → name'den türetilir
     *   - unit       → 'piece' (Firebase'deki 'adet' karşılığı)
     *   - price      → 0.00 (Firebase'de fiyat girilmemiş)
     *   - categorySlug → ilgili kategoriye bağlanır (pivot)
     *   - images     → product_images tablosuna eklenir
     */
    private array $products = [
        // ─── PET ŞİŞE ────────────────────────────────────────────────────────
        [
            'sku'          => 'fuska-pet-02lt',
            'name'         => 'Fuska PET Şişe 0.2L',
            'description'  => 'Fuska doğal kaynak suyu 200ml. Minik ama taptaze! Tek yudumda doğal ferahlık, her an her yerde yanınızda.',
            'category'     => 'pet-sise',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778360732/01_PET_0.2LT_kpy0oi.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 266,
            'is_active'    => true,
            'is_featured'  => false,
        ],
        [
            'sku'          => 'fuska-pet-033lt',
            'name'         => 'Fuska PET Şişe 0.33L',
            'description'  => 'Fuska doğal kaynak suyu 330ml. Gün boyu size eşlik eden bir destek!',
            'category'     => 'pet-sise',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778360733/02_PET_0.33LT_ay2sw0.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 209,
            'is_active'    => true,
            'is_featured'  => false,
        ],
        [
            'sku'          => 'fuska-pet-05lt',
            'name'         => 'Fuska PET Şişe 0.5L',
            'description'  => 'Fuska doğal kaynak suyu 500ml. Pratik şişesiyle evde veya işte su ihtiyacınızı karşılar.',
            'category'     => 'pet-sise',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778360796/03_PET_0.5LT_tzgkaf.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 147,
            'is_active'    => true,
            'is_featured'  => true,
        ],
        [
            'sku'          => 'fuska-pet-1lt',
            'name'         => 'Fuska PET Şişe 1L',
            'description'  => 'Fuska doğal mineralli su 1 litre. Gün boyu size eşlik eden bir destek!',
            'category'     => 'pet-sise',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778360797/04_PET_1LT_iuzyb8.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 126,
            'is_active'    => true,
            'is_featured'  => false,
        ],
        [
            'sku'          => 'fuska-pet-15lt',
            'name'         => 'Fuska PET Şişe 1.5L',
            'description'  => 'Fuska doğal kaynak suyu 1.5 litre. Her yudumda doğal ferahlık!',
            'category'     => 'pet-sise',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778360798/05_PET_1.5LT_g7h3ag.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 84,
            'is_active'    => true,
            'is_featured'  => true,
        ],
        [
            'sku'          => 'fuska-pet-5lt',
            'name'         => 'Fuska PET Şişe 5L',
            'description'  => 'Fuska doğal kaynak suyu 5 litre. Aile boyu tazelik!',
            'category'     => 'pet-sise',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778360799/06_PET_5LT_yehhnn.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 80,
            'is_active'    => true,
            'is_featured'  => false,
        ],

        // ─── PREMİUM ─────────────────────────────────────────────────────────
        [
            'sku'          => 'fuska-premium-075lt',
            'name'         => 'Fuska Premium 0.75L',
            'description'  => 'Fuska Premium doğal kaynak suyu 750ml. Saflığın ve kalitenin buluşma noktası!',
            'category'     => 'premium',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361554/07_PREMIUM_0.75LT_ozpor4.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 174,
            'is_active'    => true,
            'is_featured'  => true,
        ],
        [
            'sku'          => 'fuska-premium-04lt',
            'name'         => 'Fuska Premium 0.4L',
            'description'  => 'Fuska Premium doğal kaynak suyu 400ml. Saflığın ve kalitenin buluşma noktası!',
            'category'     => 'premium',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361712/08_PREMIUM_0.4LT_yqsy4g.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 171,
            'is_active'    => true,
            'is_featured'  => false,
        ],

        // ─── PRESTİGE CAM ────────────────────────────────────────────────────
        [
            'sku'          => 'fuska-prestige-cam-033lt',
            'name'         => 'Fuska Prestige Cam 0.33L',
            'description'  => 'Fuska Prestige cam şişe 330ml. Etkinlikler için mükemmel şık bir dokunuş.',
            'category'     => 'prestige-cam',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361714/09_PRESTIGE_CAM_0.33LT_puk2tj.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 168,
            'is_active'    => true,
            'is_featured'  => true,
        ],
        [
            'sku'          => 'fuska-prestige-cam-075lt',
            'name'         => 'Fuska Prestige Cam 0.75L',
            'description'  => 'Fuska Prestige cam şişe 750ml. Etkinlikler için mükemmel şık bir dokunuş.',
            'category'     => 'prestige-cam',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361715/10_PRESTIGE_CAM_0.75LT_xberek.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 156,
            'is_active'    => true,
            'is_featured'  => false,
        ],

        // ─── DAMACANA ────────────────────────────────────────────────────────
        [
            'sku'          => 'fuska-cam-damacana-15lt',
            'name'         => 'Fuska Cam Damacana 15L',
            'description'  => 'Fuska doğal mineralli su cam damacana 15 litre. Hijyenik dolum ve güvenli ambalajıyla.',
            'category'     => 'damacana',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361718/11_CAM_DAMACANA_15LT_idpvuv.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 50,
            'is_active'    => true,
            'is_featured'  => false,
        ],
        [
            'sku'          => 'fuska-damacana-19lt',
            'name'         => 'Fuska Damacana 19L',
            'description'  => 'Fuska doğal mineralli su damacana 19 litre. Evinize ve iş yerinize sağlığı taşır.',
            'category'     => 'damacana',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361720/12_DAMACANA_19LT_cnujx4.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 50,
            'is_active'    => true,
            'is_featured'  => true,
        ],

        // ─── BARDAK SU ───────────────────────────────────────────────────────
        [
            'sku'          => 'fuska-bardak-02lt',
            'name'         => 'Fuska Bardak Su 0.2L',
            'description'  => 'Fuska doğal kaynak suyu bardak 200ml. Etkinliklerde her yudumda ferahlık sunar.',
            'category'     => 'bardak-su',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361556/13_BARDAK_SU_0.2LT_zzzuwd.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 70,
            'is_active'    => true,
            'is_featured'  => false,
        ],

        // ─── DİĞER ───────────────────────────────────────────────────────────
        [
            'sku'          => 'fuska-limonata-025lt',
            'name'         => 'Fuska Limonata 0.25L',
            'description'  => 'Fuska Limonata 250ml. Ferahlığın en doğal hali! Gerçek limon tadı ve ideal şeker dengesiyle.',
            'category'     => 'diger',
            'image_url'    => 'https://res.cloudinary.com/dm1cg87di/image/upload/v1778361557/14_LIMONATA_0.25LT_woc32u.png',
            'price'        => 0.00,
            'min_order_qty' => 1,
            'max_order_qty' => 66,
            'is_active'    => true,
            'is_featured'  => false,
        ],
    ];

    public function run(): void
    {
        // Kategori slug → id map'i önbellekle
        $categoryMap = Category::pluck('id', 'slug')->all();

        $created = 0;
        $updated = 0;

        foreach ($this->products as $data) {
            $slug = Str::slug($data['name']);

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name'          => $data['name'],
                    'slug'          => $slug,
                    'description'   => $data['description'],
                    'unit'          => 'piece',
                    'price'         => $data['price'],
                    'tax_rate'      => 20.00,
                    'min_order_qty' => $data['min_order_qty'],
                    'max_order_qty' => $data['max_order_qty'],
                    'is_active'     => $data['is_active'],
                    'is_featured'   => $data['is_featured'],
                    'deleted_at'    => null,
                ],
            );

            $wasRecentlyCreated = $product->wasRecentlyCreated;
            $wasRecentlyCreated ? $created++ : $updated++;

            // Kategori ilişkisi
            $categorySlug = $data['category'];
            if (isset($categoryMap[$categorySlug])) {
                $product->categories()->syncWithoutDetaching([$categoryMap[$categorySlug]]);
            } else {
                $this->command->warn("  ⚠ Kategori bulunamadı: '{$categorySlug}' ({$data['sku']})");
            }

            // Ürün görseli — zaten varsa tekrar ekleme
            $imageExists = $product->images()->where('image_url', $data['image_url'])->exists();
            if (! $imageExists) {
                // Mevcut primary'i kaldır, yeni olanı primary yap
                $product->images()->update(['is_primary' => false]);
                $product->images()->create([
                    'image_url'  => $data['image_url'],
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        $total = count($this->products);
        $this->command->info("✓ {$total} Fuska ürünü işlendi ({$created} yeni, {$updated} güncellendi).");
    }
}
