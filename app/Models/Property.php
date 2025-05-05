<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Property extends Model
{
    protected $table = 'properties';

    protected $fillable = [

        'first_name',
        'last_name',
        'phone_number',
        'email',
        'property_type',
        'property_address',
        'property_description',
        'income_goals',
        'is_managed_by_rejuvenest',
    ];


    protected $casts = [
        'property_type' => 'array', // Automatically cast it as an array
    ];
    public function photos()
    {
        return $this->hasMany(PropertyPhoto::class);
    }

}
