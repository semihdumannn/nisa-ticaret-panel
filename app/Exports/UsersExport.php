<?php

namespace App\Exports;

use App\Models\User;
use App\Modules\User\Domain\ValueObjects\UserRole;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class UsersExport implements FromQuery, ShouldAutoSize, WithHeadings, WithMapping, WithStyles
{
    public function query()
    {
        return User::withCount('addresses')->orderByDesc('created_at');
    }

    public function headings(): array
    {
        return [
            'ID',
            'Name',
            'Phone',
            'Email',
            'Role',
            'Active',
            'Addresses',
            'Registered At',
        ];
    }

    public function map($user): array
    {
        return [
            $user->id,
            $user->name ?? '—',
            $user->phone,
            $user->email ?? '—',
            UserRole::tryFrom($user->role)?->label() ?? $user->role,
            $user->is_active ? 'Yes' : 'No',
            $user->addresses_count,
            $user->created_at?->format('d.m.Y H:i'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
