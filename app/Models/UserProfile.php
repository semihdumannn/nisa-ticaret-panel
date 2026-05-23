<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'avatar_url',
        'company_name',
        'tax_number',
        'balance',
        'credit_limit',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'balance'      => 'decimal:2',
            'credit_limit' => 'decimal:2',
            'metadata'     => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
