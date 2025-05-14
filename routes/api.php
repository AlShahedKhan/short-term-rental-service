<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\LogoutController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\PropertyListingController;
use App\Http\Controllers\Auth\ForgotPasswordController;

Route::post('register', [RegisterController::class, 'register']);
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LogoutController::class, 'logout']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendOtp']);
Route::post('/verify-otp', [ForgotPasswordController::class, 'verifyOtp']);
Route::post('/reset-password', [ForgotPasswordController::class, 'resetPassword']);

Route::post('/contact', [ContactController::class, 'store'])->name('contact.store');

Route::post('/property-store-update/{id?}', [PropertyController::class, 'StoreOrUpdateProperty']);

Route::get('/get-property-listing/{id}', [PropertyListingController::class, 'show']);

// Route::group(['middleware' => ['auth:api']], function () {
    Route::get('/get-all-contact', [ContactController::class, 'index'])->name('contact.index');
    Route::get('/get-contact/{contact}', [ContactController::class, 'show'])->name('contact.show');
    Route::get('/property/search', [PropertyController::class, 'search']);
    Route::get('/property', [PropertyController::class, 'index']);
    Route::get('/get-recent-submissions', [PropertyController::class, 'getRecentSubmissions']);
    Route::get('/property/{property}', [PropertyController::class, 'show']);
    Route::get('/count-submissions', [PropertyController::class, 'countSubmissions']);

    Route::get('/get-recent-contacts', [ContactController::class, 'getRecentContacts']);
    Route::get('/count-contacts', [ContactController::class, 'countContacts']);

    Route::post('/property-listing/{id?}', [PropertyListingController::class, 'storeOrUpdate']);
    Route::get('/count-property-listings', [PropertyListingController::class, 'countPropertyListings']);

    Route::get('/admin/properties', [PropertyController::class, 'adminIndex']);

// });

Route::get('/get-property-list/{listing_website?}', [PropertyListingController::class, 'index']);
