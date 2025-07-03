<?php

namespace App\Filament\Resources;

use App\Enums\ConsultationStatus;
use App\Filament\Resources\ConsultationResource\Pages;
use App\Models\Consultation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ConsultationResource extends Resource
{
    protected static ?string $model = Consultation::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-oval-left-ellipsis';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('user_id')
                    ->label('User')
                    ->formatStateUsing(fn ($record) => $record->user->name)
                    ->required(),

                TextInput::make('doctor_id')
                    ->label('Doctor')
                    ->formatStateUsing(fn ($record) => $record->user->name)
                    ->nullable(),

                Textarea::make('complaint')
                    ->label('Complaint')
                    ->nullable()
                    ->rows(10)
                    ->columnSpanFull(),

                Select::make('status')
                    ->label('Status')
                    ->options(ConsultationStatus::getAsOptions())
                    ->required()
                    ->columnSpanFull(),

                Textarea::make('conversation')
                    ->label('Conversation')
                    ->formatStateUsing(fn ($state) => json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES))
                    ->nullable()
                    ->columnSpanFull()
                    ->rows(20),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['user', 'doctor']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not assigned'),

                TextColumn::make('complaint')
                    ->label('Complaint')
                    ->limit(50)
                    ->tooltip(fn ($record) => $record->complaint)
                    ->placeholder('No complaint'),

                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable()
                    ->colors([
                        'primary' => 'pending',
                        'success' => 'completed',
                        'danger' => 'cancelled',
                        'warning' => 'in-progress',
                    ])
                    ->placeholder('No status'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsultations::route('/'),
            'view' => Pages\ViewConsultation::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }
}
