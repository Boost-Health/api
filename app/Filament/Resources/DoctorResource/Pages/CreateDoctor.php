<?php

namespace App\Filament\Resources\DoctorResource\Pages;

use App\Enums\UserType;
use App\Filament\Resources\DoctorResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDoctor extends CreateRecord
{
    protected static string $resource = DoctorResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['type'] = UserType::DOCTOR;
        $data['is_onboarded'] = true;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
