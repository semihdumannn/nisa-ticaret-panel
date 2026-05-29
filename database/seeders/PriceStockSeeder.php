<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PriceStockSeeder extends Seeder
{
    // Ürün adı → fiyat (vanguardgida.com.tr)
    private array $prices = [
        // ─── AYRAN ───────────────────────────────────────────────────────────
        'Havran Çiftlik Cam 245ml 20li'       => 575.00,
        'Havran Çiftlik Pet 245ml 20li'        => 475.00,
        'Havran Çiftlik 280ml 12li'            => 150.00,
        'Havran Çiftlik 180ml 20li'            => 150.00,
        'Havran Çiftlik 170ml 20li'            => 135.00,
        'Havran Çiftlik Cam Lt 6lı'            => 500.00,

        // ─── NOODLE ──────────────────────────────────────────────────────────
        'İndomie Bardak Köri Aromalı Noodle 24lü'  => 818.10,
        'İndomie Bardak Tavuklu Noodle 24lü'        => 818.10,
        'İndomie Jumbo Köri Aromalı Noodle 40lı'    => 808.00,
        'İndomie Jumbo Tavuk Aromalı Noodle 40lı'   => 808.00,
        'İndomie Paket Tavuklu Noodle 40lı'         => 642.36,
        'İndomie Paket Spesyal Noodle 40lı'         => 642.36,
        'İndomie Paket Körili Noodle 40lı'          => 642.36,
        'İndomie Gurme Soya Soslu 40lı'             => 868.60,

        // ─── KOLA & GAZLI İÇECEK ─────────────────────────────────────────────
        'Sarıyer Kola 2.5lt 6lı'               => 359.56,
        'Sarıyer Portakal 2.5lt 6lı'           => 359.56,
        'Sarıyer Kola 1lt 12li'                => 419.34,
        'Sarıyer Portakal 1lt 12li'            => 419.34,
        'Sarıyer Kola Kutu 330ml 24lü'         => 597.00,
        'Sarıyer Portakal Kutu 330ml 24lü'     => 597.00,
        'Sarıyer Gazoz Kutu 330ml 24lü'        => 597.00,
        'Sarıyer Kola Cam Şişe 200ml 24lü'     => 494.29,
        'Sarıyer Portakal Cam Şişe 200ml 24lü' => 494.29,
        'Sarıyer Gazoz Cam Şişe 200ml 24lü'    => 494.29,
        'Sarıyer Kola Pet Şişe 250ml 24lü'     => 300.00,
        'Black Bruin 1lt Pet 12li'             => 459.00,
        'Black Bruin 500ml Kutu 24lü'          => 781.66,
        'Max Fly Cola 1lt 12li'                => 360.00,
        'Max Fly Cola 330ml 24lü'              => 534.34,
        'Max Fly Cola 250ml 24lü'              => 465.17,
        'Max Fly Cola Şekersiz 330ml 24lü'     => 534.34,
        'Max Fly Portakal 250ml 24lü'          => 465.17,
        'Max Fly Limon Lime 250ml 24lü'        => 465.17,
        'Cola Turka 2500ml 6lı'                => 390.00,
        'Cola Turka 1000ml 12li'               => 530.00,
        'Cola Turka 330ml 24lü'                => 660.00,
        'Cola Turka Cam Şişe 200ml 24lü'       => 475.00,
        'Niğde Gazozu Cam Şişe 250ml 24lü'    => 510.00,
        'Kınık Bursa Gazozu 24lü'              => 350.00,
        'Kınık Bursa Gazozu Portakallı 24lü'   => 350.00,
        'Çamlıca Sade Cam Şişe 200ml 24lü'     => 400.00,
        'Coca Cola 330ml Kutu 24lü'             => 1020.00,
        'Coca Cola 200ml Kutu 24lü'             => 799.00,
        'Coca Cola 200ml Cam İadesiz Şişe 24lü' => 699.00,
        'Coca Cola Dönüşümlü Şişe 200ml 24lü'  => 499.00,
        'Coca Cola 2.5lt 6lı'                  => 459.00,
        'Coca Cola 1lt 12li'                   => 649.00,
        'Fanta 330ml Kutu 24lü'                => 1020.00,
        'Fanta 200ml Kutu 12li'                => 780.00,
        'Fanta Dönüşümlü Şişe 200ml 24lü'      => 499.00,
        'Fanta 2.5lt 6lı'                      => 435.60,
        'Fanta 1lt 12li'                       => 624.00,
        'Pepsi 200ml İadesiz Şişe 24lü'        => 540.00,

        // ─── SODA ────────────────────────────────────────────────────────────
        'Beypazarı Sade Soda 24lü'                     => 225.00,
        'Beypazarı Limonlu Soda 24lü'                  => 266.00,
        'Beypazarı Elmalı Soda 24lü'                   => 266.00,
        'Beypazarı Karadut Frenk Üzümlü Soda 24lü'    => 276.00,
        'Beypazarı Mango Ananas Soda 24lü'             => 276.00,
        'Beypazarı Narlı Soda 24lü'                    => 276.00,
        "Beypazarı Co'ala Lime Bah 24lü"               => 285.00,
        'Sultan Sade Soda 24lü'                        => 170.00,
        'Kınık Sade Soda'                              => 193.00,

        // ─── MEYVE SUYU ──────────────────────────────────────────────────────
        'Juss Karışık Kutu 250ml 24lü'         => 551.00,
        'Juss Şeftali Kutu 250ml 24lü'         => 551.00,
        'Juss Kayısı Kutu 250ml 24lü'          => 551.00,
        'Juss Vişne Kutu 250ml 24lü'           => 551.00,
        'Juss Vişne Tetra 200ml 27li'          => 310.00,
        'Juss Karışık Tetra 200ml 27li'        => 310.00,
        'Juss Kayısı Tetra 200ml 27li'         => 310.00,
        'Juss Şeftali Tetra 200ml 27li'        => 310.00,
        'Juss Cam Şişe Şeftali Nektarı 24lü'  => 530.00,
        'Juss Cam Şişe Vişne Nektarı 24lü'    => 530.00,
        'Juss Cam Şişe Karışık Nektarı 24lü'  => 530.00,
        'Juss Cam Şişe Kayısı Nektarı 24lü'   => 530.00,
        'Juss Şeftali Meyve İçeceği 12li'     => 529.00,
        'Juss Kayısı Meyve İçeceği 12li'      => 529.00,
        'Juss Karışık Meyve İçeceği 12li'     => 529.00,
        'Juss Vişne Meyve İçeceği 12li'       => 529.00,
        'Meysu Slim Kayısı Nektarı 27li'      => 325.00,
        'Meysu Slim Şeftali Nektarı 27li'     => 325.00,
        'Meysu Slim Karışık Nektarı 27li'     => 325.00,
        'Meysu Slim Vişne Meyve İçeceği 27li' => 325.00,
        'Kardan Karadut Bardak 250cc'         => 180.00,
        'Kardan Limonata Bardak 250cc'        => 180.00,
        'Tarihi Odunpazarı Karadutlu İçecek 3lt' => 44.00,
        'Tarihi Odunpazarı Limonata 3lt'      => 44.00,
        'Kardelen Limonata 250ml 12li'        => 168.00,
        'Kardelen Karadut İçeceği 250ml 12li' => 168.00,
        'Kardelen Çilek İçeceği 250ml 12li'   => 168.00,
        'Pınar Limonata 330ml 12li'           => 165.00,
        'Pınar Limonata 1lt 12li'             => 282.00,
        'Pınar Cam Şişe Limonata 250ml 24lü'  => 480.00,

        // ─── SU ──────────────────────────────────────────────────────────────
        'Kardelen Su 330ml Pet 24lü'  => 110.00,
        'Kardelen Su 500ml 24lü'      => 110.00,
        'Kardelen Su 1lt Pet 12li'    => 110.00,
        'Kardelen Su 1.5lt Pet 12li'  => 110.00,
        'Kardelen Su 5lt Pet 4lü'     => 110.00,
        'Kardelen Su 19lt'            => 110.00,
        'Kardelen Su 200ml Bardak 72li' => 125.00,
        'Sultan Doğal Kaynak Suyu 0.5lt 2×12' => 120.00,
        'Sultan Doğal Kaynak Suyu 1.5lt 2×6'  => 120.00,
        'Sultan Doğal Kaynak Suyu 5lt 4lü'    => 120.00,
        'Pınar Sporcu Kapaklı Su 750ml 12li'  => 180.00,

        // ─── ENERJİ İÇECEĞİ ──────────────────────────────────────────────────
        'Max Fly Enerji İçeceği Classic 250ml 24lü' => 543.31,
        'Max Fly Enerji İçeceği Mojito 250ml 24lü'  => 543.31,
        'Max Fly Enerji İçeceği Sirius 250ml 24lü'  => 543.31,
        'Max Fly Enerji İçeceği Vega 250ml 24lü'    => 543.31,
        'Max Fly Enerji İçeceği Nova 250ml 24lü'    => 543.31,
        'Max Fly Enerji İçeceği Cosmos 250ml 24lü'  => 543.31,
        'Gorilla Ultimate Energy Original 250ml 12li' => 384.00,
        'Gorilla Ultimate Energy Original 500ml 12li' => 528.00,
        'Gorilla Energy Karma Koli 4 Çeşit 24lü 500ml' => 1056.00,
        'Gorilla Energy Karma Koli 4 Çeşit 24lü 250ml' => 768.00,
        'Gorilla Energy Karpuz Mango 500ml'          => 528.00,
        'Gorilla Energy Mango Hindistan Cevizi 250ml 12li' => 384.00,
        'Gorilla Energy Mango Hindistan Cevizi 500ml' => 528.00,
        'Gorilla Energy Original 500ml'              => 528.00,
        'Gorilla Energy Original 250ml'              => 768.00,
        'Red Bull 250ml 24lü'                        => 1425.00,
        'Hot Line Enerji 250ml 24lü'                 => 400.00,
        'Hot Line Enerji 500ml 24lü'                 => 575.00,
        'Hot Line Enerji Pet 1lt 12li'               => 425.00,

        // ─── ŞALGAM ──────────────────────────────────────────────────────────
        'Doğanay 300ml Acılı Şalgam 24lü'   => 439.00,
        'Doğanay 300ml Acısız Şalgam 24lü'  => 439.00,
        'Doğanay Tatlı Şalgam 1lt 12li'     => 510.00,
        'As01 300ml Acısız Şalgam 24lü'     => 375.00,
        'As01 300ml Acılı Şalgam 24lü'      => 375.00,

        // ─── KAHVE ───────────────────────────────────────────────────────────
        'Max Brew Latte Soğuk Kahve 250ml 12li'    => 409.33,
        'Max Brew Caramel Soğuk Kahve 250ml 12li'  => 409.33,
        'Max Brew Mocha Soğuk Kahve 250ml 12li'    => 409.33,
        'Nescafe 3+1 Arada 10gr 56lı'              => 450.00,
        'Nescafe 3+1 Süt Köpük 17.4gr 48li'        => 450.00,
        'Nescafe 2+1 Arada 10gr 56lı'              => 450.00,
        'My Coffee Latte 250ml 24lü'               => 720.00,
        'My Coffee Caramel 250ml 24lü'             => 720.00,
        'Kahve Dünyası Orta Kavrulmuş 100gr 12li'  => 65.00,
        "Kahve Dünyası 3'ü 1 Arada 40lı Paket"    => 240.00,
        "Kahve Dünyası 2'si 1 Arada 40lı Paket"   => 240.00,
        "KD 2'si 1 Arada 192li"                   => 943.00,
        "KD 3'ü 1 Arada 192li"                    => 943.00,

        // ─── KETÇAP & MAYONEZ ────────────────────────────────────────────────
        'Pınar Mayonez Servis 700gr' => 75.00,
        'Pınar Ketçap Servis 800gr'  => 64.00,

        // ─── SOĞUK ÇAY ───────────────────────────────────────────────────────
        'Didi Soğuk Çay Şeftali 250ml 24lü'   => 407.80,
        'Didi Soğuk Çay Çilek 250ml 24lü'     => 407.80,
        'Didi Soğuk Çay Limon 250ml 24lü'     => 407.80,
        'Didi Soğuk Çay Bergamot 250ml 24lü'  => 407.80,
        'Didi Soğuk Çay Şeftali 330ml 24lü'   => 461.46,
        'Didi Soğuk Çay Limon 330ml 24lü'     => 461.46,
        'Didi Soğuk Çay Bergamot 330ml 24lü'  => 461.46,
        'Didi Soğuk Çay Bergamot 500ml 24lü'  => 587.93,
        'Didi Soğuk Çay Limon 500ml 24lü'     => 587.92,
        'Didi Soğuk Çay Şeftali 500ml 24lü'   => 587.92,
        'Didi Soğuk Çay Şeftali 1lt 12li'     => 395.00,
        'Didi Soğuk Çay Limon 1lt 12li'       => 395.00,
        'Didi Soğuk Çay Bergamot 1lt 12li'    => 395.00,
        'Didi Soğuk Çay Şeftali 1.5lt 12li'   => 495.17,
        'Didi Soğuk Çay Limon 1.5lt 12li'     => 495.17,
        'Didi Soğuk Çay Bergamot 1.5lt 12li'  => 495.17,
        'Didi Soğuk Çay Bergamot 2.5lt 6lı'   => 350.00,
        'Didi Soğuk Çay Limon 2.5lt 6lı'      => 350.00,
        'Didi Soğuk Çay Şeftali 2.5lt 6lı'    => 350.00,

        // ─── TOZ İÇECEK ──────────────────────────────────────────────────────
        'Poli Sütlü Muz 250gr'                 => 45.00,
        'Poli Salep 250gr 24lü'               => 45.00,
        'Poli Nane Limon 250gr'               => 45.00,
        'Poli Süka Kakaolu Aromalı Toz İçecek' => 49.00,
        'Poli Tarçın 300gr'                   => 45.00,
        'Poli Kuşburnu Aromalı Toz İçecek 300gr' => 45.00,
        'Poli Kekik 300gr 24lü'               => 45.00,
        'Poli Kivi 300gr'                     => 45.00,
        'Poli Portakal Aromalı Toz İçecek 300gr' => 45.00,
    ];

    public function run(): void
    {
        // ── 1. Fiyat güncellemesi ─────────────────────────────────────────────
        $priceUpdated = 0;
        $priceSkipped = 0;

        foreach ($this->prices as $name => $price) {
            $slug = Str::slug($name);
            $rows = DB::table('products')->where('slug', $slug)->update(['price' => $price]);

            if ($rows > 0) {
                $priceUpdated++;
            } else {
                // SKU 50-karakterde kesilmiş olabilir, sku ile de dene
                $sku  = Str::limit($slug, 50, '');
                $rows = DB::table('products')->where('sku', $sku)->update(['price' => $price]);
                $rows > 0 ? $priceUpdated++ : $priceSkipped++;
            }
        }

        $this->command->info("✅ Fiyat: {$priceUpdated} ürün güncellendi, {$priceSkipped} eşleşmedi.");

        // ── 2. Stok güncellemesi — mevcut kayıtları 1000 yap ─────────────────
        $inventoryRows = DB::table('inventory')->update(['quantity' => 1000]);
        $this->command->info("✅ Stok: {$inventoryRows} mevcut envanter kaydı → 1000 yapıldı.");

        // ── 3. Stok kaydı olmayan ürünler için kayıt oluştur ─────────────────
        $warehouse = Warehouse::where('is_active', true)->first();

        if (! $warehouse) {
            $this->command->warn('⚠ Aktif depo bulunamadı — stok kaydı oluşturulamadı.');
            return;
        }

        $created = 0;
        $products = Product::whereDoesntHave('inventories')->get();

        foreach ($products as $product) {
            DB::table('inventory')->insert([
                'product_id'        => $product->id,
                'variant_id'        => null,
                'warehouse_id'      => $warehouse->id,
                'quantity'          => 1000,
                'reserved_quantity' => 0,
                'created_at'        => now(),
                'updated_at'        => now(),
            ]);
            $created++;
        }

        if ($created > 0) {
            $this->command->info("✅ Stok: {$created} ürün için yeni envanter kaydı oluşturuldu.");
        }
    }
}
