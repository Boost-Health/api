<?php

namespace App\Filament\Widgets;

use App\Enums\ConsultationStatus;
use App\Enums\UserType;
use App\Models\Consultation;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Users', User::whereType(UserType::USER)->count())->description('Total active users'),
            Stat::make('Doctors', User::whereType(UserType::DOCTOR)->count())->description('Total active doctors'),
            Stat::make('Available Doctors', User::whereType(UserType::DOCTOR)->where('is_available', 1)->count())->description('Total available doctors'),
            Stat::make('Agents', User::whereType(UserType::AGENT)->count())->description('Total active agents'),
            Stat::make('Consultations', Consultation::count()),
            Stat::make('Pending Consultations', Consultation::whereStatus(ConsultationStatus::PENDING)->count())->description('Total pending consultations'),
        ];
    }
}
