<?php

use Illuminate\Support\Facades\Route;
use Jgabboud\Subscriptions\Http\Controllers\SubscriptionController;

Route::get('subscription', function(){
    echo "Hello from subscription";
});

Route::get('add/{a}/{b}',[SubscriptionController::class, 'add']);
Route::get('subtract/{a}/{b}',[SubscriptionController::class, 'subtract']);