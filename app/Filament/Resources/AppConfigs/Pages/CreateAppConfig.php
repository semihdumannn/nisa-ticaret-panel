<?php

namespace App\Filament\Resources\AppConfigs\Pages;

use App\Filament\Resources\AppConfigs\AppConfigResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAppConfig extends CreateRecord
{
    protected static string $resource = AppConfigResource::class;
}
