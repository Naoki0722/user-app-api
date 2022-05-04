<?php

use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// 会員登録時に必要な上下関係を取得
Route::get('/user/all', [UserController::class, 'fetchAllUser']);

Route::group(['middleware' => ['auth:sanctum']], function () {
    // Route::get('/user', [UserController::class, 'get']);
    Route::prefix('user')->group(function () {
        Route::get('/', function (Request $request) {
            return $request->user();
        });
        Route::post('/{id}', [UserController::class, 'editUser']);
        Route::delete('/', [UserController::class, 'delete']);
        Route::get('person/{id}', [UserController::class, 'person']);
        Route::get('/{id}', [UserController::class, 'show']);
    });
});
