<?php

use Illuminate\Support\Facades\Route;

Route::prefix('admin')->group(function () {
    include 'api/admin.php';
});

Route::prefix('user')->group(function () {
    include 'api/user.php';
});
