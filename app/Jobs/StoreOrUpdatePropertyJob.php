<?php
namespace App\Jobs;

use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;

class StoreOrUpdatePropertyJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $propertyData;
    protected $photoPaths;
    protected $propertyId;

    public function __construct($propertyData, $photoPaths, $propertyId = null)
    {
        $this->propertyData = $propertyData;
        $this->photoPaths = $photoPaths;
        $this->propertyId = $propertyId;
    }

    public function handle()
    {
        DB::beginTransaction();

        try {
            // Create or update the property
            $property = Property::updateOrCreate(
                ['id' => $this->propertyId],
                $this->propertyData
            );

            // Handle photos if present
            if ($this->photoPaths) {
                foreach ($this->photoPaths as $photoPath) {
                    // Save the photo path and associate it with the property
                    PropertyPhoto::create([
                        'property_id' => $property->id,
                        'photo_path' => $photoPath
                    ]);
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            // You can log the error or handle the exception here if needed
        }
    }
}
