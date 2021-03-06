<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Support\Facades\App;

Route::get('/', [
    'as' => 'home',
    'uses' => 'HomeController@index'
]);

Route::controller('member', 'MemberController', array(
    'getIndex' => 'member.list',
    'getLogin' => 'member.login',
    'postLogin' => 'member.login',
    'getRegister' => 'member.register',
    'postRegister' => 'member.register',
    'getConfirm' => 'member.confirm',
    'getResend' => 'member.resend',
    'postResend' => 'member.resend',
    'getForgotPassword' => 'member.forgot-password',
    'postForgotPassword' => 'member.forgot-password',
    'getResetPassword' => 'member.reset-password',
    'postResetPassword' => 'member.reset-password',
    'getChangePassword' => 'member.change-password',
    'postChangePassword' => 'member.change-password',
    'getProfile' => 'member.profile',
    'getEditProfile' => 'member.edit-profile',
    'postEditProfile' => 'member.edit-profile',
    'getEditOtherProfile' => 'member.edit-other-profile',
    'postEditOtherProfile' => 'member.edit-other-profile',
    'getLogout' => 'member.logout'
));

Route::controller('api', 'ApiController');

Route::resource('course', 'CourseController');
Route::resource('card', 'CardController');
Route::delete('signin/destroy/{id}', array(
    'as' => 'signin.destroy',
    'uses' => 'SigninController@destroy'
));
Route::get('signin/create/{courseId}', array(
    'as' => 'signin.create',
    'uses' => 'SigninController@create'
));
Route::post('signin/store/{courseId}', array(
    'as' => 'signin.store',
    'uses' => 'SigninController@store'
));

//學生會開票系統
Route::resource('candidate', 'CandidateController');
Route::resource('booth', 'BoothController');
Route::controller('vote', 'VoteController');
Route::controller('vote-api', 'VoteApiController', array(
    'anyVote' => 'vote-api.vote',
));

//Markdown API
Route::any('markdown', [
    'as' => 'markdown.preview',
    'uses' => 'MarkdownApiController@markdownPreview'
]);

//記錄檢視器
Route::group(['middleware' => 'staff'], function () {
    Route::get('logs', '\Rap2hpoutre\LaravelLogViewer\LogViewerController@index');
});

Route::controller('export', 'ExportController', array(
    'getSigninList' => 'export.signin-list'
));

//未定義路由
Route::get('{all}', array(
    'as' => 'not-found',
    function () {
        abort(404);
    }
))->where('all', '.*');
