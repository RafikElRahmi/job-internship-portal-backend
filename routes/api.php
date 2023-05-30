<?php

use App\Http\Controllers\PostsController;
use App\Http\Controllers\PostulationsController;
use App\Http\Controllers\PostulationsUserController;
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


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', [UserController::class, 'store']);
Route::post('/login', [UserController::class, 'login']);
Route::post('/homeadmin', [UserController::class, 'dashboard']);
Route::post('/homesociety', [UserController::class, 'dashboardsociety']);
Route::post('/getphoto', [UserController::class, 'update']);
Route::post('/setphoto', [UserController::class, 'upload']);
Route::post('/refresh', [UserController::class, 'refresh']);
Route::post('/getadmins', [UserController::class, 'getadmins']);
Route::post('/getusers', [UserController::class, 'getallusers']);
Route::post('/getsocieties', [UserController::class, 'getallsocieties']);
Route::post('/getuser', [UserController::class, 'getuser']);
Route::post('/deleteuser', [UserController::class, 'deleteuser']);
Route::post('/deleteadmin', [UserController::class, 'deleteadmin']);
Route::post('/upgradeuser', [UserController::class, 'upgradeuser']);
Route::post('/upgradesociety', [PostulationsController::class, 'store']);
Route::post('/checkupgrade', [PostulationsController::class, 'check']);
Route::post('/getadminpostulations', [PostulationsController::class, 'getall']);
Route::post('/editsocietypostulation', [PostulationsController::class, 'editstate']);
Route::post('/search', [PostsController::class, 'search']);
Route::post('/addpost', [PostsController::class, 'create']);
Route::post('/modifypost', [PostsController::class, 'update']);
Route::post('/deletepost', [PostsController::class, 'delete']);
Route::post('/myposts', [PostsController::class, 'getmine']);
Route::post('/postsuser', [PostsController::class, 'usergetall']);
Route::post('/filterposts', [PostsController::class, 'filter']);
Route::post('/postulateuser', [PostulationsUserController::class, 'store']);
Route::post('/userpostulations', [PostulationsUserController::class, 'getforuser']);
Route::post('/societypostulations', [PostulationsUserController::class, 'getforsociety']);
Route::post('/getpost', [PostulationsUserController::class, 'getpostulation']);
Route::post('/getpostsociety', [PostulationsUserController::class, 'getpostulationsociety']);
Route::post('/societypostulations', [PostulationsUserController::class, 'getforsociety']);
Route::post('/deletepostulation', [PostulationsUserController::class, 'deletepostulation']);
Route::post('/editpostulation', [PostulationsUserController::class, 'editpostulationsociety']);
Route::post('/downloadcv', [PostulationsUserController::class, 'downloadcv']);
Route::post('/postprofile', [UserController::class, 'getprofile']);
