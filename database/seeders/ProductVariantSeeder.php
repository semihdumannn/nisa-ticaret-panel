<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductVariantSeeder extends Seeder
{
    // Slug → package_qty overrides for products without qty in the name
    private array $explicitQtys = [
        // Fuska tekil şişeler (koli miktarları)
        'fuska-pet-sise-0-2l'                         => 48,
        'fuska-pet-sise-0-33l'                        => 24,
        'fuska-pet-sise-0-5l'                         => 24,
        'fuska-pet-sise-1l'                           => 12,
        'fuska-pet-sise-1-5l'                         => 12,
        'fuska-pet-sise-5l'                           => 4,
        'fuska-premium-0-4l'                          => 12,
        'fuska-premium-0-75l'                         => 12,
        'fuska-prestige-cam-0-33l'                    => 24,
        'fuska-prestige-cam-0-75l'                    => 12,
        'fuska-cam-damacana-15l'                      => 1,
        'fuska-damacana-19l'                          => 1,
        'fuska-bardak-su-0-2l'                        => 100,
        'fuska-limonata-0-25l'                        => 24,
        // Su - damacana
        'kardelen-su-19lt'                            => 1,
        // Bardak ürünler
        'kardan-karadut-bardak-250cc'                 => 24,
        'kardan-limonata-bardak-250cc'                => 24,
        // Soda
        'kinik-sade-soda'                             => 24,
        // Ketçap & Mayonez
        'pinar-mayonez-servis-700gr'                  => 6,
        'pinar-ketsap-servis-800gr'                   => 6,
        // Büyük kaplar
        'tarihi-odunpazari-karadutlu-icecek-3lt'      => 4,
        'tarihi-odunpazari-limonata-3lt'              => 4,
        // Gorilla tekil ürünler
        'gorilla-energy-karpuz-mango-500ml'           => 12,
        'gorilla-energy-mango-hindistan-cevizi-500ml' => 12,
        'gorilla-energy-original-500ml'               => 12,
        'gorilla-energy-original-250ml'               => 12,
        // Poli toz içecek (ambalaj miktarı yok isimde)
        'poli-sutlu-muz-250gr'                        => 24,
        'poli-nane-limon-250gr'                       => 24,
        'poli-suka-kakaolu-aromali-toz-icecek'        => 24,
        'poli-tarcin-300gr'                           => 24,
        'poli-kusburnu-aromali-toz-icecek-300gr'      => 24,
        'poli-kivi-300gr'                             => 24,
        'poli-portakal-aromali-toz-icecek-300gr'      => 24,
    ];

    public function run(): void
    {
        // Önce mevcut koli varyantları sil
        ProductVariant::whereRaw("attributes->>'is_koli' = 'true'")->delete();

        $products = Product::active()->get();
        $created  = 0;
        $skipped  = 0;

        foreach ($products as $product) {
            $packageQty = $this->resolvePackageQty($product->name, $product->slug);

            if ($packageQty === null) {
                $skipped++;
                continue;
            }

            $sku = $this->uniqueVariantSku($product->sku);

            ProductVariant::create([
                'product_id'       => $product->id,
                'sku'              => $sku,
                'name'             => 'Koli',
                'attributes'       => [
                    'is_koli'     => true,
                    'package_qty' => $packageQty,
                    'unit'        => 'koli',
                    'sale_price'  => null,
                ],
                'price_adjustment' => 0,
                'stock'            => 100,
                'is_active'        => true,
            ]);

            $created++;
        }

        $this->command->info("✅ {$created} ürüne koli varyantı eklendi. {$skipped} ürün atlandı.");
    }

    private function resolvePackageQty(string $name, string $slug): ?int
    {
        // Açık override varsa kullan
        if (isset($this->explicitQtys[$slug])) {
            return $this->explicitQtys[$slug];
        }

        // "2×12" veya "2x6" gibi çarpım kalıpları: Sultan suyu gibi
        if (preg_match('/(\d+)\s*[×x]\s*(\d+)/u', $name, $m)) {
            return (int) $m[1] * (int) $m[2];
        }

        // "24lü", "12li", "20li", "6lı", "4lü", "40lı", "192li" vb.
        if (preg_match('/(\d+)\s*l[üuıiı]/iu', $name, $m)) {
            return (int) $m[1];
        }

        // Slug'dan son sayıyı al (yedek)
        if (preg_match('/(\d+)$/', $slug, $m)) {
            return (int) $m[1];
        }

        // Eşleşme yok — varsayılan 6
        return 6;
    }

    private function uniqueVariantSku(string $productSku): string
    {
        $base = Str::limit($productSku, 44, '') . '-KOLI';
        $sku  = $base;
        $i    = 1;

        while (ProductVariant::where('sku', $sku)->exists()) {
            $sku = Str::limit($productSku, 41, '') . '-K' . $i++;
        }

        return $sku;
    }
}
