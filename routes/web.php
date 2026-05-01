<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\GuestAccessController;
use App\Http\Controllers\SpotipoAdminController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
});

Route::get('/guest', [GuestAccessController::class, 'show'])->name('guest.show');
Route::post('/guest/login', [GuestAccessController::class, 'login'])->name('guest.login');
Route::post('/guest/logout', [GuestAccessController::class, 'logout'])->name('guest.logout');
Route::post('/guest/ad-access', [GuestAccessController::class, 'adAccess'])->name('guest.ad');
Route::post('/guest/subscription-access', [GuestAccessController::class, 'subscriptionAccess'])->name('guest.subscription');

Route::middleware('auth')->group(function (): void {
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout.get');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/', [PortalController::class, 'index'])->name('portal.index');
    Route::post('/business', [PortalController::class, 'updateBusiness'])->name('portal.business.update');
    Route::post('/branding', [PortalController::class, 'updateBranding'])->name('portal.branding.update');
    Route::post('/packages', [PortalController::class, 'storePackage'])->name('portal.packages.store');
    Route::post('/packages/{package}/toggle', [PortalController::class, 'togglePackage'])->name('portal.packages.toggle');
    Route::post('/routers', [PortalController::class, 'storeRouter'])->name('portal.routers.store');
    Route::post('/devices', [PortalController::class, 'storeDevice'])->name('portal.devices.store');

    Route::prefix('admin/spotipo')->name('spotipo.')->group(function (): void {
        Route::get('/', [SpotipoAdminController::class, 'index'])->name('index');
        Route::post('/test', [SpotipoAdminController::class, 'test'])->name('test');
        Route::get('/vouchers', [SpotipoAdminController::class, 'listVouchers'])->name('vouchers.index');
        Route::post('/vouchers', [SpotipoAdminController::class, 'storeVoucher'])->name('vouchers.store');
        Route::get('/vouchers/{voucherId}', [SpotipoAdminController::class, 'showVoucher'])->name('vouchers.show');
        Route::put('/vouchers/{voucherId}', [SpotipoAdminController::class, 'updateVoucher'])->name('vouchers.update');
        Route::delete('/vouchers/{voucherId}', [SpotipoAdminController::class, 'deleteVoucher'])->name('vouchers.destroy');
        Route::get('/guest-users', [SpotipoAdminController::class, 'listGuestUsers'])->name('guest-users.index');
        Route::post('/guest-users', [SpotipoAdminController::class, 'storeGuestUser'])->name('guest-users.store');
        Route::get('/guest-users/{username}', [SpotipoAdminController::class, 'showGuestUser'])->name('guest-users.show');
        Route::put('/guest-users/{username}', [SpotipoAdminController::class, 'updateGuestUser'])->name('guest-users.update');
        Route::delete('/guest-users/{username}', [SpotipoAdminController::class, 'deleteGuestUser'])->name('guest-users.destroy');
    });
});
