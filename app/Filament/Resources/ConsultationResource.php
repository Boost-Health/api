<?php

namespace App\Filament\Resources;

use App\Enums\ConsultationStatus;
use App\Enums\OrderType;
use App\Filament\Resources\ConsultationResource\Pages;
use App\Models\Consultation;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
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
                TextInput::make('id')->label('ID')->nullable()->disabled(fn (string $context) => $context === 'edit'),

                TextInput::make('user_id')
                    ->label('User')
                    ->formatStateUsing(fn ($record) => $record?->user?->name)
                    ->disabled(),

                TextInput::make('doctor_id')
                    ->label('Doctor')
                    ->formatStateUsing(fn ($record) => $record?->user?->name)
                    ->disabled(),

                Select::make('status')
                    ->label('Status')
                    ->options(ConsultationStatus::getAsOptions())
                    ->required(),

                MarkdownEditor::make('prescription')
                    ->label('Prescription')
                    ->columnSpanFull()
                    ->disabled(),

                Select::make('order_type')
                    ->label('Order Type')
                    ->options(OrderType::getAsOptions()),

                TextInput::make('order_source')->label('Order Source')->nullable(),
                TextInput::make('order_number')->label('Order Number')->nullable()->numeric(),
                TextInput::make('order_total')->label('Order Total')->nullable()->numeric(),
                TextInput::make('order_address')->label('Order Delivery Address')->nullable()->columnSpanFull(),
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
                TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('doctor.name')
                    ->label('Doctor')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Not assigned'),

                TextColumn::make('order_type')
                    ->label('Order Type')
                    ->formatStateUsing(fn ($state) => str($state->value)->replace('_', ' ')->title()),

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
                        'warning' => 'in_progress',
                    ])
                    ->placeholder('No status')
                    ->formatStateUsing(fn ($state) => str($state->value)->replace('_', ' ')->title()),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListConsultations::route('/'),
            'view' => Pages\ViewConsultation::route('/{record}'),
            'edit' => Pages\EditConsultation::route('/{record}/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }
}
