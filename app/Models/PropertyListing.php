<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PropertyListing extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'location',
        'bedrooms',
        'bathrooms',
        'property_type',
        'listing_website',
        'listing_website_link',
        'description',
    ];

    /**
     * Get the photos for the property listing.
     */
    public function photos()
    {
        return $this->hasMany(PropertyListingPhoto::class);
    }
}
