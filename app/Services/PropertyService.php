<?php

namespace App\Services;

use App\Jobs\StorePropertyJob;

class PropertyService
{
    public function createProperty(int $landlordId, array $data, array $photos): array
    {
        $photoPaths = [];
        foreach ($photos as $photo) {
            $photoPaths[] = $photo->store('property_photos', 'public');
        }

        // Directly run the class
        $job = new StorePropertyJob();
        return $job->handle($landlordId, $data, $photoPaths);
    }
}
