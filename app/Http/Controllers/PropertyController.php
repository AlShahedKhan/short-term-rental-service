<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrUpdatePropertyRequest;
use App\Services\PropertyService;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\Auth;

class PropertyController extends Controller
{
    use ApiResponse;

    protected $propertyService;

    public function __construct(PropertyService $propertyService)
    {
        $this->propertyService = $propertyService;
    }

    public function storeOrUpdate(StoreOrUpdatePropertyRequest $request)
    {
        return $this->safeCall(function () use ($request) {
            $landlordId = Auth::id();

            // Handle photos (force array)
            $photos = $request->file('photos', []);
            if ($photos instanceof \Illuminate\Http\UploadedFile) {
                $photos = [$photos];
            }

            $property = $this->propertyService->createProperty($landlordId, $request->validated(), $photos);

            return $this->successResponse('Property details saved successfully.', $property);
        });
    }
}
