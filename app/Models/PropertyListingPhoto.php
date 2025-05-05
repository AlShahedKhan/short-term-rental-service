<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyListingPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'property_listing_id',
        'photo_path',
    ];

    /**
     * Get the property listing that owns the photo.
     */
    public function propertyListing()
    {
        return $this->belongsTo(PropertyListing::class);
    }
}
