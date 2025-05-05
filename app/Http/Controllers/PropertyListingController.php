<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PropertyListing;
use Illuminate\Support\Facades\Log;
use App\Models\PropertyListingPhoto;
use Illuminate\Support\Facades\Validator;

class PropertyListingController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        return $this->safeCall(function () use ($request) {
            // Retrieve properties, paginated with 12 per page
            $properties = PropertyListing::paginate(10);

            // Map the properties to the required structure
            $propertyData = $properties->map(function ($property) {
                return [
                    'image' => asset('storage/' . $property->photo_path), // Assuming photo_path is stored in 'storage'
                    'title' => $property->title,
                    'property_description' => $property->description,
                    'date' => $property->created_at->toDateString(), // Formatting the date
                ];
            });

            // Return the success response with pagination data
            return $this->successResponse('Properties fetched successfully.', [
                'data' => $propertyData,
                'pagination' => [
                    'total' => $properties->total(),
                    'current_page' => $properties->currentPage(),
                    'last_page' => $properties->lastPage(),
                    'per_page' => $properties->perPage(),
                    'from' => $properties->firstItem(),
                    'to' => $properties->lastItem(),
                ]
            ]);
        });
    }

    public function storeOrUpdate(Request $request, $id = null)
    {
        return $this->safeCall(function () use ($request, $id) {
            // Decode the incoming property data from the 'data' key in the request
            $propertyData = json_decode($request->input('data'), true);

            // Validate the incoming property data
            $validatedData = \Validator::make($propertyData, [
                'title' => 'required|string|max:255',
                'location' => 'required|string|max:255',
                'bedrooms' => 'nullable|integer',
                'bathrooms' => 'nullable|integer',
                'property_type' => 'required|string|max:100',
                'listing_website' => 'required|string|max:100',
                'listing_website_link' => 'required|url',
                'description' => 'required|string|max:1000',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            ]);

            // If validation fails, throw an exception
            if ($validatedData->fails()) {
                throw new ValidationException($validatedData);
            }

            // Use the updateOrCreate method to store or update the property
            $property = PropertyListing::updateOrCreate(
                ['id' => $id],
                $propertyData
            );

            // Handle the file upload for photos if present
            $photoPaths = [];
            if ($request->hasFile('photos')) {
                $photos = $request->file('photos');
                foreach ($photos as $photo) {
                    $photoPaths[] = $photo->store('property_listing_photos', 'public'); // Save photo and get path
                }
            }

            // Return a success response indicating the property save process has completed
            return $this->successResponse('Property save process completed successfully.', [
                'property' => array_merge($propertyData, ['id' => $property->id, 'status' => 'completed']),
                'photos' => $photoPaths
            ]);
        });
    }


}

