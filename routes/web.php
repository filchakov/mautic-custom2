<?php

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

/*Route::get('/', function () {
    return view('welcome');
});*/


//Route::get('', 'HomeController@index')->name('home');
Route::get('', function (){
    return redirect('/email');
});
Auth::routes();

Route::get('/api/email/{id}', '\App\Http\Controllers\EmailController@api_get_markup')->name('email.api_get_markup');

Route::group(['middleware'=> ['web','auth']],function(){
    Route::get('/email/{email_id}/project/{project_id}', '\App\Http\Controllers\EmailController@builder')->name('email.builder');
    Route::post('/email/save_image', '\App\Http\Controllers\EmailController@save_image')->name('email.save_image');

});

//project Routes
Route::group(['middleware'=> ['web','auth']], function(){
  Route::resource('project','\App\Http\Controllers\ProjectController');
  Route::post('project/{id}/update','\App\Http\Controllers\ProjectController@update');
  Route::get('project/{id}/delete','\App\Http\Controllers\ProjectController@destroy');
  Route::get('project/{id}/deleteMsg','\App\Http\Controllers\ProjectController@DeleteMsg');
});

//email Routes
Route::group(['middleware'=> ['web','auth']],function(){
  Route::resource('email','\App\Http\Controllers\EmailController');
  Route::post('email/{id}','\App\Http\Controllers\EmailController@update');
  Route::get('email/{id}/customize','\App\Http\Controllers\EmailController@customize')->name('email.customize');
  Route::get('email/{id}/delete','\App\Http\Controllers\EmailController@destroy');
  Route::get('email/{id}','\App\Http\Controllers\EmailController@show')->name('email.show');
  Route::get('email/{id}/deleteMsg','\App\Http\Controllers\EmailController@DeleteMsg');
});
