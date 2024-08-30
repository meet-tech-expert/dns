<?php

use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoUserController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\ResetController;
use App\Http\Controllers\SessionsController;
use App\Http\Controllers\DNSController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


Route::group(['middleware' => 'auth'], function () {

    Route::get('/', [HomeController::class, 'home']);
    Route::get('dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    Route::get('billing', function () {
        return view('billing');
    })->name('billing');

    Route::get('profile', function () {
        return view('profile');
    })->name('profile');

    Route::get('rtl', function () {
        return view('rtl');
    })->name('rtl');

    Route::get('user-management', function () {
        return view('laravel-examples/user-management');
    })->name('user-management');

    Route::get('tables', function () {
        return view('tables');
    })->name('tables');

    Route::get('virtual-reality', function () {
        return view('virtual-reality');
    })->name('virtual-reality');

    Route::get('static-sign-in', function () {
        return view('static-sign-in');
    })->name('sign-in');

    Route::get('static-sign-up', function () {
        return view('static-sign-up');
    })->name('sign-up');

    Route::get('/logout', [SessionsController::class, 'destroy']);
    Route::get('/user-profile', [InfoUserController::class, 'create']);

    Route::get('/dns', [DnsController::class, 'view'])->name('dns.view');

    Route::get('/dns/{domainName}', [DnsController::class, 'edit'])->name('dns.get');
    Route::get('/dns/{domainName}/get_records', [DnsController::class, 'get'])->name('dns.get_records');

    Route::get('/dns/{domainName}/add', [DnsController::class, 'add'])->name('dns.add');

    Route::post('/dns/{domainName}/create', [DnsController::class, 'create'])->name('dns.create');

    Route::get('/dns/{domainName}/edit/{recordId}', [DnsController::class, 'edit'])->name('dns.edit');

    Route::put('/dns/{domainName}/update/{recordId}', [DnsController::class, 'update'])->name('dns.update');

    Route::delete('/dns/{domainName}/delete/{recordId}', [DnsController::class, 'delete'])->name('dns.delete');

    Route::post('/user-profile', [InfoUserController::class, 'store']);
    Route::get('/login', function () {
        return view('dashboard');
    })->name('sign-up');
});



Route::group(['middleware' => 'guest'], function () {
    Route::get('/register', [RegisterController::class, 'create']);
    Route::post('/register', [RegisterController::class, 'store']);
    Route::get('/login', [SessionsController::class, 'create']);
    Route::post('/session', [SessionsController::class, 'store']);
    Route::get('/login/forgot-password', [ResetController::class, 'create']);
    Route::post('/forgot-password', [ResetController::class, 'sendEmail']);
    Route::get('/reset-password/{token}', [ResetController::class, 'resetPass'])->name('password.reset');
    Route::post('/reset-password', [ChangePasswordController::class, 'changePassword'])->name('password.update');

    Route::get('/login/{social}', [RegisterController::class, 'socialLogin'])->where('social', 'twitter|facebook|google');
    Route::get('/login/{social}/callback', [RegisterController::class, 'handleProviderCallback'])->where('social', 'twitter|facebook|google');
});

Route::get('/login', function () {
    return view('session/login-session');
})->name('login');
