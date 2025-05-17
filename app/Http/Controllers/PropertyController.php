<?php

namespace App\Http\Controllers;

use App\Jobs\StoreOrUpdatePropertyJob;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use App\Models\Property;
use App\Models\PropertyPhoto;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class PropertyController extends Controller
{
    use ApiResponse;

    public function search(Request $request)
    {
        $query = Property::query();
        if ($request->has('email')) {
            $query->where('email', 'like', '%' . $request->input('email') . '%');
        }
        if ($request->has('phone_number')) {
            $query->where('phone_number', 'like', '%' . $request->input('phone_number') . '%');
        }
        $properties = $query->paginate(10);
        return $this->successResponse('Properties search results.', $properties);
    }
    public function index()
    {
        // Retrieve properties with only the required fields, sorted by 'created_at' in descending order
        $properties = Property::select('id','first_name', 'last_name', 'phone_number', 'email', 'created_at')
            ->orderBy('created_at', 'desc')  // Sort by 'created_at' in descending order
            ->paginate(10);  // Adjust the pagination per page

        // Return the paginated items directly in the response (no wrapping in "data" key)
        return $this->successResponse('Properties retrieved successfully.', [
            'properties' => $properties->items(),  // List of items on the current page
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'total_pages' => $properties->lastPage(),
                'total_items' => $properties->total(),
            ]
        ]);
    }

    public function getRecentSubmissions()
    {
        // Retrieve properties with only the required fields, sorted by 'created_at' in descending order
        $properties = Property::select('id','first_name', 'last_name', 'phone_number', 'email', 'created_at')
            ->orderBy('created_at', 'desc')  // Sort by 'created_at' in descending order
            ->paginate(3);  // Adjust the pagination per page

        return $this->successResponse('Properties retrieved successfully.', [
            'properties' => $properties->items(),  // List of items on the current page
            'pagination' => [
                'current_page' => $properties->currentPage(),
                'total_pages' => $properties->lastPage(),
                'total_items' => $properties->total(),
            ]
        ]);
    }

    public function StoreOrUpdateProperty(Request $request, $id = null)
    {
        return $this->safeCall(function () use ($request, $id) {
            $propertyData = json_decode($request->input('data'), true);

            // Validate the incoming property data
            $validatedData = \Validator::make($propertyData, [
                'first_name' => 'required|string|max:255',
                'last_name' => 'required|string|max:255',
                'phone_number' => 'required|string|max:15',
                'email' => 'required|email|max:255',
                'property_type' => 'required|array',
                'property_type.*' => 'string', // Ensure each item in the array is a string
                'property_address' => 'required|string|max:500',
                'property_description' => 'nullable|string|max:1000',
                'income_goals' => 'nullable|string|max:500',
                'photos' => 'nullable|array',
                'photos.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
                'is_managed_by_rejuvenest' => 'nullable|boolean',
            ]);

            if ($validatedData->fails()) {
                throw new ValidationException($validatedData);
            }

            $property = Property::updateOrCreate(
                ['id' => $id],
                $propertyData
            );

            $photoPaths = [];
            if ($request->hasFile('photos')) {
                $photos = $request->file('photos');
                foreach ($photos as $photo) {
                    $photoPaths[] = $photo->store('property_photos', 'public');  // Save photo and get path
                }
            }

            StoreOrUpdatePropertyJob::dispatch($propertyData, $photoPaths, $property->id);

            return $this->successResponse('Property save process has started.', [
                'property' => array_merge($propertyData, ['id' => $property->id, 'status' => 'processing']),
                'photos' => $photoPaths
            ]);
        });
    }

    public function show($id)
    {
        $property = Property::with('photos')->find($id);

        if (!$property) {
            return $this->errorResponse('Property not found.', 404);
        }
        return $this->successResponse('Property retrieved successfully.', $property);
    }

    public function countSubmissions()
    {
        return $this->safeCall(function () {
            $count = Property::count();
            return $this->successResponse('Total property submissions count.', ['count' => $count]);
        });
    }

    public function destroy($id)
    {
        return $this->safeCall(function () use ($id) {
            // Find the property with its photos
            $property = Property::with('photos')->find($id);

            if (!$property) {
                return $this->errorResponse('Property not found.', 404);
            }

            try {
                // Begin transaction
                \DB::beginTransaction();

                // Delete photos from storage and database
                foreach ($property->photos as $photo) {
                    // Delete file from storage
                    if (\Storage::disk('public')->exists($photo->photo_path)) {
                        \Storage::disk('public')->delete($photo->photo_path);
                    }
                    // Delete photo record
                    $photo->delete();
                }

                // Delete the property
                $property->delete();

                // Commit transaction
                \DB::commit();

                return $this->successResponse('Property deleted successfully.');

            } catch (\Exception $e) {
                // Rollback transaction on error
                \DB::rollBack();
                return $this->errorResponse('Failed to delete property: ' . $e->getMessage(), 500);
            }
        });
    }
}
