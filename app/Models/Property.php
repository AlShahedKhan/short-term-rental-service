<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'properties';

    protected $fillable = [
        'landlord_id',
        'zip_code',
        'bedroom_count',
        'bathroom_count',
        'highlights',
        'key_amenities',
    ];

    protected $casts = [
        'key_amenities' => 'array',
    ];

    public function photos()
    {
        return $this->hasMany(PropertyPhoto::class);
    }
}
