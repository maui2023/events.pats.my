<?php

namespace App\Filament\Admin\Resources\Events\Schemas;

use App\Models\Event;
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
                Select::make('state_id')
                    ->relationship('state', 'name')
                    ->searchable()
                    ->preload()
                    ->default(null),
                TextInput::make('banner_path')
                    ->default(null),
                TextInput::make('icon')
                    ->default(null)
                    ->maxLength(16),
                Select::make('category_keys')
                    ->multiple()
                    ->options(collect(Event::categoryDefinitions())->mapWithKeys(fn (array $d) => [$d['key'] => ($d['icon'] . ' ' . $d['label'])])->all())
                    ->default([]),
                Toggle::make('is_published')
                    ->required(),
            ]);
    }
}
