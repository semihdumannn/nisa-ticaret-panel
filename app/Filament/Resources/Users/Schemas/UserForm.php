<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Modules\User\Domain\ValueObjects\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Account Information')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('Full name'),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->placeholder('user@example.com'),

                        TextInput::make('phone')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->placeholder('+905XXXXXXXXX'),

                        TextInput::make('firebase_uid')
                            ->label('Firebase UID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('(managed by Firebase)'),
                    ]),

                Section::make('Roles & Status')
                    ->columns(2)
                    ->schema([
                        Select::make('role')
                            ->required()
                            ->options(collect(UserRole::cases())->mapWithKeys(
                                fn (UserRole $r) => [$r->value => $r->label()],
                            ))
                            ->default(UserRole::CUSTOMER->value),

                        Toggle::make('is_active')
                            ->label('Account Active')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->nullable(),

                        TextInput::make('password')
                            ->password()
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->nullable()
                            ->revealable()
                            ->placeholder('Leave blank to keep current'),
                    ]),
            ]);
    }
}
