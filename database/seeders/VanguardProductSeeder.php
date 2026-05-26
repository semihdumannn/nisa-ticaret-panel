<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VanguardProductSeeder extends Seeder
{
    // [name, brand, category, description, image_url, unit]
    // unit: 'pack' = koli/paket, 'piece' = tek adet
    private array $products = [

        // ─── AYRAN ───────────────────────────────────────────────────────────
        ['Havran Çiftlik Cam 245ml 20li',       'Havran Çiftlik', 'Ayran',              'Cam şişede geleneksel Türk ayranı, 245ml × 20 adet.',                  '', 'pack'],
        ['Havran Çiftlik Pet 245ml 20li',        'Havran Çiftlik', 'Ayran',              'PET şişede ayran, 245ml × 20 adet.',                                  '', 'pack'],
        ['Havran Çiftlik 280ml 12li',            'Havran Çiftlik', 'Ayran',              'Küçük boy cam şişede ayran, 280ml × 12 adet.',                        '', 'pack'],
        ['Havran Çiftlik 180ml 20li',            'Havran Çiftlik', 'Ayran',              'Mini boyda ayran, 180ml × 20 adet.',                                  '', 'pack'],
        ['Havran Çiftlik 170ml 20li',            'Havran Çiftlik', 'Ayran',              'Mini boyda ayran, 170ml × 20 adet.',                                  '', 'pack'],
        ['Havran Çiftlik Cam Lt 6lı',            'Havran Çiftlik', 'Ayran',              'Cam şişede 1 litrelik aile boy ayran, 6 adet.',                       '', 'pack'],

        // ─── NOODLE ──────────────────────────────────────────────────────────
        ['İndomie Bardak Köri Aromalı Noodle 24lü',  'İndomie', 'Noodle', 'Körili aromalı bardak instant noodle, 24 adet.',            '', 'pack'],
        ['İndomie Bardak Tavuklu Noodle 24lü',        'İndomie', 'Noodle', 'Tavuk aromalı bardak instant noodle, 24 adet.',             '', 'pack'],
        ['İndomie Jumbo Köri Aromalı Noodle 40lı',    'İndomie', 'Noodle', 'Jumbo boy körili paket noodle, 40 adet.',                   '', 'pack'],
        ['İndomie Jumbo Tavuk Aromalı Noodle 40lı',   'İndomie', 'Noodle', 'Jumbo boy tavuk aromalı paket noodle, 40 adet.',            '', 'pack'],
        ['İndomie Paket Tavuklu Noodle 40lı',         'İndomie', 'Noodle', 'Standart paket tavuk aromalı noodle, 40 adet.',             '', 'pack'],
        ['İndomie Paket Spesyal Noodle 40lı',         'İndomie', 'Noodle', 'Özel soslu spesyal paket noodle, 40 adet.',                 '', 'pack'],
        ['İndomie Paket Körili Noodle 40lı',          'İndomie', 'Noodle', 'Körili paket noodle, 40 adet.',                            '', 'pack'],
        ['İndomie Gurme Soya Soslu 40lı',             'İndomie', 'Noodle', 'Soya soslu gurme paket noodle, 40 adet.',                  '', 'pack'],

        // ─── KOLA & GAZLI İÇECEK ─────────────────────────────────────────────
        ['Sarıyer Kola 2.5lt 6lı',               'Sarıyer',    'Kola & Gazlı İçecek', 'Sarıyer marka kola, 2.5L PET × 6 adet koli.',
            'https://vanguardgida.com.tr/images/EK42V5OydbmXfiNLJiWohVnOJcO4j_dQsXd2j6ra7V0/_small/ks-prod/images/shop/6735a5592b654/product/69f5a779a43c5/Saryer-Kola-2-5-lt-Mockup-2.webp', 'pack'],
        ['Sarıyer Portakal 2.5lt 6lı',           'Sarıyer',    'Kola & Gazlı İçecek', 'Portakal aromalı gazlı içecek, 2.5L × 6 adet.',
            'https://vanguardgida.com.tr/images/FvpeCH5jPDwEez9ntNcnfOAZZ1ypUwCavFcpPDPj4As/_small/ks-prod/images/shop/6735a5592b654/product/69f5a8e11cabf/sariyer-portakal-2-5-L_1.webp', 'pack'],
        ['Sarıyer Kola 1lt 12li',                'Sarıyer',    'Kola & Gazlı İçecek', 'Sarıyer kola 1L PET şişe, 12 adet koli.',
            'https://vanguardgida.com.tr/images/zQ5f5FxhX66FUarLTxED1GCLAE131V36j6F6sj-CS6w/_small/ks-prod/images/shop/6735a5592b654/product/69f5a7cce92f4/Saryer-Kola-1-lt-Mockup-3.webp', 'pack'],
        ['Sarıyer Portakal 1lt 12li',            'Sarıyer',    'Kola & Gazlı İçecek', 'Portakal aromalı gazlı içecek 1L × 12 adet.',
            'https://vanguardgida.com.tr/images/-_soHcDWAQul9p8RVWVHeshC4tqef-JYyPxsfJ4z12Y/_small/ks-prod/images/shop/6735a5592b654/product/69f5a7f6d7b45/sariyer-portakal-1-L-1.webp', 'pack'],
        ['Sarıyer Kola Kutu 330ml 24lü',         'Sarıyer',    'Kola & Gazlı İçecek', 'Sarıyer kola kutu 330ml × 24 adet.',
            'https://vanguardgida.com.tr/images/E7K-HAkpOVwTXmMk4GwItxsu1FxRCTmVxQfyN31mITc/_small/ks-prod/images/shop/6735a5592b654/product/69f5a816e8536/Saryer-Kola-New-330.webp', 'pack'],
        ['Sarıyer Portakal Kutu 330ml 24lü',     'Sarıyer',    'Kola & Gazlı İçecek', 'Portakal aromalı gazlı içecek kutu 330ml × 24 adet.',
            'https://vanguardgida.com.tr/images/fORHfH3NHpO4lybWCDlhgidcFOz4l6mQ4Qz9HW2e8dw/_small/ks-prod/images/shop/6735a5592b654/product/69f5a84df2103/Saryer-Portakal-330ml.webp', 'pack'],
        ['Sarıyer Gazoz Kutu 330ml 24lü',        'Sarıyer',    'Kola & Gazlı İçecek', 'Gazoz aromalı kutu içecek 330ml × 24 adet.',
            'https://vanguardgida.com.tr/images/OFZjqtEbNJoVSh4JEzvYwDWwzT2DTppeoC6GnJCPt1k/_small/ks-prod/images/shop/6735a5592b654/product/69f5a88ed8606/Saryer-Gazoz-330ml.webp', 'pack'],
        ['Sarıyer Kola Cam Şişe 200ml 24lü',     'Sarıyer',    'Kola & Gazlı İçecek', 'Cam şişede Sarıyer kola 200ml × 24 adet.',
            'https://vanguardgida.com.tr/images/e02HXvtaB8oImc9oaR-MFmqE3LG-sr1gzvYLgTxOXR0/_small/ks-prod/images/shop/6735a5592b654/product/69f5a902758a3/saryer-kola-250-ml-1.webp', 'pack'],
        ['Sarıyer Portakal Cam Şişe 200ml 24lü', 'Sarıyer',    'Kola & Gazlı İçecek', 'Cam şişede portakal aromalı gazlı içecek 200ml × 24 adet.',
            'https://vanguardgida.com.tr/images/i-l0z44LEFeUaxwj8ep-v_4ybKR3B0xGEjjJo0F7mwM/_small/ks-prod/images/shop/6735a5592b654/product/69f5a9493666d/sariyer-portakal-250ml-cam.webp', 'pack'],
        ['Sarıyer Gazoz Cam Şişe 200ml 24lü',    'Sarıyer',    'Kola & Gazlı İçecek', 'Cam şişede gazoz 200ml × 24 adet.',
            'https://vanguardgida.com.tr/images/q5Yw1zwcI8xQUiygpJoKqt5yWiNDalD_Y6k1UIUzdCw/_small/ks-prod/images/shop/6735a5592b654/product/69f5a9e692114/saryer-ra-250-ml-1.webp', 'pack'],
        ['Sarıyer Kola Pet Şişe 250ml 24lü',     'Sarıyer',    'Kola & Gazlı İçecek', 'PET şişede Sarıyer kola 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/9etmHEbPs3Uy1aM_3nohCK9QnQChM4zvIJpkpSwKSyg/_small/ks-prod/images/shop/6735a5592b654/product/69f5aa165726e/sariyer-kola-250-ml.webp', 'pack'],
        ['Black Bruin 1lt Pet 12li',             'Black Bruin','Kola & Gazlı İçecek', 'Black Bruin gazlı içecek 1L PET × 12 adet.',
            'https://vanguardgida.com.tr/images/P7IYCaKANB6qNsdS-V5yXQulDcVDV4sULppLOYVEYX0/_small/ks-prod/images/shop/6735a5592b654/product/69f5aae746992/ORIGINAL-LTRE.webp', 'pack'],
        ['Black Bruin 500ml Kutu 24lü',          'Black Bruin','Kola & Gazlı İçecek', 'Black Bruin gazlı içecek kutu 500ml × 24 adet.',
            'https://vanguardgida.com.tr/images/-tjB5bkfhpyIAQJADvY_xKXp3lDI8O0MARX5zgrO0ng/_small/ks-prod/images/shop/6735a5592b654/product/6a065e4fc7551/original-5001.webp', 'pack'],
        ['Max Fly Cola 1lt 12li',                'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly kola 1L × 12 adet koli.',                                     '', 'pack'],
        ['Max Fly Cola 330ml 24lü',              'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly kola kutu 330ml × 24 adet.',                                  '', 'pack'],
        ['Max Fly Cola 250ml 24lü',              'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly kola kutu 250ml × 24 adet.',                                  '', 'pack'],
        ['Max Fly Cola Şekersiz 330ml 24lü',     'Max Fly',    'Kola & Gazlı İçecek', 'Şekersiz Max Fly kola kutu 330ml × 24 adet.',                         '', 'pack'],
        ['Max Fly Portakal 250ml 24lü',          'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly portakal aromalı gazlı içecek 250ml × 24 adet.',              '', 'pack'],
        ['Max Fly Limon Lime 250ml 24lü',        'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly limon ve misket limonu aromalı gazlı içecek 250ml × 24 adet.','', 'pack'],
        ['Cola Turka 2500ml 6lı',                'Cola Turka', 'Kola & Gazlı İçecek', 'Cola Turka büyük boy PET 2.5L × 6 adet.',
            'https://vanguardgida.com.tr/images/osuGVXPkIt5rBNPDZV-6u1lx1SHAENmQWImfBNz2WrY/_small/ks-prod/images/shop/6735a5592b654/product/676093cf57b2b/0duU50qtvFb1LqQ1xkMFdt6lsvDg0Pq8XdwrbEUc.png', 'pack'],
        ['Cola Turka 1000ml 12li',               'Cola Turka', 'Kola & Gazlı İçecek', 'Cola Turka PET şişe 1L × 12 adet.',
            'https://vanguardgida.com.tr/images/sgm3tjistJpcE1dW0wvTDCshBaBndE4c2VDXzFnK-BY/_small/ks-prod/images/shop/6735a5592b654/product/676093cf4c4e9/4wMNU2HoH8uEXlOS2BcSgAYFbOvdA7r8cb5eQGqK.png', 'pack'],
        ['Cola Turka 330ml 24lü',                'Cola Turka', 'Kola & Gazlı İçecek', 'Cola Turka kutu 330ml × 24 adet.',
            'https://vanguardgida.com.tr/images/uPa9azcHfGSKil3Ho9dCL-_H3XKNGP3Cqxd7dKZ9QkU/_small/ks-prod/images/shop/6735a5592b654/product/676093cf69026/dJNsURd0yk3BEfQWKe5xGwlnM0DB2S9jqKPckAag.png', 'pack'],
        ['Cola Turka Cam Şişe 200ml 24lü',       'Cola Turka', 'Kola & Gazlı İçecek', 'Cola Turka efsane cam şişe 200ml × 24 adet.',
            'https://vanguardgida.com.tr/images/mLLq9AK3W7HZzTY5HjPhGas_Cg_Sqi1C7yFhdi9HUI0/_small/ks-prod/images/shop/6735a5592b654/product/676093cf5d4cc/NtcOdqbcNa41tS5tlR5HHaGgD7TEzS9Qj7TMwi3.png', 'pack'],
        ['Niğde Gazozu Cam Şişe 250ml 24lü',    'Niğde Gazozu','Kola & Gazlı İçecek','Niğde gazozu cam şişe 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/ZpseXfqN6YfNz2hnRpRhEuxN8vOha93TFidHpOoFWsc/_small/ks-prod/images/shop/6735a5592b654/product/68d1cf9483875/PlQ8E6osTb2k8KZonjtna2SySe5ddVcVMbIATeZH.png', 'pack'],
        ['Kınık Bursa Gazozu 24lü',              'Kınık',      'Kola & Gazlı İçecek', 'Kınık Bursa gazozu, 24 adet koli.',
            'https://vanguardgida.com.tr/images/PWNxUhCeZka2LqrisbSGl1ahL3ou1T6KdT0ChlVDCtQ/_small/ks-prod/images/shop/6735a5592b654/product/68bfc5421df8b/Np5tWdNw7CJXbqkGLB9N4LZMJ2lZikp8IEPVmU6y.png', 'pack'],
        ['Kınık Bursa Gazozu Portakallı 24lü',   'Kınık',      'Kola & Gazlı İçecek', 'Portakal aromalı Kınık Bursa gazozu, 24 adet.',
            'https://vanguardgida.com.tr/images/rMCX5ynvfGuUfN-asTsNSSxFTJLue93nB9TPn9t7pCk/_small/ks-prod/images/shop/6735a5592b654/product/68bfc53fd4b49/7LKM6G78yPHUkNT88ZqYBB5Fj0kHL0iQjU1ewi20.png', 'pack'],
        ['Çamlıca Sade Cam Şişe 200ml 24lü',     'Çamlıca',    'Kola & Gazlı İçecek', 'Çamlıca sade gazoz cam şişe 200ml × 24 adet.',
            'https://vanguardgida.com.tr/images/6jNURHU_yyuwcX1CdQx615K1goEz45UTqC6KRyT1WMM/_small/ks-prod/images/shop/6735a5592b654/product/676093cfaf6cc/HyjBPCTaFjc6u6XKIvzLIKHeH3ZPAVOIQ53cVtqZ.png', 'pack'],
        ['Coca Cola 330ml Kutu 24lü',             'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola kutu 330ml × 24 adet.',                                     '', 'pack'],
        ['Coca Cola 200ml Kutu 24lü',             'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola mini kutu 200ml × 24 adet.',                                '', 'pack'],
        ['Coca Cola 200ml Cam İadesiz Şişe 24lü', 'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola cam iadesiz şişe 200ml × 24 adet.',                         '', 'pack'],
        ['Coca Cola Dönüşümlü Şişe 200ml 24lü',  'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola dönüşümlü cam şişe 200ml × 24 adet.',                       '', 'pack'],
        ['Coca Cola 2.5lt 6lı',                  'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola büyük boy PET 2.5L × 6 adet.',                              '', 'pack'],
        ['Coca Cola 1lt 12li',                   'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola PET şişe 1L × 12 adet.',                                    '', 'pack'],
        ['Fanta 330ml Kutu 24lü',                'Fanta',      'Kola & Gazlı İçecek', 'Fanta portakal aromalı kutu 330ml × 24 adet.',                         '', 'pack'],
        ['Fanta 200ml Kutu 12li',                'Fanta',      'Kola & Gazlı İçecek', 'Fanta mini kutu 200ml × 12 adet.',                                     '', 'pack'],
        ['Fanta Dönüşümlü Şişe 200ml 24lü',      'Fanta',      'Kola & Gazlı İçecek', 'Fanta dönüşümlü cam şişe 200ml × 24 adet.',                            '', 'pack'],
        ['Fanta 2.5lt 6lı',                      'Fanta',      'Kola & Gazlı İçecek', 'Fanta büyük boy PET 2.5L × 6 adet.',                                  '', 'pack'],
        ['Fanta 1lt 12li',                       'Fanta',      'Kola & Gazlı İçecek', 'Fanta PET şişe 1L × 12 adet.',                                        '', 'pack'],
        ['Pepsi 200ml İadesiz Şişe 24lü',        'Pepsi',      'Kola & Gazlı İçecek', 'Pepsi cam iadesiz şişe 200ml × 24 adet.',                              '', 'pack'],

        // ─── SODA ────────────────────────────────────────────────────────────
        ['Beypazarı Sade Soda 24lü',                     'Beypazarı','Soda','Sade Beypazarı maden suyu, 24 adet koli.',                         '', 'pack'],
        ['Beypazarı Limonlu Soda 24lü',                  'Beypazarı','Soda','Limon aromalı Beypazarı sodası, 24 adet.',                         '', 'pack'],
        ['Beypazarı Elmalı Soda 24lü',                   'Beypazarı','Soda','Elma aromalı Beypazarı sodası, 24 adet.',                          '', 'pack'],
        ['Beypazarı Karadut Frenk Üzümlü Soda 24lü',    'Beypazarı','Soda','Karadut ve frenk üzümü aromalı soda, 24 adet.',                    '', 'pack'],
        ['Beypazarı Mango Ananas Soda 24lü',             'Beypazarı','Soda','Mango ve ananas aromalı soda, 24 adet.',                           '', 'pack'],
        ['Beypazarı Narlı Soda 24lü',                    'Beypazarı','Soda','Nar aromalı Beypazarı sodası, 24 adet.',                           '', 'pack'],
        ["Beypazarı Co'ala Lime Bah 24lü",               'Beypazarı','Soda',"Lime ve baharatlı aromalı Co'ala soda, 24 adet.",                  '', 'pack'],
        ['Sultan Sade Soda 24lü',                        'Sultan',   'Soda','Sade Sultan maden suyu, 24 adet koli.',                            '', 'pack'],
        ['Kınık Sade Soda',                              'Kınık',    'Soda','Türkiye\'de geniş hayran kitlesine sahip doğal soda.',             '', 'piece'],

        // ─── MEYVE SUYU ──────────────────────────────────────────────────────
        ['Juss Karışık Kutu 250ml 24lü',         'Juss',   'Meyve Suyu', 'Karışık meyve aromalı meyve suyu kutu 250ml × 24 adet.','', 'pack'],
        ['Juss Şeftali Kutu 250ml 24lü',         'Juss',   'Meyve Suyu', 'Şeftali aromalı meyve suyu kutu 250ml × 24 adet.',      '', 'pack'],
        ['Juss Kayısı Kutu 250ml 24lü',          'Juss',   'Meyve Suyu', 'Kayısı aromalı meyve suyu kutu 250ml × 24 adet.',       '', 'pack'],
        ['Juss Vişne Kutu 250ml 24lü',           'Juss',   'Meyve Suyu', 'Vişne aromalı meyve suyu kutu 250ml × 24 adet.',        '', 'pack'],
        ['Juss Vişne Tetra 200ml 27li',          'Juss',   'Meyve Suyu', 'Vişne nektar tetra pak 200ml × 27 adet.',               '', 'pack'],
        ['Juss Karışık Tetra 200ml 27li',        'Juss',   'Meyve Suyu', 'Karışık meyve tetra pak 200ml × 27 adet.',              '', 'pack'],
        ['Juss Kayısı Tetra 200ml 27li',         'Juss',   'Meyve Suyu', 'Kayısı nektar tetra pak 200ml × 27 adet.',              '', 'pack'],
        ['Juss Şeftali Tetra 200ml 27li',        'Juss',   'Meyve Suyu', 'Şeftali nektar tetra pak 200ml × 27 adet.',             '', 'pack'],
        ['Juss Cam Şişe Şeftali Nektarı 24lü',  'Juss',   'Meyve Suyu', 'Cam şişede şeftali nektarı, 24 adet.',                 '', 'pack'],
        ['Juss Cam Şişe Vişne Nektarı 24lü',    'Juss',   'Meyve Suyu', 'Cam şişede vişne nektarı, 24 adet.',                   '', 'pack'],
        ['Juss Cam Şişe Karışık Nektarı 24lü',  'Juss',   'Meyve Suyu', 'Cam şişede karışık meyve nektarı, 24 adet.',           '', 'pack'],
        ['Juss Cam Şişe Kayısı Nektarı 24lü',   'Juss',   'Meyve Suyu', 'Cam şişede kayısı nektarı, 24 adet.',                  '', 'pack'],
        ['Juss Şeftali Meyve İçeceği 12li',     'Juss',   'Meyve Suyu', 'Şeftali aromalı meyve içeceği, 12 adet.',             '', 'pack'],
        ['Juss Kayısı Meyve İçeceği 12li',      'Juss',   'Meyve Suyu', 'Kayısı aromalı meyve içeceği, 12 adet.',              '', 'pack'],
        ['Juss Karışık Meyve İçeceği 12li',     'Juss',   'Meyve Suyu', 'Karışık meyve aromalı içecek, 12 adet.',              '', 'pack'],
        ['Juss Vişne Meyve İçeceği 12li',       'Juss',   'Meyve Suyu', 'Vişne aromalı meyve içeceği, 12 adet.',               '', 'pack'],
        ['Meysu Slim Kayısı Nektarı 27li',      'Meysu',  'Meyve Suyu', 'Slim paket kayısı nektar 200ml × 27 adet.',            '', 'pack'],
        ['Meysu Slim Şeftali Nektarı 27li',     'Meysu',  'Meyve Suyu', 'Slim paket şeftali nektar 200ml × 27 adet.',           '', 'pack'],
        ['Meysu Slim Karışık Nektarı 27li',     'Meysu',  'Meyve Suyu', 'Slim paket karışık meyve nektar 200ml × 27 adet.',     '', 'pack'],
        ['Meysu Slim Vişne Meyve İçeceği 27li', 'Meysu',  'Meyve Suyu', 'Slim paket vişne meyve içeceği 200ml × 27 adet.',     '', 'pack'],
        ['Kardan Karadut Bardak 250cc',         'Kardan', 'Meyve Suyu', 'Bardakta karadut içeceği, 250cc.',                    '', 'piece'],
        ['Kardan Limonata Bardak 250cc',        'Kardan', 'Meyve Suyu', 'Bardakta limonata, 250cc.',                           '', 'piece'],
        ['Tarihi Odunpazarı Karadutlu İçecek 3lt','Tarihi Odunpazarı','Meyve Suyu','Geleneksel tarife ile hazırlanmış karadut içeceği, 3 litre.','', 'piece'],
        ['Tarihi Odunpazarı Limonata 3lt',     'Tarihi Odunpazarı','Meyve Suyu','Geleneksel tarife ile hazırlanmış limonata, 3 litre.',     '', 'piece'],
        ['Kardelen Limonata 250ml 12li',        'Kardelen','Meyve Suyu', 'Kardelen markalı taze limonata 250ml × 12 adet.',     '', 'pack'],
        ['Kardelen Karadut İçeceği 250ml 12li', 'Kardelen','Meyve Suyu', 'Kardelen markalı karadut meyveli içecek 250ml × 12 adet.', '', 'pack'],
        ['Kardelen Çilek İçeceği 250ml 12li',   'Kardelen','Meyve Suyu', 'Kardelen markalı çilek aromalı içecek 250ml × 12 adet.',   '', 'pack'],
        ['Pınar Limonata 330ml 12li',           'Pınar',  'Meyve Suyu', 'Pınar limonata 330ml × 12 adet.',
            'https://vanguardgida.com.tr/images/Lj811tTB38IjaCn9zW0oRg9bf8ykLx0R2FAlpJsT21I/_small/ks-prod/images/shop/6735a5592b654/product/6830454a806b3/B2QHlFAh097Ktx92CTQwSs8uVSJQqHD3SjQnCCk6.png', 'pack'],
        ['Pınar Limonata 1lt 12li',             'Pınar',  'Meyve Suyu', 'Pınar limonata 1L × 12 adet.',
            'https://vanguardgida.com.tr/images/itqu-N7P_CBu92D4AylUENi2dBpTGht4vN0Ji9RNjtY/_small/ks-prod/images/shop/6735a5592b654/product/683042fdb8eef/3VvcKkSblp41cmQRfuDGML3Xza1dNJZHixg4kA5G.png', 'pack'],
        ['Pınar Cam Şişe Limonata 250ml 24lü',  'Pınar',  'Meyve Suyu', 'Cam şişede Pınar limonata 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/6n9j1S3yrjyCYkbNfmglo1xnfT7ZfPfkdaYkYubdzME/_small/ks-prod/images/shop/6735a5592b654/product/683049a22e028/PnZm0y7v6PkQE1tgMIjRsFd8jn4MEMA3M88b0L7V.png', 'pack'],

        // ─── SU ──────────────────────────────────────────────────────────────
        ['Kardelen Su 330ml Pet 24lü',  'Kardelen','Su','Kardelen doğal kaynak suyu 330ml PET × 24 adet.',       '', 'pack'],
        ['Kardelen Su 500ml 24lü',      'Kardelen','Su','Kardelen doğal kaynak suyu 500ml × 24 adet.',           '', 'pack'],
        ['Kardelen Su 1lt Pet 12li',    'Kardelen','Su','Kardelen doğal kaynak suyu 1L PET × 12 adet.',          '', 'pack'],
        ['Kardelen Su 1.5lt Pet 12li',  'Kardelen','Su','Kardelen doğal kaynak suyu 1.5L PET × 12 adet.',        '', 'pack'],
        ['Kardelen Su 5lt Pet 4lü',     'Kardelen','Su','Kardelen doğal kaynak suyu 5L PET × 4 adet.',           '', 'pack'],
        ['Kardelen Su 19lt',            'Kardelen','Su','Kardelen doğal kaynak suyu damacana 19 litre.',          '', 'piece'],
        ['Kardelen Su 200ml Bardak 72li','Kardelen','Su','Bardakta kaynak suyu 200ml × 72 adet.',                '', 'pack'],
        ['Sultan Doğal Kaynak Suyu 0.5lt 2×12','Sultan','Su','Sultan doğal kaynak suyu 500ml, koli içi 2×12 adet.',
            'https://vanguardgida.com.tr/images/D7nVLktR1T7HAhMXA5d9SMMZjpG6DEsDd0gHMAKJ6i8/_small/ks-prod/images/shop/6735a5592b654/product/6737d8b24aca5/Q6cmhwtOwzcbY8xLJjOcKQHJWDhHaFqIRz2OtlYq.jpg', 'pack'],
        ['Sultan Doğal Kaynak Suyu 1.5lt 2×6','Sultan','Su','Sultan doğal kaynak suyu 1.5L, koli içi 2×6 adet.',
            'https://vanguardgida.com.tr/images/1xz_LS_Ws5NdQYN8r952QuDRq4A4ZAetxuW_qvL5JxI/_small/ks-prod/images/shop/6735a5592b654/product/6737d95057883/WPCmvUZLmDtkqgAJWfdAya5NWQKfZk8OKFBCHfke.jpg', 'pack'],
        ['Sultan Doğal Kaynak Suyu 5lt 4lü','Sultan','Su','Sultan doğal kaynak suyu 5L, koli içi 4 adet.',
            'https://vanguardgida.com.tr/images/s-zITOc_ES8UoVqTopvrnKIOdMLwY_2VOOQjVJJbHcQ/_small/ks-prod/images/shop/6735a5592b654/product/6737db1381dec/dedXyJsfmKQcPHtfFawPLYTElHUHrq93aJ3iDCG1.jpg', 'pack'],
        ['Pınar Sporcu Kapaklı Su 750ml 12li','Pınar','Su','Sporcu tipi kapaklı Pınar su 750ml × 12 adet.',
            'https://vanguardgida.com.tr/images/mIb1v-RZWSssLQe8LczlLqxN8nozr0CSm8BugVmfo5Y/_small/ks-prod/images/shop/6735a5592b654/product/68355b959ab1b/2gE3CB9lfhvJEiUHz1YCe7yBZCO3GfHMQj5DxdOl.png', 'pack'],

        // ─── ENERJİ İÇECEĞİ ──────────────────────────────────────────────────
        ['Max Fly Enerji İçeceği Classic 250ml 24lü', 'Max Fly', 'Enerji İçeceği','Klasik formül Max Fly enerji içeceği 250ml × 24 adet.',    '', 'pack'],
        ['Max Fly Enerji İçeceği Mojito 250ml 24lü',  'Max Fly', 'Enerji İçeceği','Mojito aromalı Max Fly enerji içeceği 250ml × 24 adet.',  '', 'pack'],
        ['Max Fly Enerji İçeceği Sirius 250ml 24lü',  'Max Fly', 'Enerji İçeceği','Sirius serisi Max Fly enerji içeceği 250ml × 24 adet.',   '', 'pack'],
        ['Max Fly Enerji İçeceği Vega 250ml 24lü',    'Max Fly', 'Enerji İçeceği','Vega serisi Max Fly enerji içeceği 250ml × 24 adet.',     '', 'pack'],
        ['Max Fly Enerji İçeceği Nova 250ml 24lü',    'Max Fly', 'Enerji İçeceği','Nova serisi Max Fly enerji içeceği 250ml × 24 adet.',     '', 'pack'],
        ['Max Fly Enerji İçeceği Cosmos 250ml 24lü',  'Max Fly', 'Enerji İçeceği','Cosmos serisi Max Fly enerji içeceği 250ml × 24 adet.',   '', 'pack'],
        ['Gorilla Ultimate Energy Original 250ml 12li','Gorilla', 'Enerji İçeceği','Gorilla Ultimate orijinal formül enerji içeceği 250ml × 12 adet.',
            'https://vanguardgida.com.tr/images/nAbHoQIwqqRH_y90Q7QfTvsR6W-liLPS1p6jz6BRI0Q/_small/ks-prod/images/shop/6735a5592b654/product/697750eb21203/wcTjB3k0OFtCLixULA51YabNZSczDcYyLAwZjM6F.jpg', 'pack'],
        ['Gorilla Ultimate Energy Original 500ml 12li','Gorilla', 'Enerji İçeceği','Gorilla Ultimate orijinal formül enerji içeceği 500ml × 12 adet.',
            'https://vanguardgida.com.tr/images/tV7UGkUs61PWQW01NzYGJzCwb2ejGplf53C0cCyZEhw/_small/ks-prod/images/shop/6735a5592b654/product/697750b61d7ae/A7zAe6yI9gYHBSAMbJjZtdG11PllL1ZQhybVUpiD.png', 'pack'],
        ['Gorilla Energy Karma Koli 4 Çeşit 24lü 500ml','Gorilla','Enerji İçeceği','4 farklı çeşit Gorilla enerji içeceği karma koli 500ml × 24 adet.',
            'https://vanguardgida.com.tr/images/PR8O-059O0GObD_aFHFkIyrM2qtvB9Lh73hpnLp8nK4/_small/ks-prod/images/shop/6735a5592b654/product/68d3ce0aed870/1LQBmrqpTlVf1sLxRYAuQJWSokQ4iw9gzmniAuw6.jpg', 'pack'],
        ['Gorilla Energy Karma Koli 4 Çeşit 24lü 250ml','Gorilla','Enerji İçeceği','4 farklı çeşit Gorilla enerji içeceği karma koli 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/4ZYNuBKdULk_03O_UnC_HOFU3OIk1hAKl8OYVUJs45A/_small/ks-prod/images/shop/6735a5592b654/product/68d3ce04dc1d4/qSeFMq4OeVWjUTo8RvN7IefKMRAvmOQ29d01Iv6i.jpg', 'pack'],
        ['Gorilla Energy Karpuz Mango 500ml',         'Gorilla', 'Enerji İçeceği','Karpuz ve mango aromalı Gorilla enerji içeceği, 500ml.',
            'https://vanguardgida.com.tr/images/P8KmkIwPRDetsZhg66aQxAWBER7QYHQL_auoReKpWGE/_small/ks-prod/images/shop/6735a5592b654/product/67a135325d4fc/M0BXWcSs28O3SJ6BamAP7dexa7UV9XrWPdRP3ios.png', 'piece'],
        ['Gorilla Energy Mango Hindistan Cevizi 250ml 12li','Gorilla','Enerji İçeceği','Mango ve hindistan cevizi aromalı Gorilla enerji içeceği 250ml × 12 adet.',
            'https://vanguardgida.com.tr/images/2AJDDzZEFqWU8KfQve4aEc39zIAXGMxcZkUgcl8ZHcA/_small/ks-prod/images/shop/6735a5592b654/product/677ee41218b1f/lUUpkW69dkBKGESiiYWMlzVck73xgJH5HaDhnb9I.png', 'pack'],
        ['Gorilla Energy Mango Hindistan Cevizi 500ml','Gorilla', 'Enerji İçeceği','Tropikal mango-hindistan cevizi aromalı Gorilla enerji içeceği, 500ml.',
            'https://vanguardgida.com.tr/images/Z9Ok2uZ9rqY-FZstf1sOrx2SsyJqA3S-wvORmKxvbLw/_small/ks-prod/images/shop/6735a5592b654/product/677ee3a3428aa/5THtZ8iJWboSLK3kZFNsulqCQ5yntRH4Xzd0Zdk1.png', 'piece'],
        ['Gorilla Energy Original 500ml',             'Gorilla', 'Enerji İçeceği','Klasik Gorilla orijinal enerji içeceği, 500ml.',
            'https://vanguardgida.com.tr/images/UnGrnDWHpnAY0IqQluxKxDKZ3_dRisHSHpSnHrQHR9w/_small/ks-prod/images/shop/6735a5592b654/product/677ee29d272d0/egox6W71azzyvGrVIJyiuqdtgnosARgYvo92PUek.png', 'piece'],
        ['Gorilla Energy Original 250ml',             'Gorilla', 'Enerji İçeceği','Klasik Gorilla orijinal enerji içeceği, 250ml.',
            'https://vanguardgida.com.tr/images/yr1c5iTMsq8tw13qX-sOSe9U6-1EXZ6x97A24J3_1Wo/_small/ks-prod/images/shop/6735a5592b654/product/677ee24b8b2cb/rsdZ0KFYLAtbBzbV93IWu4P7PtE6bTsWR3h6Iz2Q.png', 'piece'],
        ['Red Bull 250ml 24lü',                       'Red Bull','Enerji İçeceği','Red Bull enerji içeceği 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/zkpn6EP3UMwY4RxEybLBm6ajGCLUn0zBYiONsoEsmDU/_small/ks-prod/images/shop/6735a5592b654/product/67655e129893d/RcO7BFyeRi4HQkIh08b6GKL25H1njrs3NtWZIavC.png', 'pack'],
        ['Hot Line Enerji 250ml 24lü',                'Hot Line','Enerji İçeceği','Hot Line enerji içeceği 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/AC6oIKmoIiOsOmGWeFK3D4O-9JZEw5_QfFDXRv4PcIQ/_small/ks-prod/images/shop/6735a5592b654/product/68c3f326ee7d8/sXnfNQIqhJTn1c2MCc7Gg2TmSFjmIfHZX1km8ezq.jpg', 'pack'],
        ['Hot Line Enerji 500ml 24lü',                'Hot Line','Enerji İçeceği','Hot Line enerji içeceği 500ml × 24 adet.',
            'https://vanguardgida.com.tr/images/arsLP29xOWrR5P_0TcrWUJh1X3xaYhEHgNz7lkodoiA/_small/ks-prod/images/shop/6735a5592b654/product/68c3f2e39caea/ZwXnkC85d9vWcywlKKrS8HFgtlH0vQUwP8fluWJG.png', 'pack'],
        ['Hot Line Enerji Pet 1lt 12li',              'Hot Line','Enerji İçeceği','Hot Line enerji içeceği PET 1L × 12 adet.',
            'https://vanguardgida.com.tr/images/deZCqOw8EQYFDuNKlTPoMwDU8BI7UcW7z61_biFLNE/_small/ks-prod/images/shop/6735a5592b654/product/68c3f2d7aee2e/Mh5yvl6uHpkiTXbzjZj65CQlxXhV620hXGazpnYB.jpg', 'pack'],

        // ─── ŞALGAM ──────────────────────────────────────────────────────────
        ['Doğanay 300ml Acılı Şalgam 24lü',   'Doğanay','Şalgam','Doğanay acılı şalgam suyu PET şişe 300ml × 24 adet.',
            'https://vanguardgida.com.tr/images/KRyXQDLFphV_LCoFaGWr3Yn_ESkPOgpiAVOsuw03sLQ/_small/ks-prod/images/shop/6735a5592b654/product/6855dc1987a89/kY20LF9PmMW3VKquJ72Dx4OTQMjKAqYdVHqOZOuP.png', 'pack'],
        ['Doğanay 300ml Acısız Şalgam 24lü',  'Doğanay','Şalgam','Doğanay acısız şalgam suyu PET şişe 300ml × 24 adet.',
            'https://vanguardgida.com.tr/images/mXwpENDSHcSDG32VXN6kOTD04MFQCOBWtRgMBfET11Q/_small/ks-prod/images/shop/6735a5592b654/product/6855dc2866c47/LfMw4WJREI1sYeMVBXk9KVRKxRoP8WoWYC1ASe7C.png', 'pack'],
        ['Doğanay Tatlı Şalgam 1lt 12li',     'Doğanay','Şalgam','Tatlı şalgam suyu 1L PET × 12 adet.',                              '', 'pack'],
        ['As01 300ml Acısız Şalgam 24lü',     'As01',   'Şalgam','As01 marka acısız şalgam suyu 300ml × 24 adet.',
            'https://vanguardgida.com.tr/images/zvmZQ7O5fuNQvjO8hHnpBJTQN1i5wH4Ygf3JUBhtV9c/_small/ks-prod/images/shop/6735a5592b654/product/69f8843395cf5/WhatsApp_Image_2026-05-04_at_14.31.21-removebg-preview.png', 'pack'],
        ['As01 300ml Acılı Şalgam 24lü',      'As01',   'Şalgam','As01 marka acılı şalgam suyu 300ml × 24 adet.',
            'https://vanguardgida.com.tr/images/lrlaMTbU-nOLYFC09ggNx3P-lVj_bGx_whgt7aJmfqg/_small/ks-prod/images/shop/6735a5592b654/product/69f883d09e6bc/WhatsApp_Image_2026-05-04_at_14.31.21-removebg-preview.png', 'pack'],
        ['As01 3lt Acılı Şalgam 6lı',         'As01',   'Şalgam','As01 büyük boy acılı şalgam suyu 3L × 6 adet.',
            'https://vanguardgida.com.tr/images/IIgDkAzmcpx0ensgJJ6tSgzQ7D3n76X0HFL_ir0PjI/_small/ks-prod/images/shop/6735a5592b654/product/69f884bba6e34/WhatsApp_Image_2026-05-04_at_14.31.21-removebg-preview.png', 'pack'],

        // ─── KAHVE ───────────────────────────────────────────────────────────
        ['Max Brew Latte Soğuk Kahve 250ml 12li',    'Max Brew',      'Kahve','Latte aromalı soğuk kahve 250ml × 12 adet.',             '', 'pack'],
        ['Max Brew Caramel Soğuk Kahve 250ml 12li',  'Max Brew',      'Kahve','Karamel aromalı soğuk kahve 250ml × 12 adet.',           '', 'pack'],
        ['Max Brew Mocha Soğuk Kahve 250ml 12li',    'Max Brew',      'Kahve','Mocha aromalı soğuk kahve 250ml × 12 adet.',             '', 'pack'],
        ['Nescafe 3+1 Arada 10gr 56lı',             'Nescafe',       'Kahve',"Nescafe 3'ü 1 arada hazır kahve 10gr × 56 adet.",        '', 'pack'],
        ['Nescafe 3+1 Süt Köpük 17.4gr 48li',       'Nescafe',       'Kahve','Süt köpüklü Nescafe 3+1 17.4gr × 48 adet.',              '', 'pack'],
        ['Nescafe 2+1 Arada 10gr 56lı',             'Nescafe',       'Kahve',"Nescafe 2'si 1 arada hazır kahve 10gr × 56 adet.",       '', 'pack'],
        ['My Coffee Latte 250ml 24lü',               'My Coffee',     'Kahve','Latte aromalı soğuk kahve 250ml × 24 adet.',             '', 'pack'],
        ['My Coffee Caramel 250ml 24lü',             'My Coffee',     'Kahve','Karamel aromalı soğuk kahve 250ml × 24 adet.',           '', 'pack'],
        ['Kahve Dünyası Orta Kavrulmuş 100gr 12li',  'Kahve Dünyası', 'Kahve','Orta kavrulmuş filtre kahve 100gr × 12 adet.',          '', 'pack'],
        ["Kahve Dünyası 3'ü 1 Arada 40lı Paket",    'Kahve Dünyası', 'Kahve',"Hazır 3'ü 1 arada kahve paketi, 40 adet.",              '', 'pack'],
        ["Kahve Dünyası 2'si 1 Arada 40lı Paket",   'Kahve Dünyası', 'Kahve',"Hazır 2'si 1 arada kahve paketi, 40 adet.",             '', 'pack'],
        ["KD 2'si 1 Arada 192li",                   'Kahve Dünyası', 'Kahve',"Toplu ekonomik paket 2'si 1 arada kahve, 192 adet.",    '', 'pack'],
        ["KD 3'ü 1 Arada 192li",                    'Kahve Dünyası', 'Kahve',"Toplu ekonomik paket 3'ü 1 arada kahve, 192 adet.",    '', 'pack'],

        // ─── KETÇAP & MAYONEZ ─────────────────────────────────────────────────
        ['Pınar Mayonez Servis 700gr', 'Pınar','Ketçap & Mayonez','Pınar servis boy mayonez, 700 gram.',
            'https://vanguardgida.com.tr/images/rTE77HtYn4mwoEoiuUcd6xvMJEi0BB0StF1hEhqzLo8/_small/ks-prod/images/shop/6735a5592b654/product/69245e4e313cb/30Jnk61Aie3vZZAP0sC3ZavwGG4ssjAkRp2J45AN.png', 'piece'],
        ['Pınar Ketçap Servis 800gr',  'Pınar','Ketçap & Mayonez','Pınar servis boy ketçap, 800 gram.',
            'https://vanguardgida.com.tr/images/H6BR7SSIIWmhVXY4M1XMjMoFu7HypW4ridAN0pT3p3k/_small/ks-prod/images/shop/6735a5592b654/product/69245e4c228e2/ZYNtdR1LTmKlCgoEJ5FcA64hZdwwF2YHprY1qVpD.png', 'piece'],

        // ─── SOĞUK ÇAY ────────────────────────────────────────────────────────
        ['Didi Soğuk Çay Şeftali 250ml 24lü',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 250ml × 24 adet.',   '', 'pack'],
        ['Didi Soğuk Çay Çilek 250ml 24lü',     'Didi','Soğuk Çay','Çilek aromalı Didi soğuk çay 250ml × 24 adet.',     '', 'pack'],
        ['Didi Soğuk Çay Limon 250ml 24lü',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 250ml × 24 adet.',     '', 'pack'],
        ['Didi Soğuk Çay Bergamot 250ml 24lü',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 250ml × 24 adet.',  '', 'pack'],
        ['Didi Soğuk Çay Şeftali 330ml 24lü',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 330ml × 24 adet.',   '', 'pack'],
        ['Didi Soğuk Çay Limon 330ml 24lü',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 330ml × 24 adet.',     '', 'pack'],
        ['Didi Soğuk Çay Bergamot 330ml 24lü',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 330ml × 24 adet.',  '', 'pack'],
        ['Didi Soğuk Çay Bergamot 500ml 24lü',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 500ml × 24 adet.',  '', 'pack'],
        ['Didi Soğuk Çay Limon 500ml 24lü',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 500ml × 24 adet.',     '', 'pack'],
        ['Didi Soğuk Çay Şeftali 500ml 24lü',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 500ml × 24 adet.',   '', 'pack'],
        ['Didi Soğuk Çay Şeftali 1lt 12li',     'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 1L × 12 adet.',      '', 'pack'],
        ['Didi Soğuk Çay Limon 1lt 12li',       'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 1L × 12 adet.',        '', 'pack'],
        ['Didi Soğuk Çay Bergamot 1lt 12li',    'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 1L × 12 adet.',     '', 'pack'],
        ['Didi Soğuk Çay Şeftali 1.5lt 12li',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 1.5L × 12 adet.',    '', 'pack'],
        ['Didi Soğuk Çay Limon 1.5lt 12li',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 1.5L × 12 adet.',      '', 'pack'],
        ['Didi Soğuk Çay Bergamot 1.5lt 12li',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 1.5L × 12 adet.',   '', 'pack'],
        ['Didi Soğuk Çay Bergamot 2.5lt 6lı',   'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 2.5L × 6 adet.',    '', 'pack'],
        ['Didi Soğuk Çay Limon 2.5lt 6lı',      'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 2.5L × 6 adet.',       '', 'pack'],
        ['Didi Soğuk Çay Şeftali 2.5lt 6lı',    'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 2.5L × 6 adet.',     '', 'pack'],

        // ─── TOZ İÇECEK ──────────────────────────────────────────────────────
        ['Poli Sütlü Muz 250gr',                'Poli','Toz İçecek','Muzlu sütlü toz içecek karışımı, 250gr.',
            'https://vanguardgida.com.tr/images/TNZBiZyx95mOQ15a_S8TRPaWlgD66JmygLJ0GggLgg4/_small/ks-prod/images/shop/6735a5592b654/product/68d5cb6384e79/mz1hxyLVgk3n3NBrwFcxcoG91hESm7Pqrrq7V5La.png', 'piece'],
        ['Poli Salep 250gr 24lü',               'Poli','Toz İçecek','Geleneksel salep aromalı toz içecek, 250gr.',
            'https://vanguardgida.com.tr/images/P0T4z3OSA4ekAzm8-nyZmTcE3TeaKWe_prsydbS9KRU/_small/ks-prod/images/shop/6735a5592b654/product/68d5cab429ac0/qvb4aUOaFXpYu4EZKm3bqi3P0ql2argrbkOBN0Os.jpg', 'piece'],
        ['Poli Nane Limon 250gr',               'Poli','Toz İçecek','Nane ve limon aromalı toz içecek, 250gr.',
            'https://vanguardgida.com.tr/images/w8pDcJFwRTESteykskK1Oo8SpnfyaB6UpbvTSWZRAAQ/_small/ks-prod/images/shop/6735a5592b654/product/68d5cb3310945/4EUV8jeriUWraMucrghMiIXTaXNmEEZOw6zoXd7m.png', 'piece'],
        ['Poli Süka Kakaolu Aromalı Toz İçecek','Poli','Toz İçecek','Kakao aromalı Poli Süka toz içecek.',
            'https://vanguardgida.com.tr/images/oBfRNhHUZY4a1RbTO5z7vKmHonJwnENWmYAQezXdCjM/_small/ks-prod/images/shop/6735a5592b654/product/6877999666caf/UQTXWUD8dg0WKnqVnRYRttnrn95xO0Fb02OS1GqW.jpg', 'piece'],
        ['Poli Tarçın 300gr',                   'Poli','Toz İçecek','Tarçın aromalı toz içecek, 300gr.',
            'https://vanguardgida.com.tr/images/rIuSGn1UiqmLD6B9VPOuV6BR4ASE1nBWCnYSYfyfqR8/_small/ks-prod/images/shop/6735a5592b654/product/68d5caed717dd/DtVkZLWcgb4F.png', 'piece'],
        ['Poli Kuşburnu Aromalı Toz İçecek 300gr','Poli','Toz İçecek','Kuşburnu aromalı toz içecek, 300gr.',
            'https://vanguardgida.com.tr/images/CI8V8XGmEv1zVhBINn_L8cp9ulcsTi00m4VXoimVxog/_small/ks-prod/images/shop/6735a5592b654/product/687799359b9e6/YZiZkibnGY2xFvWzAnsVJSCKgWnGZ9gqmXgAvCIn.jpg', 'piece'],
        ['Poli Kekik 300gr 24lü',               'Poli','Toz İçecek','Kekik aromalı toz içecek, 300gr.',
            'https://vanguardgida.com.tr/images/y_E_1a6YOgW36OCm2YaImGhvrAyHIsja3VWtN1VWGfg/_small/ks-prod/images/shop/6735a5592b654/product/68d5ca82b7e11/Nta5LAyKCS0CIxY3dj1rZxDD669fENRmIxDtXGnS.png', 'piece'],
        ['Poli Kivi 300gr',                     'Poli','Toz İçecek','Kivi aromalı toz içecek, 300gr.',
            'https://vanguardgida.com.tr/images/0m8on2iB6roA5uZ-llMxyS-cFu1FB49IjI53431gnPc/_small/ks-prod/images/shop/6735a5592b654/product/68c3ecfe1e542/Nwwulf2EUC3sdca2fPPk7Y9OX5RctRAjl80F1m5f.jpg', 'piece'],
        ['Poli Portakal Aromalı Toz İçecek 300gr','Poli','Toz İçecek','Portakal aromalı toz içecek, 300gr.',
            'https://vanguardgida.com.tr/images/UtccOwTVywBNFJgUbZpEzf-Be5l_MCJXO5Jmvv8jyfM/_small/ks-prod/images/shop/6735a5592b654/product/6877994dcc112/l8UadQg0CxP7Ug9SzdSFXwiYqlCcqktOlX3B0XUT.jpg', 'piece'],
    ];

    public function run(): void
    {
        $brandMap    = Brand::pluck('id', 'slug')->all();
        $categoryMap = Category::pluck('id', 'slug')->all();

        $created = 0;
        $updated = 0;

        foreach ($this->products as [$name, $brandName, $categoryName, $description, $imageUrl, $unit]) {
            $sku  = Str::limit(Str::slug($name), 50, '');
            $slug = Str::slug($name);

            // Slug çakışmasını önle: aynı slug varsa sayaç ekle
            $baseSlug = $slug;
            $counter  = 1;
            while (Product::withTrashed()->where('slug', $slug)->where('sku', '!=', $sku)->exists()) {
                $slug = $baseSlug . '-' . $counter++;
            }

            $brandSlug    = Str::slug($brandName);
            $categorySlug = Str::slug($categoryName);

            $brandId = $brandMap[$brandSlug] ?? null;
            if (! $brandId) {
                $this->command->warn("  ⚠ Marka bulunamadı: '{$brandName}' — önce VanguardBrandSeeder çalıştırın.");
                continue;
            }

            $product = Product::withTrashed()->updateOrCreate(
                ['sku' => $sku],
                [
                    'brand_id'    => $brandId,
                    'name'        => $name,
                    'slug'        => $slug,
                    'description' => $description,
                    'unit'        => $unit === 'pack' ? 'pack' : 'piece',
                    'price'       => 0.00,
                    'tax_rate'    => 20.00,
                    'is_active'   => true,
                    'is_featured' => false,
                    'deleted_at'  => null,
                ],
            );

            $product->wasRecentlyCreated ? $created++ : $updated++;

            // Kategori
            if (isset($categoryMap[$categorySlug])) {
                $product->categories()->syncWithoutDetaching([$categoryMap[$categorySlug]]);
            } else {
                $this->command->warn("  ⚠ Kategori bulunamadı: '{$categoryName}' — önce VanguardCategorySeeder çalıştırın.");
            }

            // Görsel
            if ($imageUrl && ! $product->images()->where('image_url', $imageUrl)->exists()) {
                $product->images()->update(['is_primary' => false]);
                $product->images()->create([
                    'image_url'  => $imageUrl,
                    'is_primary' => true,
                    'sort_order' => 0,
                ]);
            }
        }

        $total = count($this->products);
        $this->command->info("✓ {$total} Vanguard ürünü işlendi ({$created} yeni, {$updated} güncellendi).");
    }
}
