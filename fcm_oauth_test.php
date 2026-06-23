<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Google OAuth2 ile access token al
$creds = json_decode(file_get_contents(base_path('database/firebase-credentials.json')), true);
echo "Service account: " . $creds['client_email'] . "\n";

// JWT oluştur
$header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
$now = time();
$payload = base64_encode(json_encode([
    'iss' => $creds['client_email'],
    'sub' => $creds['client_email'],
    'aud' => 'https://oauth2.googleapis.com/token',
    'iat' => $now,
    'exp' => $now + 3600,
    'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
]));

$signInput = $header . '.' . $payload;
$privateKey = openssl_pkey_get_private($creds['private_key']);
openssl_sign($signInput, $signature, $privateKey, 'SHA256');
$jwt = $signInput . '.' . base64_encode($signature);

// Token endpoint'e istek at
$ch = curl_init('https://oauth2.googleapis.com/token');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
        'assertion' => $jwt,
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
]);
$response = curl_exec($ch);
$data = json_decode($response, true);

if (isset($data['access_token'])) {
    echo "✅ OAuth2 access token alındı! (ilk 20 char): " . substr($data['access_token'], 0, 20) . "...\n";
} else {
    echo "❌ OAuth2 hatası: " . json_encode($data) . "\n";
}
