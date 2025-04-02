<?php

namespace App\Services;

use App\Jobs\StorePropertyJob;
use App\Models\Property;
use Illuminate\Support\Facades\Storage;


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

    public function updateProperty(int $landlordId, Property $property, array $data, array $photos): array
{
    // Optionally protect ownership
    if ($property->landlord_id !== $landlordId) {
        abort(403, 'Unauthorized access to this property');
    }

    // Update property fields
    $property->update([
        'zip_code' => $data['zip_code'],
        'bedroom_count' => $data['bedroom_count'],
        'bathroom_count' => $data['bathroom_count'],
        'highlight' => $data['highlight'] ?? null,
        'key_amenities' => $data['key_amenities'] ?? [],
    ]);

    $photoUrls = [];

    // If photos are uploaded, replace old ones
    if (!empty($photos)) {
        // Delete old photo files
        foreach ($property->photos as $photo) {
            Storage::disk('public')->delete($photo->photo_path);
            $photo->delete();
        }

        // Upload new photos
        foreach ($photos as $photo) {
            $path = $photo->store('property_photos', 'public');
            $property->photos()->create(['photo_path' => $path]);
            $photoUrls[] = url('storage/' . $path);
        }
    } else {
        // Keep old photo URLs
        $photoUrls = $property->photos->map(fn($p) => url('storage/' . $p->photo_path))->toArray();
    }

    return [
        'id' => $property->id,
        'zip_code' => $property->zip_code,
        'bedroom_count' => $property->bedroom_count,
        'bathroom_count' => $property->bathroom_count,
        'highlight' => $property->highlight,
        'key_amenities' => $property->key_amenities,
        'photos' => $photoUrls,
    ];
}
}
