<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Filament\Forms;
use Filament\Tables;
use App\Filament\Resources\AdminResource\Pages;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\Select; 

class AdminResource extends Resource
{
    protected static ?string $model = null; // Firebase: no Eloquent

    protected static ?string $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Admins';

    public static function form(Forms\Form $form): Forms\Form
    {
         return $form->schema([
            TextInput::make('first_name')->required(),
            TextInput::make('middle_name')->required(),
            TextInput::make('last_name')->required(),
            TextInput::make('email')->email()->required(),
            Select::make('role')
                ->required()
                ->label('Role')
                ->options([
                    'SDAO Coordinator' => 'SDAO Coordinator',
                    'Volunteers Admin' => 'Volunteers Admin',
                    'Activities Admin' => 'Activities Admin',
                ]),
        ]);
    }

    public static function table(Tables\Table $table): Tables\Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name'),
                TextColumn::make('middle_name'),
                TextColumn::make('last_name'),
                TextColumn::make('email'),
            ])
            ->defaultPaginationPageOption(10);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAdmins::route('/'),
            'create' => Pages\CreateAdmin::route('/create'),
        ];
    }
}
