<?php

namespace App\Http\Controllers;

use App\Models\Property;
use App\Traits\ApiResponse;
use App\Services\PropertyService;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\StoreOrUpdatePropertyRequest;

class PropertyController extends Controller
{
    use ApiResponse;

    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function index()
    {
        return $this->safeCall(function () {
            $landlordId = Auth::id();

            $properties = \App\Models\Property::with('photos')
                ->where('landlord_id', $landlordId)
                ->latest()
                ->get();

            $formatted = $properties->map(function ($property) {
                return [
                    'id' => $property->id,
                    'zip_code' => $property->zip_code,
                    'bedroom_count' => $property->bedroom_count,
                    'bathroom_count' => $property->bathroom_count,
                    'highlight' => $property->highlight,
                    'key_amenities' => $property->key_amenities,
                    'photos' => $property->photos->map(function ($photo) {
                        return url('storage/' . $photo->photo_path);
                    })->toArray(),
                ];
            });

            return $this->successResponse('Properties retrieved successfully.', $formatted);
        });
    }


    public function storeOrUpdate(StoreOrUpdatePropertyRequest $request)
    {
        return $this->safeCall(function () use ($request) {
            $landlordId = Auth::id();

            // Get ID from query string
            $id = $request->query('id');

            // Ensure photos are always an array
            $photos = $request->file('photos', []);
            if ($photos instanceof \Illuminate\Http\UploadedFile) {
                $photos = [$photos];
            }

            if ($id) {
                // Update existing property
                $property = Property::findOrFail($id);
                return $this->successResponse(
                    'Property updated successfully.',
                    $this->propertyService->updateProperty($landlordId, $property, $request->validated(), $photos)
                );
            } else {
                // Create new property
                return $this->successResponse(
                    'Property details saved successfully.',
                    $this->propertyService->createProperty($landlordId, $request->validated(), $photos)
                );
            }
        });
    }

    public function show(Property $property)
    {
        return $this->safeCall(function () use ($property) {
            $photoUrls = $property->photos->map(function ($photo) {
                return url('storage/' . $photo->photo_path);
            })->toArray();

            return $this->successResponse('Property retrieved successfully.', [
                'id' => $property->id,
                'zip_code' => $property->zip_code,
                'bedroom_count' => $property->bedroom_count,
                'bathroom_count' => $property->bathroom_count,
                'highlight' => $property->highlight,
                'key_amenities' => $property->key_amenities,
                'photos' => $photoUrls,
            ]);
        });
    }
}
