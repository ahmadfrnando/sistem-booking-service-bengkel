<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\XenditWebhookController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/layanan', [HomeController::class, 'services'])->name('services');

Route::prefix('booking')->name('booking.')->middleware('throttle:60,1')->group(function () {
    Route::get('/', [BookingController::class, 'create'])->name('create');
    Route::post('/', [BookingController::class, 'store'])->middleware('throttle:10,1')->name('store');
    Route::get('/slots', [BookingController::class, 'slots'])->name('slots');
    Route::get('/{booking}', [BookingController::class, 'show'])->name('show');
    Route::get('/{booking}/bayar', [BookingController::class, 'pay'])->name('pay');
    Route::post('/{booking}/qris', [BookingController::class, 'regenerateQris'])->middleware('throttle:5,1')->name('qris');
    Route::get('/{booking}/status', [BookingController::class, 'status'])->name('status');
    Route::post('/{booking}/simulate', [BookingController::class, 'simulate'])->name('simulate');
    Route::get('/{booking}/invoice', [BookingController::class, 'invoice'])->name('invoice');
    Route::get('/{booking}/struk', [BookingController::class, 'receipt'])->name('receipt');
});

Route::post('/webhooks/xendit', XenditWebhookController::class)->name('webhooks.xendit');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
        Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
        Route::get('/', [Admin\DashboardController::class, 'index'])->name('dashboard');

        Route::controller(Admin\BookingController::class)->prefix('bookings')->name('bookings.')->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/{booking}', 'show')->name('show');
            Route::post('/{booking}/check-in', 'checkIn')->name('check-in');
            Route::post('/{booking}/start', 'startService')->name('start');
            Route::post('/{booking}/cancel', 'cancel')->name('cancel');
            Route::get('/{booking}/finish', 'finishForm')->name('finish');
            Route::post('/{booking}/finish', 'finish')->name('finish.store');
            Route::get('/{booking}/payment', 'payment')->name('payment');
            Route::post('/{booking}/payment/qris', 'payQris')->name('payment.qris');
            Route::post('/{booking}/payment/manual', 'payManual')->name('payment.manual');
            Route::get('/{booking}/payment/status', 'paymentStatus')->name('payment.status');
            Route::post('/{booking}/payment/simulate', 'simulate')->name('payment.simulate');
            Route::post('/{booking}/resend', 'resendNotification')->name('resend');
        });

        Route::resource('services', Admin\ServiceController::class)->except('show');
        Route::resource('mechanics', Admin\MechanicController::class)->except('show');
    });
});
