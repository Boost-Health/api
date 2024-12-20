<?php

namespace App\Filament\Resources\PromptResource\Pages;

use App\Filament\Resources\PromptResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePrompt extends CreateRecord
{
    protected static string $resource = PromptResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
