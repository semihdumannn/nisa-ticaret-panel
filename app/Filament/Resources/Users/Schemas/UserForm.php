<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Modules\User\Domain\ValueObjects\UserRole;
use Filament\Forms\Components\DateTimePicker;
use Filament\Schemas\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(3)
            ->components([
                // ── Personal Info ─────────────────────────────────────────────
                Section::make('Personal Information')
                    ->description('Basic user contact details.')
                    ->icon('heroicon-o-user')
                    ->columnSpan(2)
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Full Name')
                            ->required()
                            ->maxLength(100)
                            ->placeholder('e.g. Ayşe Kaya'),

                        TextInput::make('phone')
                            ->label('Phone Number')
                            ->tel()
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(20)
                            ->mask('+90 (999) 999 99 99')
                            ->placeholder('+90 (5XX) XXX XX XX'),

                        TextInput::make('email')
                            ->label('Email Address')
                            ->email()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('user@example.com'),

                        TextInput::make('firebase_uid')
                            ->label('Firebase UID')
                            ->disabled()
                            ->dehydrated(false)
                            ->placeholder('(managed by Firebase)')
                            ->helperText('Set automatically on first login.'),
                    ]),

                // ── Access Control ────────────────────────────────────────────
                Section::make('Access & Status')
                    ->description('Role assignment and account status.')
                    ->icon('heroicon-o-shield-check')
                    ->columnSpan(1)
                    ->schema([
                        Select::make('role')
                            ->label('Role')
                            ->required()
                            ->native(false)
                            ->options(collect(UserRole::cases())->mapWithKeys(
                                fn (UserRole $r) => [$r->value => $r->label()],
                            ))
                            ->default(UserRole::CUSTOMER->value),

                        Toggle::make('is_active')
                            ->label('Account Active')
                            ->helperText('Inactive users cannot log in.')
                            ->default(true)
                            ->onColor('success')
                            ->offColor('danger'),

                        DateTimePicker::make('email_verified_at')
                            ->label('Email Verified At')
                            ->native(false)
                            ->nullable()
                            ->placeholder('Not verified'),
                    ]),

                // ── Password ──────────────────────────────────────────────────
                Section::make('Password')
                    ->description('Leave blank to keep the current password.')
                    ->icon('heroicon-o-lock-closed')
                    ->collapsed()
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('password')
                            ->label('New Password')
                            ->password()
                            ->revealable()
                            ->minLength(8)
                            ->dehydrateStateUsing(fn ($state) => filled($state) ? bcrypt($state) : null)
                            ->dehydrated(fn ($state) => filled($state))
                            ->nullable()
                            ->placeholder('Minimum 8 characters'),
                    ]),
            ]);
    }
}
