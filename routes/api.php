<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Auth Routes
Route::post("/signup", 'App\Http\Controllers\AuthController@store');
Route::post("/verify", 'App\Http\Controllers\AuthController@verify')->name('email.verify');
Route::post("/resend-otp", 'App\Http\Controllers\AuthController@resend');
Route::post("/login", 'App\Http\Controllers\AuthController@login');
Route::post("/logout", 'App\Http\Controllers\AuthController@logout')->middleware("auth:sanctum");

// Authenticated Routes
Route::group(["middleware" => ['auth:sanctum']], function () {
    // Expenses
    Route::post("/expense", 'App\Http\Controllers\ExpenseController@store');
    Route::get("/expense", 'App\Http\Controllers\ExpenseController@index');
    Route::get("/expense/{id}", 'App\Http\Controllers\ExpenseController@show');
    Route::put("/expense/{id}", 'App\Http\Controllers\ExpenseController@update');
    Route::delete("/expense/{id}", 'App\Http\Controllers\ExpenseController@destroy');
    Route::post("/expense/add-to-category/{expense_id}/{category_id}", 'App\Http\Controllers\ExpenseController@add_to_category');

    // Income
    Route::post("/income", "App\Http\Controllers\IncomeController@store");
    Route::get("/income", "App\Http\Controllers\IncomeController@index");
    Route::put("/income/{id}", "App\Http\Controllers\IncomeController@update");
    Route::delete("/income/{id}", "App\Http\Controllers\IncomeController@destroy");

    // Categories
    Route::post("/category", "App\Http\Controllers\CategoryController@store");
    Route::get("/category", 'App\Http\Controllers\CategoryController@index');
    Route::put("/category/{id}", 'App\Http\Controllers\CategoryController@update');
    Route::delete("/category/{id}", 'App\Http\Controllers\CategoryController@destroy');

    // Scheduled process routes
    Route::post("/schedule-income", "App\Http\Controllers\ScheduleController@store_income");
    Route::post("/schedule-expense", "App\Http\Controllers\ScheduleController@store_expenses");

    // Reports
    Route::get("/net-savings", "App\Http\Controllers\ReportsController@check_net_savings");
});
