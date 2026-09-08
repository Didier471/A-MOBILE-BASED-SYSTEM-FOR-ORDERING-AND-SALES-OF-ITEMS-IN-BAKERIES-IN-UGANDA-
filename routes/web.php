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
Route::get('/sales', function () {
    return view('sales');
});
Route::get('/payments', function () {
    return view('payments');
});
Route::get('/purchases', function () {
    return view('purchases');
});
Route::get('/deliveries', function () {
    return view('deliveries');
});
Route::get('/customers', function () {
    return view('customers');
});
Route::get('/suppliers', function () {
    return view('suppliers');
});
Route::get('/reports', function () {
    return view('reports');
});
Route::get('/users', function () {
    return view('users');
});