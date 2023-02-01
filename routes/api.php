<?php

use App\Http\Controllers\Api\v1\Booking\BookingController;
use App\Http\Controllers\Api\v1\Booking\BookingOwnerController;
use App\Http\Controllers\Api\v1\CityController;
use App\Http\Controllers\Api\v1\ContactController;
use App\Http\Controllers\Api\v1\CountryController;
use App\Http\Controllers\Api\v1\Hotel\HotelController;
use App\Http\Controllers\Api\v1\Hotel\HotelFavoriteController;
use App\Http\Controllers\Api\v1\Hotel\HotelOwnerController;
use App\Http\Controllers\Api\v1\Hotel\HotelViewedController;
use App\Http\Controllers\Api\v1\LakeController;
use App\Http\Controllers\Api\v1\MealsController;
use App\Http\Controllers\Api\v1\MediaController;
use App\Http\Controllers\Api\v1\RegionController;
use App\Http\Controllers\Api\v1\RoomController;
use App\Http\Controllers\Api\v1\TagController;
use App\Http\Controllers\Api\v1\UserProfileController;
use App\Http\Middleware\HotelViewedMiddleware;
use Illuminate\Support\Facades\Route;

Route::group(['as' => 'api.'], function () {
    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::group(['prefix' => 'profile', 'as' => 'profile.'], function () {
            Route::get('/', [UserProfileController::class, 'index'])->name('index');
            Route::post('/update', [UserProfileController::class, 'update'])->name('update');
            Route::post('/update/partner', [UserProfileController::class, 'updatePartner'])->name('updatePartner');
            Route::post('/input-phone-code', [UserProfileController::class, 'inputChangePhoneCode'])->name('inputChangePhoneCode');
        });
        Route::group(['prefix' => 'favorites', 'as' => 'favorites.'], function () {
            Route::post('/', [HotelFavoriteController::class, 'index'])->name('index');
            Route::post('/hotels/{hotel}', [HotelFavoriteController::class, 'store'])->name('store');
            Route::delete('/hotels/{hotel}', [HotelFavoriteController::class, 'remove'])->name('remove');
        });

        Route::get('/viewed/hotels/', [HotelViewedController::class, 'index'])->name('hotels.viewed.index');

        Route::group(['prefix' => 'owner/hotels', 'as' => 'owner.hotels.'], function () {
            Route::group(['middleware' => 'hotel.owner'], function () {
                Route::post('/', [HotelOwnerController::class, 'index'])->name('index');
                Route::get('/{hotel}', [HotelOwnerController::class, 'show'])->name('show');
                Route::post('/upsert/{hotel?}', [HotelOwnerController::class, 'upsert'])->name('upsert');
            });
        });

        Route::group(['middleware' => 'hotel.owner', 'prefix' => 'hotels', 'as' => 'hotels.'], function () {
            Route::delete('/{hotel}/rooms/{room}', [RoomController::class, 'remove'])->name('rooms.remove');
            Route::delete('/{hotel}/contacts/{contact}', [ContactController::class, 'remove'])->name('contacts.remove');
            Route::delete('/{hotel}/media/{media}', [MediaController::class, 'remove'])->name('media.remove');
            Route::post('/{hotel}/media/{media}/main', [MediaController::class, 'addMain'])->name('media.addMain');
            Route::delete('/{hotel}/media/{media}/main', [MediaController::class, 'removeMain'])->name('media.removeMain');
        });
        Route::group(['middleware' => 'booking', 'prefix' => 'bookings', 'as' => 'bookings.'], function () {
            Route::post('/', [BookingController::class, 'index'])->name('index');
            Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
        });
        Route::group(['middleware' => 'booking.owner', 'prefix' => 'owner/bookings', 'as' => 'owner.bookings.'], function () {
            Route::post('/', [BookingOwnerController::class, 'index'])->name('index');
            Route::get('/{booking}', [BookingOwnerController::class, 'show'])->name('show');
        });
    });

    Route::group(['prefix' => 'hotels', 'as' => 'hotels.'], function () {
        Route::post('/', [HotelController::class, 'index'])->name('index');
        Route::get('/all', [HotelController::class, 'all'])->name('all');
        Route::post('/search', [HotelController::class, 'search'])->name('search');
        Route::get('/{hotel}', [HotelController::class, 'show'])
            ->name('show')
            ->middleware(HotelViewedMiddleware::class);

        Route::post('/{hotelId}/rooms', [RoomController::class, 'getHotelAvailableRooms'])->name('rooms.getHotelAvailableRooms');
        Route::get('/{hotel}/available-rooms/{room}', [RoomController::class, 'show'])->name('rooms.show');
    });

    Route::post('/bookings/hotel/{hotel}', [BookingController::class, 'init'])->name('bookings.init');
    Route::post('/bookings/process/{booking}', [BookingController::class, 'process'])->name('bookings.process');

    Route::get('/tags', [TagController::class, 'index'])->name('tags.index');
    Route::get('/countries', [CountryController::class, 'index'])->name('countries.index');
    Route::get('/cities', [CityController::class, 'index'])->name('cities.index');
    Route::get('/regions', [RegionController::class, 'index'])->name('regions.index');
    Route::get('/lakes', [LakeController::class, 'index'])->name('lakes.index');
    Route::get('/meals', [MealsController::class, 'index'])->name('meals.index');
});


