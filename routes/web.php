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

Route::post('/webhooks/lead', 'WebhookController@create')->name('lead.create');

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
  Route::get('project/{id}/matcher_to_emails','\App\Http\Controllers\ProjectController@matcher_to_emails')->name('project.matcher_to_emails');
  Route::post('project/copy_emails','\App\Http\Controllers\ProjectController@copy_emails')->name('project.copy_emails');
});

Route::post('email/tests','\App\Http\Controllers\EmailController@tests')->name('email.tests');
//email Routes
Route::group(['middleware'=> ['web','auth']],function(){
  Route::resource('email','\App\Http\Controllers\EmailController');
  Route::post('email/create','\App\Http\Controllers\EmailController@create');
  Route::post('email/{id}','\App\Http\Controllers\EmailController@update');
  Route::get('email/{id}/customize','\App\Http\Controllers\EmailController@customize')->name('email.customize');
  Route::get('email/{id}/stats','\App\Http\Controllers\EmailController@get_stats_email')->name('email.stats');
  Route::get('email/{id}/test_campaign','\App\Http\Controllers\EmailController@test_campaign')->name('email.test_campaign');
  Route::post('email/{id}/test_campaign','\App\Http\Controllers\EmailController@create_test_campaign')->name('email.create_test_campaign');
  Route::get('email/{id}/delete','\App\Http\Controllers\EmailController@destroy')->name('email.delete');
  Route::get('email/{id}','\App\Http\Controllers\EmailController@show')->name('email.show');
  Route::get('email/{id}/deleteMsg','\App\Http\Controllers\EmailController@DeleteMsg');
  Route::get('email/{id}/bounced','\App\Http\Controllers\EmailController@bounced')->name('email.bounced');
});

//segments
Route::group(['middleware'=> ['web','auth']],function(){
    Route::get('segment/count_leads', '\App\Http\Controllers\SegmentController@count_leads')->name('segment.count_leads');
    Route::post('segment/{id}/update','\App\Http\Controllers\SegmentController@update')->name('segment.update_main');
    Route::get('segment/count_leads_by_alias','\App\Http\Controllers\SegmentController@count_leads_by_alias')->name('segment.count_leads_by_alias');
    Route::resource('segment','\App\Http\Controllers\SegmentController');
    Route::get('segment/{id}/projects','\App\Http\Controllers\SegmentController@projects')->name('segment.projects');
    Route::get('segment/{id}/setting','\App\Http\Controllers\SegmentController@setting')->name('segment.setting');
    Route::get('segment/{id}/new_project/{project_id}','\App\Http\Controllers\SegmentController@create_for_project')->name('segment.create_for_project');
});