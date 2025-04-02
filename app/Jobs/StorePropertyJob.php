<?php

namespace App\Jobs;

use App\Models\Property;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class StorePropertyJob
{
    public function handle($landlordId, $data, $photoPaths): array
    {
        $property = Property::create([
            'landlord_id' => $landlordId,
            'zip_code' => $data['zip_code'],
            'bedroom_count' => $data['bedroom_count'],
            'bathroom_count' => $data['bathroom_count'],
            'highlights' => $data['highlights'] ?? null,
            'key_amenities' => $data['key_amenities'] ?? [],
        ]);

        $photoUrls = [];
        foreach ($photoPaths as $path) {
            $property->photos()->create(['photo_path' => $path]);
            $photoUrls[] = url('storage/' . $path);
        }

        return [
            'id' => $property->id,
            'zip_code' => $property->zip_code,
            'bedroom_count' => $property->bedroom_count,
            'bathroom_count' => $property->bathroom_count,
            'highlights' => $property->highlights,
            'key_amenities' => $property->key_amenities,
            'photos' => $photoUrls,
        ];
    }
}

