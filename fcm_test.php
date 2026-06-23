<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$token = DB::table('fcm_tokens')->value('token');
if (!$token) { echo "Token yok\n"; exit(1); }
echo "Token: " . substr($token, 0, 50) . "...\n";

$messaging = app(Kreait\Firebase\Contract\Messaging::class);

$tests = [
    ['Siparişiniz Alındı 🛒', 'ORD-20260609-00099 siparişiniz alındı, işleme koyuldu.', ['type' => 'order_placed', 'order_id' => '99']],
    ['Siparişiniz Onaylandı ✅', 'ORD-20260609-00099 onaylandı.', ['type' => 'order_status_updated', 'order_id' => '99', 'status' => 'confirmed']],
    ['Siparişiniz Yolda 🚚', 'ORD-20260609-00099 yola çıktı!', ['type' => 'order_status_updated', 'order_id' => '99', 'status' => 'on_the_way']],
];

foreach ($tests as $i => [$title, $body, $data]) {
    try {
        $message = Kreait\Firebase\Messaging\CloudMessage::new()
            ->withToken($token)
            ->withNotification(Kreait\Firebase\Messaging\Notification::create($title, $body))
            ->withData($data);
        $result = $messaging->send($message);
        echo "✅ Test " . ($i+1) . ": $title\n";
        sleep(1);
    } catch (Throwable $e) {
        echo "❌ Test " . ($i+1) . ": " . $e->getMessage() . "\n";
    }
}
echo "\nTüm testler tamamlandı!\n";
