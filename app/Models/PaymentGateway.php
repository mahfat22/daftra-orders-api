<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentGateway extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'config', 'is_active'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'config' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the config attribute.
     */
    public function getConfigAttribute($value): array
    {
        if (is_null($value)) {
            return [];
        }

        return json_decode($value, true) ?? [];
    }
}
