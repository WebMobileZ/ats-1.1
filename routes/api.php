<?php

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

use App\User;

Route::post('register','Auth\RegisterController@registerUser');
Route::post('login','Auth\LoginController@loginUser');
Route::post('logout','Auth\LogoutController@logoutUser');

// Route::middleware('auth:sanctum')->get('/', function (Request $request) {
//     return response()->json(['data' => 'I am logged in']);
// });

Route::group(['middleware' => ['auth:sanctum','role:admin|recruiter|accountmanager|a-b-manager|adminhunters|bdm|head-hunters|bench-sales|jr-bench-sales|sales-lead']], function () {
    // Route::get('/userlist',function(){
    //     return response()->json(['data' => 'I am logged in admin',
    //                              'role' => Auth::user()->getRoleNames()
    //                             ]);
    // });
    Route::resource('profile', 'Admin\DashboardController');
});
Route::group(['middleware' => ['auth:sanctum','role:admin|recruiter|accountmanager|a-b-manager|adminhunters|bdm|head-hunters|bench-sales|jr-bench-sales|sales-lead']], function () {
    // Route::get('/userlist',function(){
    //     return response()->json(['data' => 'I am logged in admin',
    //                              'role' => Auth::user()->getRoleNames()
    //                             ]);
    // });

    Route::get('getOnlyTechnologies', 'Admin\TechnologiesController@getOnlyTechnologies');
    Route::get('getHotList', 'Admin\ConsultantsController@getHotList');
    Route::get('getHotListKeyword', 'Admin\ConsultantsController@getHotListKeyword');
    Route::get('getHotListOnly', 'Admin\ConsultantsController@getHotListOnly');

    Route::get('getExportHotList', 'Admin\ConsultantsController@getExportHotList');
    Route::get('getExportPrimeVendors', 'Admin\ConsultantsController@getExportPrimeVendors');
    Route::get('getAllExportVendors', 'Admin\ConsultantsController@getAllExportVendors');
    
    Route::resource('technologies', 'Admin\TechnologiesController');
    Route::resource('clients', 'Admin\ClientsController');
    Route::resource('companies', 'Admin\CompaniesController');
    Route::resource('consultants', 'Admin\ConsultantsController');
    Route::resource('contacts', 'Admin\ContactsController');

    Route::get('documentsconsultants', 'Admin\ConsultantsController@documentsconsultants');


    Route::post('contactsDetails', 'Admin\ContactsController@getDetails');
    Route::get('getUsers', 'Admin\JobsController@getUsers');


    Route::post('getTotalInterviewShecdules', 'Admin\SubmissionsController@getTotalInterviewShecdules');
    Route::resource('submissions', 'Admin\SubmissionsController');
    Route::get('getMySubmissions', 'Admin\SubmissionsController@getMySubmissions');
    Route::get('getConsultantsList', 'Admin\SubmissionsController@getConsultans');
    Route::get('getConsultantsOnly', 'Admin\SubmissionsController@getConsultansOnly');
    Route::get('interviewsubmissions', 'Admin\SubmissionsController@interviewsubmissions');
    Route::post('emailsent', 'Admin\SubmissionsController@emailsent');

    Route::resource('vendor-company-contacts', 'Admin\VendorCompanyContactListController');

    Route::resource('jobs', 'Admin\JobsController');
    Route::get('getUserAssignJobs', 'Admin\JobsController@getUserAssignJobs');


    Route::get('jobreports', 'Admin\ReportsController@jobreports');
    Route::get('getInterviewreports', 'Admin\ReportsController@getInterviewreports');

    Route::get('getTargets', 'Admin\ReportsController@getTargets');
    Route::get('getApplicantReports', 'Admin\ReportsController@getApplicantReports');
    Route::get('getBenchtalentSubmissionReport', 'Admin\ReportsController@getBenchtalentSubmissionReport');
    Route::resource('user-list', 'Admin\UserListController');

    Route::resource('prime-vendors', 'Admin\PrimeVendorsController');
    Route::resource('vendors', 'Admin\VendorsController');
    Route::resource('partners', 'Admin\PartnersController');
    Route::post('saveDocument', 'Admin\ConsultantsController@saveDocument');
    Route::post('statusChange', 'Admin\ConsultantsController@statusChange');
    Route::post('status-consultant-hotlist', 'Admin\ConsultantsController@statushotlist');

    Route::get('getActiveJobReportsforAdmin', 'Admin\ReportsController@getActiveJobReportsforAdmin');
    Route::get('activeBenchTalents', 'Admin\ReportsController@activeBenchTalents');
    Route::get('activeBenchSales', 'Admin\ReportsController@activeBenchSales');
/* Head Hunter Dashboard Reports */
// Geting consultant submited to admin
    Route::get('getSubmitConsultants', 'Admin\ReportsController@getSubmitConsultants');
// Geting consultant submited to admin then hotlist approve consultants
    Route::get('getMyHotlistConsultants', 'Admin\ReportsController@getMyHotlistConsultants');
    Route::get('getJobsConsultants', 'Admin\ReportsController@getJobsConsultants');
/* Head Hunter Dashboard Reports */
Route::get('getTotalSubmissionsbyuser', 'Admin\ReportsController@getTotalSubmissionsbyuser');
Route::get('getBenchsalesInterviewreports', 'Admin\ReportsController@getBenchsalesInterviewreports');
Route::get('getHeadhunterUsers', 'Admin\ReportsController@getHeadhunterUsers');
Route::get('getInterviewsConsultants', 'Admin\ReportsController@getInterviewsConsultants');
Route::get('getBenchUsers', 'Admin\SubmissionsController@getUsers');

});
Route::group(['middleware' => ['auth:sanctum','role:admin|recruiter|accountmanager|a-b-manager|adminhunters|bdm|head-hunters|bench-sales|jr-bench-sales|sales-lead']], function () {
});

// Route::post('users', function (Request $request) {
//     //return response()->json(['requestUsers' => $request->all()]);
//     return response()->json(['users' => User::all(), 'status' => 200]);
// });

