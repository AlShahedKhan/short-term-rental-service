<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\PropertyListing;
use Illuminate\Support\Facades\Log;
use App\Models\PropertyListingPhoto;
use Illuminate\Support\Facades\Storage;
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
            $propertyData = json_decode($request->input('data'), true);

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

            if ($validatedData->fails()) {
                throw new ValidationException($validatedData);
            }

            $property = PropertyListing::updateOrCreate(
                ['id' => $id],
                $propertyData
            );

            $photoPaths = [];
            if ($request->hasFile('photos')) {
                $photos = $request->file('photos');
                foreach ($photos as $photo) {
                    $path = $photo->store('property_listing_photos', 'public');
                    $photoPaths[] = $path;

                    // Create a PropertyListingPhoto record for each uploaded photo
                    PropertyListingPhoto::create([
                        'property_listing_id' => $property->id,
                        'photo_path' => $path,
                    ]);
                }
            }

            return $this->successResponse('Property save process completed successfully.', [
                'property' => array_merge($propertyData, ['id' => $property->id, 'status' => 'completed']),
                'photos' => $photoPaths
            ]);
        });
    }

    public function show($id)
    {
        // Log the request to show a property
        Log::info('Received request to show property listing', ['id' => $id]);

        // Find the property listing by ID with eager loaded photos
        $propertyListing = PropertyListing::with('photos')->find($id);

        // Check if the property exists
        if (!$propertyListing) {
            Log::error('Property listing not found', ['id' => $id]);
            return response()->json([
                'status' => false,
                'message' => 'Property listing not found.',
            ], 404);
        }

        // Log successful retrieval of property
        Log::info('Property listing found', ['property' => $propertyListing]);

        // Map the photos to their public URLs
        $photoUrls = $propertyListing->photos->map(function ($photo) {
            return Storage::url($photo->photo_path);
        })->toArray();

        // Prepare a custom response array
        $data = [
            'id' => $propertyListing->id,
            'title' => $propertyListing->title,
            'location' => $propertyListing->location,
            'bedrooms' => $propertyListing->bedrooms,
            'bathrooms' => $propertyListing->bathrooms,
            'property_type' => $propertyListing->property_type,
            'listing_website' => $propertyListing->listing_website,
            'listing_website_link' => $propertyListing->listing_website_link,
            'description' => $propertyListing->description,
            'created_at' => $propertyListing->created_at,
            'updated_at' => $propertyListing->updated_at,
            'photos' => $photoUrls,
        ];

        // Return the property listing with photos as a response
        return response()->json([
            'status' => true,
            'message' => 'Property listing retrieved successfully.',
            'data' => $data,
        ], 200);
    }

    public function countPropertyListings()
    {
        return $this->safeCall(function () {
            $count = PropertyListing::count();

            return $this->successResponse('Property listings count retrieved successfully.', [
                'count' => $count,
            ]);
        });
    }
}

