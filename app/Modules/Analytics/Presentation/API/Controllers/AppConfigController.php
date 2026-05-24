<?php

namespace App\Modules\Analytics\Presentation\API\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AppConfig;
use Illuminate\Http\JsonResponse;

class AppConfigController extends Controller
{
    /**
     * GET /api/v1/config
     * Public endpoint — returns typed config values for the mobile app.
     * Only exposes non-sensitive keys.
     */
    public function index(): JsonResponse
    {
        $allowedKeys = [
            'app_version_ios',
            'app_version_android',
            'force_update',
            'maintenance_mode',
            'logo_url',
            'splash_image_url',
            'primary_color',
            'secondary_color',
            'accent_color',
            'whatsapp_enabled',
            'whatsapp_number',
            'min_order_amount',
            'free_shipping_threshold',
        ];

        $configs = AppConfig::whereIn('key', $allowedKeys)
            ->get()
            ->mapWithKeys(fn (AppConfig $c) => [$c->key => $c->typedValue()]);

        return response()->json(['data' => $configs]);
    }
}
