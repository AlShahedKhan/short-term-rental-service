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

    // public function storeOrUpdate(StoreOrUpdatePropertyRequest $request)
    // {
    //     return $this->safeCall(function () use ($request) {
    //         $landlordId = Auth::id();

    //         // Handle photos (force array)
    //         $photos = $request->file('photos', []);
    //         if ($photos instanceof \Illuminate\Http\UploadedFile) {
    //             $photos = [$photos];
    //         }

    //         $property = $this->propertyService->createProperty($landlordId, $request->validated(), $photos);

    //         return $this->successResponse('Property details saved successfully.', $property);
    //     });
    // }
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
}
