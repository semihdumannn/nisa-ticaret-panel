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
        ['Havran Çiftlik Cam 245ml 20li',       'Havran Çiftlik', 'Ayran',              'Cam şişede geleneksel Türk ayranı, 245ml × 20 adet.',                  'https://vanguardgida.com.tr/images/Gh1jGUZ_hW4Of8yDzEd0BOO7NR8hdygXMyvGGB6ZxAk/_small/ks-prod/images/shop/6735a5592b654/product/69c2f36b93346/471025185_889058233215013_4442781479880136588_n-removebg-preview-Photoroom1.png', 'pack'],
        ['Havran Çiftlik Pet 245ml 20li',        'Havran Çiftlik', 'Ayran',              'PET şişede ayran, 245ml × 20 adet.',                                  'https://vanguardgida.com.tr/images/N9ZBeL5RCrB2DY6W_FlZvuPM2iRYItUHOGfHlSvzY_8/_small/ks-prod/images/shop/6735a5592b654/product/69c2f30e1fd80/thumb_2026_03_24__06_33_53--WhatsApp_Image_2026-03-24_at_09.32.46-removebg-preview.png', 'pack'],
        ['Havran Çiftlik 280ml 12li',            'Havran Çiftlik', 'Ayran',              'Küçük boy cam şişede ayran, 280ml × 12 adet.',                        'https://vanguardgida.com.tr/images/5-WiyJ56hvl_X1S6Da_AL0aXJAc0iX2XB42RH0QRGu8/_small/ks-prod/images/shop/6735a5592b654/product/69c2f23077ff8/471025185_889058233215013_4442781479880136588_n-removebg-preview-Photoroom.png', 'pack'],
        ['Havran Çiftlik 180ml 20li',            'Havran Çiftlik', 'Ayran',              'Mini boyda ayran, 180ml × 20 adet.',                                  'https://vanguardgida.com.tr/images/tOQLLJgJyqdRR1NvWGo1-38SeTS7v3dk9YFsMvh6_qY/_small/ks-prod/images/shop/6735a5592b654/product/69c2f2182d0b9/Adsztasarm2.png', 'pack'],
        ['Havran Çiftlik 170ml 20li',            'Havran Çiftlik', 'Ayran',              'Mini boyda ayran, 170ml × 20 adet.',                                  'https://vanguardgida.com.tr/images/BR7bFDEMii0OaFqgJ0cr8rBBj7DWxuZvCy5cduKmSwo/_small/ks-prod/images/shop/6735a5592b654/product/69c2f1f867472/Adsztasarm2.png', 'pack'],
        ['Havran Çiftlik Cam Lt 6lı',            'Havran Çiftlik', 'Ayran',              'Cam şişede 1 litrelik aile boy ayran, 6 adet.',                       'https://vanguardgida.com.tr/images/qWjVAO_fi6rOPXghuaIpgKGj11kDwTJ6dSId_yWeNFY/_small/ks-prod/images/shop/6735a5592b654/product/69c2f27f93907/Adsz_tasarm__3_-removebg-preview.png', 'pack'],

        // ─── NOODLE ──────────────────────────────────────────────────────────
        ['İndomie Bardak Köri Aromalı Noodle 24lü',  'İndomie', 'Noodle', 'Körili aromalı bardak instant noodle, 24 adet.',            'https://vanguardgida.com.tr/images/62aH7yVz-QvWhoYv6xR4y0WmyPePCSp0mO-ULg0QuE0/_small/ks-prod/images/shop/6735a5592b654/product/689923144f448/Pi6svP5pBp9bvgg5hUAMAL6hGjrvJA09aEAXNp1V.png', 'pack'],
        ['İndomie Bardak Tavuklu Noodle 24lü',        'İndomie', 'Noodle', 'Tavuk aromalı bardak instant noodle, 24 adet.',             'https://vanguardgida.com.tr/images/7dQnBpgDsJBDb1CiLKOajsAd3YdYn6waz_V-Y9iAKQs/_small/ks-prod/images/shop/6735a5592b654/product/689922c967dab/YwY3JNPbQpm2D3JnvSZbkNZezLA6LOCANMSisjiI.png', 'pack'],
        ['İndomie Jumbo Köri Aromalı Noodle 40lı',    'İndomie', 'Noodle', 'Jumbo boy körili paket noodle, 40 adet.',                   'https://vanguardgida.com.tr/images/4yoSs55xaFEOmQnRqLcLQbbDsVyGS_xXwV6jrxpGV24/_small/ks-prod/images/shop/6735a5592b654/product/689925e768d97/UcptRZY2nxfRUD4ZZcTKPanUI5adIvqM8IjD4KAv.png', 'pack'],
        ['İndomie Jumbo Tavuk Aromalı Noodle 40lı',   'İndomie', 'Noodle', 'Jumbo boy tavuk aromalı paket noodle, 40 adet.',            'https://vanguardgida.com.tr/images/uuAmtyogfBor6AC9Q8hBtVUlZ8zvdUk6XdIkBJ55Tg8/_small/ks-prod/images/shop/6735a5592b654/product/689925784e857/bqIig8qpy8caSnE8NweojOZrIlQcBYEOQBO7Pvhm.png', 'pack'],
        ['İndomie Paket Tavuklu Noodle 40lı',         'İndomie', 'Noodle', 'Standart paket tavuk aromalı noodle, 40 adet.',             'https://vanguardgida.com.tr/images/55b7VjTrDS-gPB0fLyMUVqk93zeoDdKzpyQHcupi1OU/_small/ks-prod/images/shop/6735a5592b654/product/689923d9299db/mQMiv4szNbwWyMuWysog7xxX1iYLv459lossKN52.png', 'pack'],
        ['İndomie Paket Spesyal Noodle 40lı',         'İndomie', 'Noodle', 'Özel soslu spesyal paket noodle, 40 adet.',                 'https://vanguardgida.com.tr/images/iiLr0g_sUpEQp_oYIr83F0evjnDMnDYXfkV9lPUcNsc/_small/ks-prod/images/shop/6735a5592b654/product/6899238281c53/rWT4p4vfbqd04l6nkYZ51ccYed717tjOW0XJXyps.png', 'pack'],
        ['İndomie Paket Körili Noodle 40lı',          'İndomie', 'Noodle', 'Körili paket noodle, 40 adet.',                            'https://vanguardgida.com.tr/images/2hM2gi1gbYWtRlRWGI_ez6_q81qr7XhdZGN-Pl4yhBA/_small/ks-prod/images/shop/6735a5592b654/product/6899235181974/BXcrKfIeiHS0a1KWvuyIpeGUmHD0kR1swnTTACZE.png', 'pack'],
        ['İndomie Gurme Soya Soslu 40lı',             'İndomie', 'Noodle', 'Soya soslu gurme paket noodle, 40 adet.',                  'https://vanguardgida.com.tr/images/fwsfkUaMsnQaIUN5Fp7bYyslvK-NU4VlmYq926j8bHk/_small/ks-prod/images/shop/6735a5592b654/product/6899268bd762a/tUfvg84TMHAyZXQ2YrGWq9CranYtGqCvn1gRaFAL.png', 'pack'],

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
        ['Max Fly Cola 1lt 12li',                'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly kola 1L × 12 adet koli.',                                     'https://vanguardgida.com.tr/images/MI0uhPHWFtmj5NRa1nLDQovDXXE0mGjO8QxMuSYKgL0/_small/ks-prod/images/shop/6735a5592b654/product/6a07090bafe17/MX-removebg-preview.png', 'pack'],
        ['Max Fly Cola 330ml 24lü',              'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly kola kutu 330ml × 24 adet.',                                  'https://vanguardgida.com.tr/images/YIxXS7GU0mhTNyWyxzgPMbRvc--1t045JXqiBtPtisw/_small/ks-prod/images/shop/6735a5592b654/product/689e6a0797979/ai0gNy6iilrKIgZdaOihmLmuPbNRA2SpRKjCD8di.png', 'pack'],
        ['Max Fly Cola 250ml 24lü',              'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly kola kutu 250ml × 24 adet.',                                  'https://vanguardgida.com.tr/images/ruOcJNi1O6uvaPXA2upwbrMxG_3_wRrpdZE660sGncc/_small/ks-prod/images/shop/6735a5592b654/product/689e69fd17761/2wcAld4YJrnDhaa2F53ojIvboqHfdwlTgnHvutGa.png', 'pack'],
        ['Max Fly Cola Şekersiz 330ml 24lü',     'Max Fly',    'Kola & Gazlı İçecek', 'Şekersiz Max Fly kola kutu 330ml × 24 adet.',                         'https://vanguardgida.com.tr/images/brIOcCyZ4RYf8wT8OLD9Y4UqulzrhEdzYt7kMnEj_h4/_small/ks-prod/images/shop/6735a5592b654/product/689e6a30344ae/iTOux5fu3cxcLQQlxhDlTwTcssBQq3uOzM863M0Z.png', 'pack'],
        ['Max Fly Portakal 250ml 24lü',          'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly portakal aromalı gazlı içecek 250ml × 24 adet.',              'https://vanguardgida.com.tr/images/hKn3_EBV-1rYTPEa0gmHEF_5bNUrRpV4MYaJ2Qs9dYg/_small/ks-prod/images/shop/6735a5592b654/product/689e6a57ec6bd/dMCi4caGzKigAYvkQ6YFSrENr2v4AcEhxUbxwh2J.png', 'pack'],
        ['Max Fly Limon Lime 250ml 24lü',        'Max Fly',    'Kola & Gazlı İçecek', 'Max Fly limon ve misket limonu aromalı gazlı içecek 250ml × 24 adet.','https://vanguardgida.com.tr/images/6cTkY5R2Rvzu2zlWY7JlmVX539dLQGcn7zmmFA-VtFw/_small/ks-prod/images/shop/6735a5592b654/product/689e6a9588ecd/52JIiNYVq9o5kFkyHN86pJxdlNb1TTSDmr1Lqqtj.png', 'pack'],
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
        ['Coca Cola 330ml Kutu 24lü',             'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola kutu 330ml × 24 adet.',                                     'https://vanguardgida.com.tr/images/C4cfmnIKDBa7LCKQwhgYE8VtTw7O-RMtHFlDYXi0NAM/_small/ks-prod/images/shop/6735a5592b654/product/68553fdd56734/pOvyr6aEwmfCoI1easwa6Zjg71BHPBwD8i4jNrSH.png', 'pack'],
        ['Coca Cola 200ml Kutu 24lü',             'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola mini kutu 200ml × 24 adet.',                                'https://vanguardgida.com.tr/images/8EP8hkdicdYtpdev3LEKQ9NErsDn8nlyNwj2-mojdPs/_small/ks-prod/images/shop/6735a5592b654/product/697726a72b345/oOPVftqs4OhLsb9VrKfd0UuztoUdI0yNircm2GV0.jpg', 'pack'],
        ['Coca Cola 200ml Cam İadesiz Şişe 24lü', 'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola cam iadesiz şişe 200ml × 24 adet.',                         'https://vanguardgida.com.tr/images/7Gqn0IQ5M6beLMu9V8njETZaLldxPOTXhnO9pxPIBjY/_small/ks-prod/images/shop/6735a5592b654/product/68553f98904e4/XFmC3cn8FJ4B3hr4AxUisBxCaiP4VuWVwE8wvoyC.jpg', 'pack'],
        ['Coca Cola Dönüşümlü Şişe 200ml 24lü',  'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola dönüşümlü cam şişe 200ml × 24 adet.',                       'https://vanguardgida.com.tr/images/2op1W1TWr4Wo6fMvAlcDjNTdd4Uq7981k8qjvLk3xW4/_small/ks-prod/images/shop/6735a5592b654/product/69a0247b0934c/AWH-RB-product-shot-500X500-Product-shotFn35124R2TrimC-1-removebg-preview.png', 'pack'],
        ['Coca Cola 2.5lt 6lı',                  'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola büyük boy PET 2.5L × 6 adet.',                              'https://vanguardgida.com.tr/images/3XFnX_TR7-fU2H8WPNA0B5g3LMxrXaopQuIBaCFk0rg/_small/ks-prod/images/shop/6735a5592b654/product/686ef53a8aaac/Tes0R3MHntg10eLeAiL5yhFkkALWV1p5abgZ0PFw.png', 'pack'],
        ['Coca Cola 1lt 12li',                   'Coca-Cola',  'Kola & Gazlı İçecek', 'Coca-Cola PET şişe 1L × 12 adet.',                                    'https://vanguardgida.com.tr/images/e3_X3BWKttSz0jQVG4-D5Q-YIL1J9wATXvNmcuwGL0A/_small/ks-prod/images/shop/6735a5592b654/product/68554023eb6f5/eQcwVAYECT4YsgTpmWRKVbobcE2QNpK1ZwAesIW5.png', 'pack'],
        ['Fanta 330ml Kutu 24lü',                'Fanta',      'Kola & Gazlı İçecek', 'Fanta portakal aromalı kutu 330ml × 24 adet.',                         'https://vanguardgida.com.tr/images/W5bK4hg1aAz0wTaJId9oHiZFRGuvxOI7LA4EKsn-2c0/_small/ks-prod/images/shop/6735a5592b654/product/68d1d5e37e7d3/nSXkG1NOBdygaQrBnMjmtN72uqxr7HxihyZHkYNz.png', 'pack'],
        ['Fanta 200ml Kutu 12li',                'Fanta',      'Kola & Gazlı İçecek', 'Fanta mini kutu 200ml × 12 adet.',                                     'https://vanguardgida.com.tr/images/HxDk_ldqjgMe5VKmNVdR0pnQcEaZKllXINiTRvnwgT4/_small/ks-prod/images/shop/6735a5592b654/product/69772861263bd/Fc9T8lDZx7pswVhKmy1qtVUFZmBuClCsP9mQ431w.jpg', 'pack'],
        ['Fanta Dönüşümlü Şişe 200ml 24lü',      'Fanta',      'Kola & Gazlı İçecek', 'Fanta dönüşümlü cam şişe 200ml × 24 adet.',                            'https://vanguardgida.com.tr/images/UB7tQhSEX8v5Rypjsh1dEYytrjmJQAQI9qqODwy4hco/_small/ks-prod/images/shop/6735a5592b654/product/69a025b9681ce/Fanta-RGB-500X500-40656-removebg-preview.png', 'pack'],
        ['Fanta 2.5lt 6lı',                      'Fanta',      'Kola & Gazlı İçecek', 'Fanta büyük boy PET 2.5L × 6 adet.',                                  'https://vanguardgida.com.tr/images/KmVrBBJ66-o_YcvIP3WAQgSH0xicnI6vaeksqVcLyZ4/_small/ks-prod/images/shop/6735a5592b654/product/6871185b92452/vCa7JEF9p6oJ54np7BOa2QZm5YewVh6M0rLxus3o.png', 'pack'],
        ['Fanta 1lt 12li',                       'Fanta',      'Kola & Gazlı İçecek', 'Fanta PET şişe 1L × 12 adet.',                                        'https://vanguardgida.com.tr/images/v7r9OiYJxncbM_YghstdTpiWzt9yuGQqZAkQ2TDbNeI/_small/ks-prod/images/shop/6735a5592b654/product/68d1d62e31567/OyDYUeiiqnY5IcXYpHW7hioMEkg3QNe3mqaamygs.png', 'pack'],
        ['Pepsi 200ml İadesiz Şişe 24lü',        'Pepsi',      'Kola & Gazlı İçecek', 'Pepsi cam iadesiz şişe 200ml × 24 adet.',                              'https://vanguardgida.com.tr/images/o2g17SnBX1JkNph0WG3lGhQFU-3tJR4EQC5Wh5Gno58/_small/ks-prod/images/shop/6735a5592b654/product/6996d95c4730f/PEPS.png', 'pack'],

        // ─── SODA ────────────────────────────────────────────────────────────
        ['Beypazarı Sade Soda 24lü',                     'Beypazarı','Soda','Sade Beypazarı maden suyu, 24 adet koli.',                         'https://vanguardgida.com.tr/images/Cd7qJere4nTVPEwpvfJy9qNBNd-YXDqOMzs7RJxPaP0/_small/ks-prod/images/shop/6735a5592b654/product/6792c1a1d447d/HUXCDl47Tmu0wPwDmLsATydzySUBxWv7qnLVHiGI.png', 'pack'],
        ['Beypazarı Limonlu Soda 24lü',                  'Beypazarı','Soda','Limon aromalı Beypazarı sodası, 24 adet.',                         'https://vanguardgida.com.tr/images/sxZvoWIV0X76yEmw7zIN8gojj7egV7MKJPnbL2qOYoU/_small/ks-prod/images/shop/6735a5592b654/product/6792c20b29060/NwyWzfL8iR4ZNPTRfBTbO4tOEJIsG1WzOYvfhFyp.png', 'pack'],
        ['Beypazarı Elmalı Soda 24lü',                   'Beypazarı','Soda','Elma aromalı Beypazarı sodası, 24 adet.',                          'https://vanguardgida.com.tr/images/T2TNf7BRBMNn4u-tdChZBU9Z2r5-xiFaZBy7vY8H39M/_small/ks-prod/images/shop/6735a5592b654/product/6792c2ee826c9/x0vydjSmuA3ipPMVhQ1S1fWD0zm8bRaa2zRNofVw.png', 'pack'],
        ['Beypazarı Karadut Frenk Üzümlü Soda 24lü',    'Beypazarı','Soda','Karadut ve frenk üzümü aromalı soda, 24 adet.',                    'https://vanguardgida.com.tr/images/ee8t9dddZcJU2HpFqebaEGHPVtfVgf89boBglUQUnOg/_small/ks-prod/images/shop/6735a5592b654/product/67d883cf84e8b/6yHcUJ6arhbtCXZ39dcu8HFp0MVFiFmDIG5rV4gW.png', 'pack'],
        ['Beypazarı Mango Ananas Soda 24lü',             'Beypazarı','Soda','Mango ve ananas aromalı soda, 24 adet.',                           'https://vanguardgida.com.tr/images/4NevsBNiVnh6flzhgI4Ow0nP7XSkEMEFijjWYxj1c08/_small/ks-prod/images/shop/6735a5592b654/product/67d883cd852eb/uFbPFKTOl9KdbBRkQjSN1Zm1YnRmX8keHzLgEPyO.png', 'pack'],
        ['Beypazarı Narlı Soda 24lü',                    'Beypazarı','Soda','Nar aromalı Beypazarı sodası, 24 adet.',                           'https://vanguardgida.com.tr/images/2yxi3p1pxBo8Y_9w45NSp6MHxurreXT9aqbt4SaWi2s/_small/ks-prod/images/shop/6735a5592b654/product/6792c293b0e52/jQqcJKTI4aSk29afiVQwDbKc1sIZiTWISkkF1Nxl.png', 'pack'],
        ["Beypazarı Co'ala Lime Bah 24lü",               'Beypazarı','Soda',"Lime ve baharatlı aromalı Co'ala soda, 24 adet.",                  'https://vanguardgida.com.tr/images/0QyW4ZRsP8K2hEetIQoF9G3DEccyQRfh80h3uUR53wQ/_small/ks-prod/images/shop/6735a5592b654/product/68348a3c52f57/JhJIHj2l0CDFAwdzztFArTBmsNltZpkrJudIBL4w.png', 'pack'],
        ['Sultan Sade Soda 24lü',                        'Sultan',   'Soda','Sade Sultan maden suyu, 24 adet koli.',                            'https://vanguardgida.com.tr/images/ynI9rgnwLoq5ryQNm7p7hKHB-7VUw4nRJj1w3uBFL-o/_small/ks-prod/images/shop/6735a5592b654/product/676d1dc6eff99/disFfa96DipQAcC8wiqK70sLWUgGbOyfyPjmjtDz.jpg', 'pack'],
        ['Kınık Sade Soda',                              'Kınık',    'Soda','Türkiye\'de geniş hayran kitlesine sahip doğal soda.',             'https://vanguardgida.com.tr/images/GUVEb_TKgZaMjz4kPXZk5VGG_xeBhcPhwYEb77E91cg/_small/ks-prod/images/shop/6735a5592b654/product/6737e2918a7fc/QGHEmY7KZRmUZQ6I3uchH40lDn7wy6qMuajj2thY.png', 'piece'],

        // ─── MEYVE SUYU ──────────────────────────────────────────────────────
        ['Juss Karışık Kutu 250ml 24lü',         'Juss',   'Meyve Suyu', 'Karışık meyve aromalı meyve suyu kutu 250ml × 24 adet.','https://vanguardgida.com.tr/images/TSR5oWq9Uja7NJeaJic2nfZVRzwlzJ6hRUvBDg5rgXY/_small/ks-prod/images/shop/6735a5592b654/product/6a05a4fe06793/s_Qd-BKlsGahgKJEzcKEGI.webp', 'pack'],
        ['Juss Şeftali Kutu 250ml 24lü',         'Juss',   'Meyve Suyu', 'Şeftali aromalı meyve suyu kutu 250ml × 24 adet.',      'https://vanguardgida.com.tr/images/Y3d3OnWU23k9g5x_62WztS11STVId_JtPlsFxyxkrKg/_small/ks-prod/images/shop/6735a5592b654/product/6a05a4dbcd052/s_-soRAeL3jY1tu7y09_QE.webp', 'pack'],
        ['Juss Kayısı Kutu 250ml 24lü',          'Juss',   'Meyve Suyu', 'Kayısı aromalı meyve suyu kutu 250ml × 24 adet.',       'https://vanguardgida.com.tr/images/1qa4jPOVAWrNeAc2C3d6L7qu-QXNOP2LzRfsUzYgV7A/_small/ks-prod/images/shop/6735a5592b654/product/6a05a4ba66e0e/s_ccZ-JY9oD-3H38rj2Ji6.webp', 'pack'],
        ['Juss Vişne Kutu 250ml 24lü',           'Juss',   'Meyve Suyu', 'Vişne aromalı meyve suyu kutu 250ml × 24 adet.',        'https://vanguardgida.com.tr/images/fCnxFe5CkPxC6aR1kk4THuRHqzoIkfv-VnsK0N5_RYI/_small/ks-prod/images/shop/6735a5592b654/product/6a05a48808e57/s__gJL8AJhbD8ybACo1821.webp', 'pack'],
        ['Juss Vişne Tetra 200ml 27li',          'Juss',   'Meyve Suyu', 'Vişne nektar tetra pak 200ml × 27 adet.',               'https://vanguardgida.com.tr/images/zRubSMUpy5ftRUXdy-3h_mTzBnan0PWz5m7RGQ1OZDM/_small/ks-prod/images/shop/6735a5592b654/product/6a05a4208a3f9/s_DsBKvjIFcfdkFDdnHi1C.webp', 'pack'],
        ['Juss Karışık Tetra 200ml 27li',        'Juss',   'Meyve Suyu', 'Karışık meyve tetra pak 200ml × 27 adet.',              'https://vanguardgida.com.tr/images/9d2nRkxFXtvHOwBi3QBVDAmXcSAav_l9tgADGvOeIn0/_small/ks-prod/images/shop/6735a5592b654/product/6a05a3f2520d0/s_FteMudoh74YGdi6ZfKop.webp', 'pack'],
        ['Juss Kayısı Tetra 200ml 27li',         'Juss',   'Meyve Suyu', 'Kayısı nektar tetra pak 200ml × 27 adet.',              'https://vanguardgida.com.tr/images/wXX7g3GERyUEjrgAUGV94zg9FWi-Fw1btlmvb5ECsh0/_small/ks-prod/images/shop/6735a5592b654/product/6a05a3cbc93d4/s_-YBvQkBhs_tirj1g9p5a.webp', 'pack'],
        ['Juss Şeftali Tetra 200ml 27li',        'Juss',   'Meyve Suyu', 'Şeftali nektar tetra pak 200ml × 27 adet.',             'https://vanguardgida.com.tr/images/Ai6rSqDw00vhhYHmngfrmBDjCYzTUO_DC7kpF0_h9Lc/_small/ks-prod/images/shop/6735a5592b654/product/6a05a372debda/s_oEAry3c3kRpSFTEv5FqV.webp', 'pack'],
        ['Juss Cam Şişe Şeftali Nektarı 24lü',  'Juss',   'Meyve Suyu', 'Cam şişede şeftali nektarı, 24 adet.',                 'https://vanguardgida.com.tr/images/NAoe9rrbAf_RXvnlJ1QyqQzZbT5ceHcmcr0ZrK0fs_4/_small/ks-prod/images/shop/6735a5592b654/product/69fb8c2cbaa03/s_HbN6mVsCBtkuEhhZFhrn.webp', 'pack'],
        ['Juss Cam Şişe Vişne Nektarı 24lü',    'Juss',   'Meyve Suyu', 'Cam şişede vişne nektarı, 24 adet.',                   'https://vanguardgida.com.tr/images/XxeUldBorWj7unWZ6kyGfNpEx3gt5zKjygd5b-n4N80/_small/ks-prod/images/shop/6735a5592b654/product/69fb8bfaacb5d/s_6R7hnh9dJ_d1BSkqbCMz.webp', 'pack'],
        ['Juss Cam Şişe Karışık Nektarı 24lü',  'Juss',   'Meyve Suyu', 'Cam şişede karışık meyve nektarı, 24 adet.',           'https://vanguardgida.com.tr/images/W5mzDlIVG93PwuRvvnun6sMOhYbL6SAyLN3AKrV7k_Q/_small/ks-prod/images/shop/6735a5592b654/product/69fb8b97ecee6/s_9QBgbuzVVz_bSG_sp3Yp.webp', 'pack'],
        ['Juss Cam Şişe Kayısı Nektarı 24lü',   'Juss',   'Meyve Suyu', 'Cam şişede kayısı nektarı, 24 adet.',                  'https://vanguardgida.com.tr/images/yXlLegNpZ74fnmSaFQKmJucpJMSBLPtehc7N7yousfY/_small/ks-prod/images/shop/6735a5592b654/product/6a065a0bb1879/s_9QBgbuzVVz_bSG_sp3Yp.png', 'pack'],
        ['Juss Şeftali Meyve İçeceği 12li',     'Juss',   'Meyve Suyu', 'Şeftali aromalı meyve içeceği, 12 adet.',             'https://vanguardgida.com.tr/images/Gk_NybHjjElLmv-7so1KRWSf3vmyOVZ2ismwU9fA3fg/_small/ks-prod/images/shop/6735a5592b654/product/69fb8947ba631/s_GRYh_L7gIMFbHNUIyhvJ.webp', 'pack'],
        ['Juss Kayısı Meyve İçeceği 12li',      'Juss',   'Meyve Suyu', 'Kayısı aromalı meyve içeceği, 12 adet.',              'https://vanguardgida.com.tr/images/o3r2HxZp5wwZ_om9Xs92v6ngFUT5LeKGJxFP_W9bbu4/_small/ks-prod/images/shop/6735a5592b654/product/69fb8922a9b07/s_F-kH5qPKC7prg6MHY9LD.webp', 'pack'],
        ['Juss Karışık Meyve İçeceği 12li',     'Juss',   'Meyve Suyu', 'Karışık meyve aromalı içecek, 12 adet.',              'https://vanguardgida.com.tr/images/1S9fbrnyRKVb0kEiF-tSiM4sZVFytN34JCGeRZUfEkc/_small/ks-prod/images/shop/6735a5592b654/product/69fb88fd8ac9e/s_CIpCJnz-sBL9-icja2nV.webp', 'pack'],
        ['Juss Vişne Meyve İçeceği 12li',       'Juss',   'Meyve Suyu', 'Vişne aromalı meyve içeceği, 12 adet.',               'https://vanguardgida.com.tr/images/TEOc1jv1Errvot6Nm_oE5blxXiadItsgrMpI3EGCeSU/_small/ks-prod/images/shop/6735a5592b654/product/69fb87f33d7fe/s_KgSSc8Fuq7P19qCrP1y1.webp', 'pack'],
        ['Meysu Slim Kayısı Nektarı 27li',      'Meysu',  'Meyve Suyu', 'Slim paket kayısı nektar 200ml × 27 adet.',            'https://vanguardgida.com.tr/images/gn8knq0-AK-b1BIfK3UB2SWBAWVpWiPifUkBvJPOB5g/_small/ks-prod/images/shop/6735a5592b654/product/67916e8d99863/qJBvYALdVYdqzGdQAqB4MNEtuAIlXs3119hrkZsG.jpg', 'pack'],
        ['Meysu Slim Şeftali Nektarı 27li',     'Meysu',  'Meyve Suyu', 'Slim paket şeftali nektar 200ml × 27 adet.',           'https://vanguardgida.com.tr/images/IKvm-wTbkkPTFdvkjIE-GTGjoKUBMyp9XMhfmvMJ704/_small/ks-prod/images/shop/6735a5592b654/product/67916e987303c/hPJygITMP3vLHDaTJjvLinXN8rLUDKc8V9334heq.jpg', 'pack'],
        ['Meysu Slim Karışık Nektarı 27li',     'Meysu',  'Meyve Suyu', 'Slim paket karışık meyve nektar 200ml × 27 adet.',     'https://vanguardgida.com.tr/images/nzjLu9cfFVtC3nap_80Fc2z7aa6Ury0iZjOJVVSk0N0/_small/ks-prod/images/shop/6735a5592b654/product/67916ee028cba/LbV27ibbhE3NCuEm9019kaCTzcbnkEUeHdBqPExw.jpg', 'pack'],
        ['Meysu Slim Vişne Meyve İçeceği 27li', 'Meysu',  'Meyve Suyu', 'Slim paket vişne meyve içeceği 200ml × 27 adet.',     'https://vanguardgida.com.tr/images/fq8CpDYJZKdvYoh2cGShHvjM5PIWRKmtH5gBFMX_0EA/_small/ks-prod/images/shop/6735a5592b654/product/67916f07afe23/mb9OaByTqsswcnQkXpzZAyJkKf9fHPJDOza6GOBY.jpg', 'pack'],
        ['Kardan Karadut Bardak 250cc',         'Kardan', 'Meyve Suyu', 'Bardakta karadut içeceği, 250cc.',                    'https://vanguardgida.com.tr/images/RSFMhBfZclbtymW4E08QpD_rEF_Y8m8jE5JypfVQKP0/_small/ks-prod/images/shop/6735a5592b654/product/6836344a6bcb0/9mxaHtKjk8dXVIDbaKn2nmVEOJKJZKXbdvYaHRC8.png', 'piece'],
        ['Kardan Limonata Bardak 250cc',        'Kardan', 'Meyve Suyu', 'Bardakta limonata, 250cc.',                           'https://vanguardgida.com.tr/images/aGogmWKhcJw04URPBf0N4cMsC_YyEoBmi2Od6TES-54/_small/ks-prod/images/shop/6735a5592b654/product/683633fc2477b/yxaVkSHnMGo3r3VGN3WSOWPcYScNhCxiouvXzLRU.png', 'piece'],
        ['Tarihi Odunpazarı Karadutlu İçecek 3lt','Tarihi Odunpazarı','Meyve Suyu','Geleneksel tarife ile hazırlanmış karadut içeceği, 3 litre.','https://vanguardgida.com.tr/images/Qx38fCx02Fk70BgNhuKqtbTHOjCrrlhyYT6L6VbgvTo/_small/ks-prod/images/shop/6735a5592b654/product/67eef69a5541e/5xktT5PfBOl2XaZ40nRjKVZGG1tZwCYteksaoO8M.png', 'piece'],
        ['Tarihi Odunpazarı Limonata 3lt',     'Tarihi Odunpazarı','Meyve Suyu','Geleneksel tarife ile hazırlanmış limonata, 3 litre.',     'https://vanguardgida.com.tr/images/v3ian1mjl6pbMXt56mZvEtGOIQqkHwY_PB2CyOjSm9Q/_small/ks-prod/images/shop/6735a5592b654/product/67eef6851a9a5/NEyyyl4e1bKRsPo4asXBuLdXtJEJn6fj917uAxkH.png', 'piece'],
        ['Kardelen Limonata 250ml 12li',        'Kardelen','Meyve Suyu', 'Kardelen markalı taze limonata 250ml × 12 adet.',     'https://vanguardgida.com.tr/images/gFcXwGk6HAD2rIsmAR1xd3GMQ_hqQuy4uCrhEXXheMM/_small/ks-prod/images/shop/6735a5592b654/product/698f3f7006be8/K4PvmODz1O0sGuIYxD2w5mUhQnrrQv8ENJClsjK6.png', 'pack'],
        ['Kardelen Karadut İçeceği 250ml 12li', 'Kardelen','Meyve Suyu', 'Kardelen markalı karadut meyveli içecek 250ml × 12 adet.', 'https://vanguardgida.com.tr/images/28tuEoUNqeasRthDF_9YMa3TqyDDWlF_xWB9tUZAZRA/_small/ks-prod/images/shop/6735a5592b654/product/698f401141bc7/BOOM_KARADUT-removebg-preview.png', 'pack'],
        ['Kardelen Çilek İçeceği 250ml 12li',   'Kardelen','Meyve Suyu', 'Kardelen markalı çilek aromalı içecek 250ml × 12 adet.',   'https://vanguardgida.com.tr/images/3Vg2qfHY0Do_7dAG3FtBdtb-klDoC1i3tI1J3-HXBwU/_small/ks-prod/images/shop/6735a5592b654/product/698f3fdf61ddc/BOOMLEK.png', 'pack'],
        ['Pınar Limonata 330ml 12li',           'Pınar',  'Meyve Suyu', 'Pınar limonata 330ml × 12 adet.',
            'https://vanguardgida.com.tr/images/Lj811tTB38IjaCn9zW0oRg9bf8ykLx0R2FAlpJsT21I/_small/ks-prod/images/shop/6735a5592b654/product/6830454a806b3/B2QHlFAh097Ktx92CTQwSs8uVSJQqHD3SjQnCCk6.png', 'pack'],
        ['Pınar Limonata 1lt 12li',             'Pınar',  'Meyve Suyu', 'Pınar limonata 1L × 12 adet.',
            'https://vanguardgida.com.tr/images/itqu-N7P_CBu92D4AylUENi2dBpTGht4vN0Ji9RNjtY/_small/ks-prod/images/shop/6735a5592b654/product/683042fdb8eef/3VvcKkSblp41cmQRfuDGML3Xza1dNJZHixg4kA5G.png', 'pack'],
        ['Pınar Cam Şişe Limonata 250ml 24lü',  'Pınar',  'Meyve Suyu', 'Cam şişede Pınar limonata 250ml × 24 adet.',
            'https://vanguardgida.com.tr/images/6n9j1S3yrjyCYkbNfmglo1xnfT7ZfPfkdaYkYubdzME/_small/ks-prod/images/shop/6735a5592b654/product/683049a22e028/PnZm0y7v6PkQE1tgMIjRsFd8jn4MEMA3M88b0L7V.png', 'pack'],

        // ─── SU ──────────────────────────────────────────────────────────────
        ['Kardelen Su 330ml Pet 24lü',  'Kardelen','Su','Kardelen doğal kaynak suyu 330ml PET × 24 adet.',       'https://vanguardgida.com.tr/images/F9Mj_viExNum1h8H3oBbFT4V0Eqw1vHZwfQ54hsNgAc/_small/ks-prod/images/shop/6735a5592b654/product/679913d565120/WAFIrujDI1YTPF7E5JUSVpRJU2XRF2TgTrJLgrgK.png', 'pack'],
        ['Kardelen Su 500ml 24lü',      'Kardelen','Su','Kardelen doğal kaynak suyu 500ml × 24 adet.',           'https://vanguardgida.com.tr/images/XPYFtOD7i0r_SbzSu3XLDW_qVM-kZ0Y55k07cciP0r4/_small/ks-prod/images/shop/6735a5592b654/product/679911fd6d458/uXXGMcy1s81ZwsdNW2PKRiMo9VmZK58GJIGKYrqe.png', 'pack'],
        ['Kardelen Su 1lt Pet 12li',    'Kardelen','Su','Kardelen doğal kaynak suyu 1L PET × 12 adet.',          'https://vanguardgida.com.tr/images/oVTx79dx4hh9ZG-870B4elInDHh2nJS1-cVeN7Md5Dc/_small/ks-prod/images/shop/6735a5592b654/product/6799132cd6ae4/fCn8I0k0xodHfrB0JuGLnfZijWoSOya4dadMcnHq.png', 'pack'],
        ['Kardelen Su 1.5lt Pet 12li',  'Kardelen','Su','Kardelen doğal kaynak suyu 1.5L PET × 12 adet.',        'https://vanguardgida.com.tr/images/vtr73f76Oj6IP5Om3qZ-ZrbqsxCwZD62ucLJRqMSS0E/_small/ks-prod/images/shop/6735a5592b654/product/6799144d4670c/StQBIna7bTlnOOMM8h4Ley9SSbPSAezxazYEejUw.png', 'pack'],
        ['Kardelen Su 5lt Pet 4lü',     'Kardelen','Su','Kardelen doğal kaynak suyu 5L PET × 4 adet.',           'https://vanguardgida.com.tr/images/YJZayxxFpCSXAqRUtFEWWbjJyKEBFHf_YDkjBU81PHk/_small/ks-prod/images/shop/6735a5592b654/product/679914c3da643/BUlqQ6w1FuRgWckZZfnqdCpnm5RC04YYMi40R8IV.png', 'pack'],
        ['Kardelen Su 19lt',            'Kardelen','Su','Kardelen doğal kaynak suyu damacana 19 litre.',          'https://vanguardgida.com.tr/images/Gbnix4tssG2sSwC41IJFx4fb3Q936421dswJbvLd7zo/_small/ks-prod/images/shop/6735a5592b654/product/679915c4e670a/UaZfUDqcJDjcu4FamyQNDUrdotuRrS5PlmnadpqW.png', 'piece'],
        ['Kardelen Su 200ml Bardak 72li','Kardelen','Su','Bardakta kaynak suyu 200ml × 72 adet.',                'https://vanguardgida.com.tr/images/5A2eukOjCI_p6SnpUAUbty6lPDOPnwIwDwFLEWXPpNQ/_small/ks-prod/images/shop/6735a5592b654/product/67990fbd7f10e/d7s784eRLcU11b37p7WLvWP1TtHtCrUqYMfjGvjk.png', 'pack'],
        ['Sultan Doğal Kaynak Suyu 0.5lt 2×12','Sultan','Su','Sultan doğal kaynak suyu 500ml, koli içi 2×12 adet.',
            'https://vanguardgida.com.tr/images/D7nVLktR1T7HAhMXA5d9SMMZjpG6DEsDd0gHMAKJ6i8/_small/ks-prod/images/shop/6735a5592b654/product/6737d8b24aca5/Q6cmhwtOwzcbY8xLJjOcKQHJWDhHaFqIRz2OtlYq.jpg', 'pack'],
        ['Sultan Doğal Kaynak Suyu 1.5lt 2×6','Sultan','Su','Sultan doğal kaynak suyu 1.5L, koli içi 2×6 adet.',
            'https://vanguardgida.com.tr/images/1xz_LS_Ws5NdQYN8r952QuDRq4A4ZAetxuW_qvL5JxI/_small/ks-prod/images/shop/6735a5592b654/product/6737d95057883/WPCmvUZLmDtkqgAJWfdAya5NWQKfZk8OKFBCHfke.jpg', 'pack'],
        ['Sultan Doğal Kaynak Suyu 5lt 4lü','Sultan','Su','Sultan doğal kaynak suyu 5L, koli içi 4 adet.',
            'https://vanguardgida.com.tr/images/s-zITOc_ES8UoVqTopvrnKIOdMLwY_2VOOQjVJJbHcQ/_small/ks-prod/images/shop/6735a5592b654/product/6737db1381dec/dedXyJsfmKQcPHtfFawPLYTElHUHrq93aJ3iDCG1.jpg', 'pack'],
        ['Pınar Sporcu Kapaklı Su 750ml 12li','Pınar','Su','Sporcu tipi kapaklı Pınar su 750ml × 12 adet.',
            'https://vanguardgida.com.tr/images/mIb1v-RZWSssLQe8LczlLqxN8nozr0CSm8BugVmfo5Y/_small/ks-prod/images/shop/6735a5592b654/product/68355b959ab1b/2gE3CB9lfhvJEiUHz1YCe7yBZCO3GfHMQj5DxdOl.png', 'pack'],

        // ─── ENERJİ İÇECEĞİ ──────────────────────────────────────────────────
        ['Max Fly Enerji İçeceği Classic 250ml 24lü', 'Max Fly', 'Enerji İçeceği','Klasik formül Max Fly enerji içeceği 250ml × 24 adet.',    'https://vanguardgida.com.tr/images/FHnNC4adlD1MAg0D6sP5l79Ip6tzJFckWie2o917ufw/_small/ks-prod/images/shop/6735a5592b654/product/689e6b7caf0c0/E8FtatqwT1J7tY3oM6cDJIgaa0alOLgGRDX6mJ4q.png', 'pack'],
        ['Max Fly Enerji İçeceği Mojito 250ml 24lü',  'Max Fly', 'Enerji İçeceği','Mojito aromalı Max Fly enerji içeceği 250ml × 24 adet.',  'https://vanguardgida.com.tr/images/GMmMrxgInCFvAzD26nIwf9r0Mx-ynWPuSAgcquB32VI/_small/ks-prod/images/shop/6735a5592b654/product/689e6bd96d138/nlIVWbrs5gwnZhQXWP3AAIXSE1tSs9OdWNwJJUT1.png', 'pack'],
        ['Max Fly Enerji İçeceği Sirius 250ml 24lü',  'Max Fly', 'Enerji İçeceği','Sirius serisi Max Fly enerji içeceği 250ml × 24 adet.',   'https://vanguardgida.com.tr/images/X-IjQe7zGfgCx2ZxHK9yeZ0Abkl2K7QzTNYFNHYUPqU/_small/ks-prod/images/shop/6735a5592b654/product/689e6c69d701b/DlO7JzuWL6FQswuUyHcwJFxdZB2GwGQD37AJZSXS.png', 'pack'],
        ['Max Fly Enerji İçeceği Vega 250ml 24lü',    'Max Fly', 'Enerji İçeceği','Vega serisi Max Fly enerji içeceği 250ml × 24 adet.',     'https://vanguardgida.com.tr/images/LUV-iEVAVSGpnuONjQUp8y1Tdb0rH6krkUZonWANNIM/_small/ks-prod/images/shop/6735a5592b654/product/689e6c2303675/0jTEOARIhr2qwNMvPXcR4ACZylP3s80axbDIdGWJ.png', 'pack'],
        ['Max Fly Enerji İçeceği Nova 250ml 24lü',    'Max Fly', 'Enerji İçeceği','Nova serisi Max Fly enerji içeceği 250ml × 24 adet.',     'https://vanguardgida.com.tr/images/sPCEy7tCw_2ekhfE3uPwlbXG4lLckwNv5C5MKhjn4ZI/_small/ks-prod/images/shop/6735a5592b654/product/689e6c46825aa/rtJSXfNHc2h6tBAuXabeFrhAjnvbsJR5QjakxjNT.png', 'pack'],
        ['Max Fly Enerji İçeceği Cosmos 250ml 24lü',  'Max Fly', 'Enerji İçeceği','Cosmos serisi Max Fly enerji içeceği 250ml × 24 adet.',   'https://vanguardgida.com.tr/images/9cP1gCel7n0iU6r3L3F0KFJigg4bE63vk4PQufwd7-E/_small/ks-prod/images/shop/6735a5592b654/product/689e6bfbbc632/IfYouLGEGH94K6ZJhekdF7dVoMv17h53GKAHVXdb.png', 'pack'],
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
        ['Doğanay Tatlı Şalgam 1lt 12li',     'Doğanay','Şalgam','Tatlı şalgam suyu 1L PET × 12 adet.',                              'https://vanguardgida.com.tr/images/vnoFNUF-yR-BMqk_K6ggWc8VW-Vy5ISGZtW-0WdxCEM/_small/ks-prod/images/shop/6735a5592b654/product/68c4a699c2d30/OULLk4uqz3OmzUJ7IHk4dFSLxfOzdBxrNCuADRbI.png', 'pack'],
        ['As01 300ml Acısız Şalgam 24lü',     'As01',   'Şalgam','As01 marka acısız şalgam suyu 300ml × 24 adet.',
            'https://vanguardgida.com.tr/images/zvmZQ7O5fuNQvjO8hHnpBJTQN1i5wH4Ygf3JUBhtV9c/_small/ks-prod/images/shop/6735a5592b654/product/69f8843395cf5/WhatsApp_Image_2026-05-04_at_14.31.21-removebg-preview.png', 'pack'],
        ['As01 300ml Acılı Şalgam 24lü',      'As01',   'Şalgam','As01 marka acılı şalgam suyu 300ml × 24 adet.',
            'https://vanguardgida.com.tr/images/lrlaMTbU-nOLYFC09ggNx3P-lVj_bGx_whgt7aJmfqg/_small/ks-prod/images/shop/6735a5592b654/product/69f883d09e6bc/WhatsApp_Image_2026-05-04_at_14.31.21-removebg-preview.png', 'pack'],
        ['As01 3lt Acılı Şalgam 6lı',         'As01',   'Şalgam','As01 büyük boy acılı şalgam suyu 3L × 6 adet.',
            'https://vanguardgida.com.tr/images/IIgDkAzmcpx0ensgJJ6tSgzQ7D3n76X0HFL_ir0PjI/_small/ks-prod/images/shop/6735a5592b654/product/69f884bba6e34/WhatsApp_Image_2026-05-04_at_14.31.21-removebg-preview.png', 'pack'],

        // ─── KAHVE ───────────────────────────────────────────────────────────
        ['Max Brew Latte Soğuk Kahve 250ml 12li',    'Max Brew',      'Kahve','Latte aromalı soğuk kahve 250ml × 12 adet.',             'https://vanguardgida.com.tr/images/QoErQuA7ro9AljDhwqGoPZGEat5dDzjE8APIzCOPPZM/_small/ks-prod/images/shop/6735a5592b654/product/689e6ab99dea1/O7K2TGKoUKdALN2yysCOCGSoD95gReNFOAqrbv4y.png', 'pack'],
        ['Max Brew Caramel Soğuk Kahve 250ml 12li',  'Max Brew',      'Kahve','Karamel aromalı soğuk kahve 250ml × 12 adet.',           'https://vanguardgida.com.tr/images/u61szBq7DWLWaTEwSZDZsPrlb6N3eiHmy0IUww4uUlA/_small/ks-prod/images/shop/6735a5592b654/product/689e6af89be12/tkoy7p4HoRMl7ihZjNICBiqXGKKpRpZX4nZ739EN.png', 'pack'],
        ['Max Brew Mocha Soğuk Kahve 250ml 12li',    'Max Brew',      'Kahve','Mocha aromalı soğuk kahve 250ml × 12 adet.',             'https://vanguardgida.com.tr/images/tdYr9YjK1euEgUpgRX_6m0cNis-hO8jr62T3armsXoc/_small/ks-prod/images/shop/6735a5592b654/product/689e6b1a110e1/QFGTXEyspVMsJ4V0Xdbzo3PQ5wJVQ5wyV4hdBkrX.png', 'pack'],
        ['Nescafe 3+1 Arada 10gr 56lı',             'Nescafe',       'Kahve',"Nescafe 3'ü 1 arada hazır kahve 10gr × 56 adet.",        'https://vanguardgida.com.tr/images/YmfR5eXdrC8Z1gMXiCqQZDD6kxdWajUJ5Dp8_IUF8hQ/_small/ks-prod/images/shop/6735a5592b654/product/697091568a101/1X0nBeJ6COdIktWrLpHrFI0cXZ4TUqNOkAUZqhpd.jpg', 'pack'],
        ['Nescafe 3+1 Süt Köpük 17.4gr 48li',       'Nescafe',       'Kahve','Süt köpüklü Nescafe 3+1 17.4gr × 48 adet.',              'https://vanguardgida.com.tr/images/yU22Xf_jom9hvTwlqXn6Bd3pnNEhI3nTd0LZLLzoMFk/_small/ks-prod/images/shop/6735a5592b654/product/697090c53408b/2i2ugc8maFLjQQvJN3N7E9Uw9fKtyVC2B6tvbHbH.jpg', 'pack'],
        ['Nescafe 2+1 Arada 10gr 56lı',             'Nescafe',       'Kahve',"Nescafe 2'si 1 arada hazır kahve 10gr × 56 adet.",       'https://vanguardgida.com.tr/images/cs-J28TLlt7EGizdYm37WkEm1FQY48Mwy0ZCtiRbOeY/_small/ks-prod/images/shop/6735a5592b654/product/68c3eb563ce68/RYVO7Wtd5K3Hrh9KMt1PbqdOXJn4H5iHohf1qMz1.jpg', 'pack'],
        ['My Coffee Latte 250ml 24lü',               'My Coffee',     'Kahve','Latte aromalı soğuk kahve 250ml × 24 adet.',             'https://vanguardgida.com.tr/images/8TRjEVgETOEQ8NcLO2yyE2ZEjxhgertrMZRKWuht1aQ/_small/ks-prod/images/shop/6735a5592b654/product/67e591698835b/NYLSJsTj5XxWQFo2bdMVCoiqOknCr1Hd2IxexanB.png', 'pack'],
        ['My Coffee Caramel 250ml 24lü',             'My Coffee',     'Kahve','Karamel aromalı soğuk kahve 250ml × 24 adet.',           'https://vanguardgida.com.tr/images/b2UOKeRxcgd7kAvvq-dzTlx9Nr2NOw0GilCnyyo8OL0/_small/ks-prod/images/shop/6735a5592b654/product/67e5915d63bc7/QsAG0zNSzmTS92NIBs0MzUVUU2yBGRTWvLCq1Oks.png', 'pack'],
        ['Kahve Dünyası Orta Kavrulmuş 100gr 12li',  'Kahve Dünyası', 'Kahve','Orta kavrulmuş filtre kahve 100gr × 12 adet.',          'https://vanguardgida.com.tr/images/M-yc7ifdELljj1UlYtsdwa5uk2oE24t475YAJnrbNf0/_small/ks-prod/images/shop/6735a5592b654/product/68c3ebe310126/AzkTsmsUBoOkDsE61y6V13xGXIohAh6B9boFKggC.png', 'pack'],
        ["Kahve Dünyası 3'ü 1 Arada 40lı Paket",    'Kahve Dünyası', 'Kahve',"Hazır 3'ü 1 arada kahve paketi, 40 adet.",              'https://vanguardgida.com.tr/images/0yy246gXk1NsSbm92WLiSJGQ7AUaB1oniNhvmuC1LCc/_small/ks-prod/images/shop/6735a5592b654/product/67dd1689be44b/YJU8CGk1uiCPIAQLgphuDcXYOCBiyIk6x2306I0H.jpg', 'pack'],
        ["Kahve Dünyası 2'si 1 Arada 40lı Paket",   'Kahve Dünyası', 'Kahve',"Hazır 2'si 1 arada kahve paketi, 40 adet.",             'https://vanguardgida.com.tr/images/VKI8bn5rgN8mfae6OCNnESBuSN7aVtToPMggxIYxGRA/_small/ks-prod/images/shop/6735a5592b654/product/67dd15da02694/LEdAqwbwYqbuAsmgEbfmG4fJGxYJ7SPMYHsxusdP.jpg', 'pack'],
        ["KD 2'si 1 Arada 192li",                   'Kahve Dünyası', 'Kahve',"Toplu ekonomik paket 2'si 1 arada kahve, 192 adet.",    'https://vanguardgida.com.tr/images/trtwr4OcoO2dFZDA5QipThIlYmBRxm94XYAPakAeYu4/_small/ks-prod/images/shop/6735a5592b654/product/676c85388c63a/pdHpJ5JtAVLuM7dabnaIR4cdcr9QQwfnMNmxwHli.jpg', 'pack'],
        ["KD 3'ü 1 Arada 192li",                    'Kahve Dünyası', 'Kahve',"Toplu ekonomik paket 3'ü 1 arada kahve, 192 adet.",    'https://vanguardgida.com.tr/images/tvTTikexfnSI7MBCj6a5Uka1dnqaqeL24HhINV2TVF0/_small/ks-prod/images/shop/6735a5592b654/product/676bc32561b27/WXywpIsUbS9rkhFYfTbe1b6ghRUZm0wPZ0y0bcCZ.jpg', 'pack'],

        // ─── KETÇAP & MAYONEZ ─────────────────────────────────────────────────
        ['Pınar Mayonez Servis 700gr', 'Pınar','Ketçap & Mayonez','Pınar servis boy mayonez, 700 gram.',
            'https://vanguardgida.com.tr/images/rTE77HtYn4mwoEoiuUcd6xvMJEi0BB0StF1hEhqzLo8/_small/ks-prod/images/shop/6735a5592b654/product/69245e4e313cb/30Jnk61Aie3vZZAP0sC3ZavwGG4ssjAkRp2J45AN.png', 'piece'],
        ['Pınar Ketçap Servis 800gr',  'Pınar','Ketçap & Mayonez','Pınar servis boy ketçap, 800 gram.',
            'https://vanguardgida.com.tr/images/H6BR7SSIIWmhVXY4M1XMjMoFu7HypW4ridAN0pT3p3k/_small/ks-prod/images/shop/6735a5592b654/product/69245e4c228e2/ZYNtdR1LTmKlCgoEJ5FcA64hZdwwF2YHprY1qVpD.png', 'piece'],

        // ─── SOĞUK ÇAY ────────────────────────────────────────────────────────
        ['Didi Soğuk Çay Şeftali 250ml 24lü',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 250ml × 24 adet.',   'https://vanguardgida.com.tr/images/Gz-3t1wfCTHYpPHLSC1t4z46mLgtQqR8KQ7KwB9EUGA/_small/ks-prod/images/shop/6735a5592b654/product/676a75a28445f/9zhA5dUP1Iur81WHSNmO7c3SrpgIvTsS94GvZXB2.jpg', 'pack'],
        ['Didi Soğuk Çay Çilek 250ml 24lü',     'Didi','Soğuk Çay','Çilek aromalı Didi soğuk çay 250ml × 24 adet.',     'https://vanguardgida.com.tr/images/hAUOaxjt7ZEz9QlYehcFFZfykYgpqYHjP6dzFM8C600/_small/ks-prod/images/shop/6735a5592b654/product/676a75d3e0236/tViXPkhtpEPTAfEaGOe2quDOmslNDGPrilj9XSuO.jpg', 'pack'],
        ['Didi Soğuk Çay Limon 250ml 24lü',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 250ml × 24 adet.',     'https://vanguardgida.com.tr/images/l37xvTFtdPiwnasleGzYJlyCYd8QZMe8NqggXvnotAQ/_small/ks-prod/images/shop/6735a5592b654/product/67e319f3711f7/5MiKy17R7eBfYnxj4adb9MLDphSj09wpVx0pphaU.png', 'pack'],
        ['Didi Soğuk Çay Bergamot 250ml 24lü',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 250ml × 24 adet.',  'https://vanguardgida.com.tr/images/jON--iAtRgsG3aKrNZzgxKhdWu2S_hiaFE6xKU9q6C8/_small/ks-prod/images/shop/6735a5592b654/product/67e31a30c8bc8/zKcxzLHPUO6rI9KoBOWwVE73AifI7CJv9FU3X2hx.png', 'pack'],
        ['Didi Soğuk Çay Şeftali 330ml 24lü',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 330ml × 24 adet.',   'https://vanguardgida.com.tr/images/uNUhp5LYhLi0zBSpX9hT8pjpWieUYq7rGGiaa_3_YRA/_small/ks-prod/images/shop/6735a5592b654/product/67e31c063d182/l3EGKb8oo8ViJJRKhBZGoNZ8HFDE9nI55CU3Zv5j.png', 'pack'],
        ['Didi Soğuk Çay Limon 330ml 24lü',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 330ml × 24 adet.',     'https://vanguardgida.com.tr/images/-6rPLNlkIoygDtuZHPCAj6IE7xp54tTykxrZt1I-Gqs/_small/ks-prod/images/shop/6735a5592b654/product/67e31b7a67333/MbipRQbyabXNShmqd8p7UG9FXX5GgxQ0YvvqaLsK.png', 'pack'],
        ['Didi Soğuk Çay Bergamot 330ml 24lü',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 330ml × 24 adet.',  'https://vanguardgida.com.tr/images/Q5EBkihcYbKSSWjMVDu6vlRO8bXi2vuepISP-psA5AU/_small/ks-prod/images/shop/6735a5592b654/product/67e31b0835030/LnBKDdiiQdkYKkzZAfAInU7m1C6PeCDn848r24vl.png', 'pack'],
        ['Didi Soğuk Çay Bergamot 500ml 24lü',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 500ml × 24 adet.',  'https://vanguardgida.com.tr/images/p5fDCoz5tDZhGeJr9zPspyJG4taRHFuO2ZTgpM6-19k/_small/ks-prod/images/shop/6735a5592b654/product/67e31d0e3d74b/z1gKSZ10m0RddqDHbs97b9ovO7zcSd0XOaLTe7Eq.png', 'pack'],
        ['Didi Soğuk Çay Limon 500ml 24lü',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 500ml × 24 adet.',     'https://vanguardgida.com.tr/images/nsNhGtcPZG7Kxex_LgSVCKYzIw0pjiq5NASBriipwFs/_small/ks-prod/images/shop/6735a5592b654/product/67e31cbedf005/dtpRqsI6BZZxnknBvaxapFsuRDFaqN2MfcNdbb78.png', 'pack'],
        ['Didi Soğuk Çay Şeftali 500ml 24lü',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 500ml × 24 adet.',   'https://vanguardgida.com.tr/images/_sUB3-5sMioRuo6hS3aAUjMoJXQBpc09zkyeQAFtHow/_small/ks-prod/images/shop/6735a5592b654/product/67e31c4f14d8c/LYNedsruWAYWepfZiJ3IVEnopK1sIrusdR0cKsai.png', 'pack'],
        ['Didi Soğuk Çay Şeftali 1lt 12li',     'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 1L × 12 adet.',      'https://vanguardgida.com.tr/images/gtuTzrwCWWahDroEXJpV0xQ3KVtOUlmUOuTEhoBVueU/_small/ks-prod/images/shop/6735a5592b654/product/67e31dd6dad9b/oaK5Ye2YnKEa04ZBMp6s9z9YRAkuQaSe2OPSdzs6.png', 'pack'],
        ['Didi Soğuk Çay Limon 1lt 12li',       'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 1L × 12 adet.',        'https://vanguardgida.com.tr/images/F55CWub1Km5w9b0R87WDq1GiB1yq4df3dhVRrvkRi1Y/_small/ks-prod/images/shop/6735a5592b654/product/67e31d9e088e8/27so9h2ehygCfPjifUuonwIhXU5IJGeNB9YC7OZN.png', 'pack'],
        ['Didi Soğuk Çay Bergamot 1lt 12li',    'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 1L × 12 adet.',     'https://vanguardgida.com.tr/images/B6aQQGI_GYbgQOweE5CIChxO0Me6rw-AoqcnS_gy2_4/_small/ks-prod/images/shop/6735a5592b654/product/67e31d3a42952/A3mbxE5DQFDJmYGSafQcfolk13St4BcNZOlnwKzB.png', 'pack'],
        ['Didi Soğuk Çay Şeftali 1.5lt 12li',   'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 1.5L × 12 adet.',    'https://vanguardgida.com.tr/images/HgKrVI8hdsrVFxMeWO3YiTu0QqZh52_RCIhNb1BrKX8/_small/ks-prod/images/shop/6735a5592b654/product/67e31f0291d57/4Wy6u9o2f4ujW9d2vKFfDVXGBsWHqWez3yYUmBGt.png', 'pack'],
        ['Didi Soğuk Çay Limon 1.5lt 12li',     'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 1.5L × 12 adet.',      'https://vanguardgida.com.tr/images/5rqa0N67OXY-xNyoataBj7umrEfcW6zwKpPBv5iumoE/_small/ks-prod/images/shop/6735a5592b654/product/67e31f016389f/SptZLGBBzJRKOEFAYj4Yvr48k8LDKHAvfWGeRcxa.png', 'pack'],
        ['Didi Soğuk Çay Bergamot 1.5lt 12li',  'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 1.5L × 12 adet.',   'https://vanguardgida.com.tr/images/bf3qND5yvn4e7zQlbcMmESjwA_OjiwSDZGRTTJzlJBs/_small/ks-prod/images/shop/6735a5592b654/product/67e31e70eeae2/KcEIa94M0983HCKhHl2FxUhDXl0oDS7LOcncoYjs.png', 'pack'],
        ['Didi Soğuk Çay Bergamot 2.5lt 6lı',   'Didi','Soğuk Çay','Bergamot aromalı Didi soğuk çay 2.5L × 6 adet.',    'https://vanguardgida.com.tr/images/jRtMgNXzlIADKiCpWRGv9e5kT6cyZ06daepYcOhDax8/_small/ks-prod/images/shop/6735a5592b654/product/67e3202791b38/LM7wweiodWDSSKWNzPbATLLxwW1bw4gDkWlM0GcK.png', 'pack'],
        ['Didi Soğuk Çay Limon 2.5lt 6lı',      'Didi','Soğuk Çay','Limon aromalı Didi soğuk çay 2.5L × 6 adet.',       'https://vanguardgida.com.tr/images/QyNF1_A7uMtwCzLEVOLzXO1zRt8D6aW5G2UyK7BiG7w/_small/ks-prod/images/shop/6735a5592b654/product/67e3200590028/fQlzbE5g5TJNl01AQIOROjx3oWp5QrLJla6U9SBo.png', 'pack'],
        ['Didi Soğuk Çay Şeftali 2.5lt 6lı',    'Didi','Soğuk Çay','Şeftali aromalı Didi soğuk çay 2.5L × 6 adet.',     'https://vanguardgida.com.tr/images/0-OtYmGxkEgNwmAhnsvY5fpJ8_y7le81180dkMEoCIw/_small/ks-prod/images/shop/6735a5592b654/product/67e31f988e119/cGCk5HpC15ApSLnfvxC0sc0QZbPmiuPsHqKHPgFj.png', 'pack'],

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
