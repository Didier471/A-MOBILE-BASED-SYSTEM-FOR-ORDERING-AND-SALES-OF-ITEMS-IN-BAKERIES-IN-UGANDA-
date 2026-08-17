<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
});
Route::get('/login', function () {
    return view('login');
});Route::get('/products', function () {
    return view('products');
});
Route::get('/inventory', function () {
    return view('inventory');
});
Route::get('/orders', function () {
    return view('orders');
});
Route::get('/orders/{id}', function ($id) {
    return view('order-details');
});