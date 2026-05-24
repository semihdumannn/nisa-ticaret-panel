<?php

namespace App\Filament\Resources\AppConfigs\Pages;

use App\Filament\Resources\AppConfigs\AppConfigResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditAppConfig extends EditRecord
{
    protected static string $resource = AppConfigResource::class;

    protected function getHeaderActions(): array
    {
        return [DeleteAction::make()];
    }
}
