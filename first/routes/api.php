<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminBookingsListController;
use App\Http\Controllers\AdminTechnicianController;


// Public routes
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/technician/login', [AuthController::class, 'technicianLogin']);

Route::get('/admin/customers', [AdminController::class, 'getAllCustomers']);

Route::get('/admin/bookings', [BookingController::class, 'getAllBookingsForAdmin']);

Route::put('/admin/bookings/{id}/assign', [BookingController::class, 'assignTechnician']);

Route::put('/admin/bookings/{id}/status', [BookingController::class, 'updateStatus']);

Route::get('/admin/completed-bookings', [BookingController::class, 'getCompletedBookingsForAdmin']);

Route::put('/admin/completed-bookings/{id}', [BookingController::class, 'updateCompletedBooking']);

Route::get('/admin/bookings/totals', [BookingController::class, 'getBookingTotals']);

//Route::post('/admin/register-technician', [AdminTechnicianController::class, 'register']);

    Route::get('/admin/technicians', [AdminTechnicianController::class, 'index']);
    Route::post('/admin/register-technician', [AdminTechnicianController::class, 'store']);
    Route::delete('/admin/technicians/{id}', [AdminTechnicianController::class, 'destroy']);
// Protected routes (Requires authentication)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/customer', [CustomerController::class, 'getCustomer']);
    Route::put('/customer/update', [CustomerController::class, 'updateCustomer']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/technician/logout', [AuthController::class, 'technicianLogout']);
    // Route to refresh authentication token
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);

    //Route::get('/admin/technicians', [AdminTechnicianController::class, 'index']);
    //Route::post('/admin/register-technician', [AdminTechnicianController::class, 'store']);
    //Route::delete('/admin/technicians/{id}', [AdminTechnicianController::class, 'destroy']);

    Route::post('/bookings', [BookingController::class, 'createBooking']);
    Route::get('/bookings', [BookingController::class, 'getBookings']);
    Route::get('/bookings/upcoming', [BookingController::class, 'getUpcomingBookings']);
    Route::put('/bookings/{id}/cancel', [BookingController::class, 'cancelBooking']);
    Route::get('/bookings/availability', [BookingController::class, 'checkAvailability']);
    Route::get('/bookings/completed', [BookingController::class, 'getCompletedBookings']);

    Route::get('/technician/bookings', [BookingController::class, 'getTechnicianBookings']);
});
