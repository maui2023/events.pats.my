<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('organizer_id')
                    ->relationship('organizer', 'name')
                    ->required(),
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->default(null)
                    ->columnSpanFull(),
                DateTimePicker::make('start_at')
                    ->required(),
                DateTimePicker::make('end_at'),
                TextInput::make('location')
                    ->default(null),
                TextInput::make('banner_path')
                    ->default(null),
                Toggle::make('is_published')
                    ->required(),
            ]);
    }
}
