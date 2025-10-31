<?php

//header("Access-Control-Allow-Origin: *");
//header("Access-Control-Allow-Headers: access");
//header("Access-Control-Allow-Methods: POST, GET");
//header("Content-Type: application/json; charset=UTF-8");
//header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\User;

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


Route::middleware('auth:api')->get('/user', function (Request $request) {
     return $request->user();
});


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return response()->json($request->user());
});

Route::post('schedule/CreatedSchedule', 'API\DemoController@CreatedSchedule');
Route::get('schedule/GetAllSchedule', 'API\DemoController@GetAllSchedule');
Route::resource('schedule', DemoController::class);

Route::post('v1/login', 'API\UserController@login');
Route::post('v1/signin', 'API\UserController@signin');
Route::post('user/SendOtpResetPassword', 'API\UserController@SendOtpResetPassword');
Route::post('user/ResetUserPassword', 'API\UserController@ResetUserPassword');
Route::get('user/GetCenterList', 'API\UserController@GetCenterList');
Route::post('user/clearlogin', 'API\UserController@clearlogin');
Route::resource('v1', UserController::class);

Route::middleware(['auth:sanctum'])->group(function () {
    //User 7
    Route::post('user/GetProfile', 'API\UserController@GetProfile');
    Route::post('user/Logout', 'API\UserController@UserLogout');
    Route::post('user/CreateUser', 'API\UserController@CreateUser');
    Route::post('user/UpdatePassword', 'API\UserController@UpdatePassword');
    Route::post('user/GetUserListData', 'API\UserController@GetUserListData');
    Route::post('user/UpdateUser', 'API\UserController@UpdateUser');
    Route::post('user/GetSingleUser', 'API\UserController@GetSingleUser');
    Route::post('user/DeleteUser', 'API\UserController@DeleteUser');
    Route::get('user/GetStaffList', 'API\UserController@GetStaffList');
    Route::get('user/GetDoctorList', 'API\UserController@GetDoctorList');
    Route::get('user/ViewDoctorList', 'API\UserController@ViewDoctorList');
    Route::post('user/GetDoctorByDepartment', 'API\UserController@GetDoctorByDepartment');
    Route::get('user/GetDepartmentDoctorCount', 'API\UserController@GetDepartmentDoctorCount');
    Route::resource('user', UserController::class);

    //City 6
    Route::get('master/GetSingleCity/{id}', 'API\MasterController@GetSingleCity');
    Route::post('master/AddNewCity', 'API\MasterController@AddNewCity');
    Route::post('master/UpdateCity', 'API\MasterController@UpdateCity');
    Route::get('master/GetAllCityList', 'API\MasterController@GetAllCityList');
    Route::post('master/GetCityList', 'API\MasterController@GetCityList');
    Route::post('master/DeleteCity', 'API\MasterController@DeleteCity');

    //Region 7
    Route::get('master/GetSingleRegion/{id}', 'API\MasterController@GetSingleRegion');
    Route::post('master/AddNewRegion', 'API\MasterController@AddNewRegion');
    Route::post('master/UpdateRegion', 'API\MasterController@UpdateRegion');
    Route::get('master/GetAllRegionList', 'API\MasterController@GetAllRegionList');
    Route::post('master/GetRegionList', 'API\MasterController@GetRegionList');
    Route::post('master/DeleteRegion', 'API\MasterController@DeleteRegion');
    Route::get('master/GetHealthAuthorityRegionList', 'API\MasterController@GetHealthAuthorityRegionList');

    //Job Title 5
    Route::get('master/GetSingleJobTitle/{id}', 'API\MasterController@GetSingleJobTitle');
    Route::get('master/GetJobTitlList', 'API\MasterController@GetJobTitlList');
    Route::post('master/AddNewJobTitle', 'API\MasterController@AddNewJobTitle');
    Route::post('master/UpdateJobTitle', 'API\MasterController@UpdateJobTitle');
    Route::post('master/DeleteJobTitle', 'API\MasterController@DeleteJobTitle');

    //Master 4
    Route::get('master/GetCountryList', 'API\MasterController@GetCountryList');
    Route::get('master/GetGenderList', 'API\MasterController@GetGenderList');
    Route::post('master/GetRoleList', 'API\MasterController@GetRoleList');
    Route::get('master/GetHospitalTypeList', 'API\MasterController@GetHospitalTypeList');

    //Title 5
    Route::get('master/GetSingleTitle/{id}', 'API\MasterController@GetSingleTitle');
    Route::post('master/AddNewTitle', 'API\MasterController@AddNewTitle');
    Route::post('master/UpdateTitle', 'API\MasterController@UpdateTitle');
    Route::get('master/GetTitleList', 'API\MasterController@GetTitleList');
    Route::post('master/DeleteTitle', 'API\MasterController@DeleteTitle');

    //Nationality 5
    Route::get('master/GetSingleNationality/{id}', 'API\MasterController@GetSingleNationality');
    Route::post('master/AddNewNationality', 'API\MasterController@AddNewNationality');
    Route::post('master/UpdateNationality', 'API\MasterController@UpdateNationality');
    Route::get('master/GetNationalityList', 'API\MasterController@GetNationalityList');
    Route::post('master/DeleteNationality', 'API\MasterController@DeleteNationality');
    
    //Department 6
    Route::get('master/GetSingleDepartment/{id}', 'API\MasterController@GetSingleDepartment');
    Route::post('master/AddNewDepartment', 'API\MasterController@AddNewDepartment');
    Route::post('master/UpdateDepartment', 'API\MasterController@UpdateDepartment');
    Route::get('master/GetAllDepartmentList', 'API\MasterController@GetAllDepartmentList');
    Route::post('master/GetDepartmentList', 'API\MasterController@GetDepartmentList');
    Route::post('master/DeleteDepartment', 'API\MasterController@DeleteDepartment');

    //Category 5
    Route::get('master/GetDeptCategoryList', 'API\MasterController@GetDeptCategoryList');
    Route::get('master/GetSingleCategory/{id}', 'API\MasterController@GetSingleCategory');
    Route::post('master/AddNewCategory', 'API\MasterController@AddNewCategory');
    Route::post('master/UpdateCategory', 'API\MasterController@UpdateCategory');
    Route::post('master/DeleteCategory', 'API\MasterController@DeleteCategory');

    //Religion 5
    Route::get('master/GetReligionList', 'API\MasterController@GetReligionList');
    Route::get('master/GetSingleReligion/{id}', 'API\MasterController@GetSingleReligion');
    Route::post('master/AddNewReligion', 'API\MasterController@AddNewReligion');
    Route::post('master/UpdateReligion', 'API\MasterController@UpdateReligion');
    Route::post('master/DeleteReligion', 'API\MasterController@DeleteReligion');

    //Education 5
    Route::get('master/GetEducationList', 'API\MasterController@GetEducationList');
    Route::get('master/GetSingleEducation/{id}', 'API\MasterController@GetSingleEducation');
    Route::post('master/AddNewEducation', 'API\MasterController@AddNewEducation');
    Route::post('master/UpdateEducation', 'API\MasterController@UpdateEducation');
    Route::post('master/DeleteEducation', 'API\MasterController@DeleteEducation');

    //Ethnic 5
    Route::get('master/GetEthnicList', 'API\MasterController@GetEthnicList');
    Route::get('master/GetSingleEthnic/{id}', 'API\MasterController@GetSingleEthnic');
    Route::post('master/AddNewEthnic', 'API\MasterController@AddNewEthnic');
    Route::post('master/UpdateEthnic', 'API\MasterController@UpdateEthnic');
    Route::post('master/DeleteEthnic', 'API\MasterController@DeleteEthnic');

    //IncomeRange 5
    Route::get('master/GetIncomeRangeList', 'API\MasterController@GetIncomeRangeList');
    Route::get('master/GetSingleIncomeRange/{id}', 'API\MasterController@GetSingleIncomeRange');
    Route::post('master/AddNewIncomeRange', 'API\MasterController@AddNewIncomeRange');
    Route::post('master/UpdateIncomeRange', 'API\MasterController@UpdateIncomeRange');
    Route::post('master/DeleteIncomeRange', 'API\MasterController@DeleteIncomeRange');

    //Language 5
    Route::get('master/GetLanguageList', 'API\MasterController@GetLanguageList');
    Route::get('master/GetSingleLanguage/{id}', 'API\MasterController@GetSingleLanguage');
    Route::post('master/AddNewLanguage', 'API\MasterController@AddNewLanguage');
    Route::post('master/UpdateLanguage', 'API\MasterController@UpdateLanguage');
    Route::post('master/DeleteLanguage', 'API\MasterController@DeleteLanguage');

    //Occupation 5
    Route::get('master/GetOccupationList', 'API\MasterController@GetOccupationList');
    Route::get('master/GetSingleOccupation/{id}', 'API\MasterController@GetSingleOccupation');
    Route::post('master/AddNewOccupation', 'API\MasterController@AddNewOccupation');
    Route::post('master/UpdateOccupation', 'API\MasterController@UpdateOccupation');
    Route::post('master/DeleteOccupation', 'API\MasterController@DeleteOccupation');

    //PatientClass 5
    Route::get('master/GetPatientClassList', 'API\MasterController@GetPatientClassList');
    Route::get('master/GetSinglePatientClass/{id}', 'API\MasterController@GetSinglePatientClass');
    Route::post('master/AddNewPatientClass', 'API\MasterController@AddNewPatientClass');
    Route::post('master/UpdatePatientClass', 'API\MasterController@UpdatePatientClass');
    Route::post('master/DeletePatientClass', 'API\MasterController@DeletePatientClass');

    //Relationship 5
    Route::get('master/GetRelationshipList', 'API\MasterController@GetRelationshipList');
    Route::get('master/GetSingleRelationship/{id}', 'API\MasterController@GetSingleRelationship');
    Route::post('master/AddNewRelationship', 'API\MasterController@AddNewRelationship');
    Route::post('master/UpdateRelationship', 'API\MasterController@UpdateRelationship');
    Route::post('master/DeleteRelationship', 'API\MasterController@DeleteRelationship');

    //MaritalStatus 5
    Route::get('master/GetMaritalStatusList', 'API\MasterController@GetMaritalStatusList');
    Route::get('master/GetSingleMaritalStatus/{id}', 'API\MasterController@GetSingleMaritalStatus');
    Route::post('master/AddNewMaritalStatus', 'API\MasterController@AddNewMaritalStatus');
    Route::post('master/UpdateMaritalStatus', 'API\MasterController@UpdateMaritalStatus');
    Route::post('master/DeleteMaritalStatus', 'API\MasterController@DeleteMaritalStatus');

    //Insurance Company
    Route::post('master/AddNewInsCompany', 'API\MasterController@AddNewInsCompany');
    Route::post('master/UpdateInsCompany', 'API\MasterController@UpdateInsCompany');
    Route::post('master/GetSingleInsCompany', 'API\MasterController@GetSingleInsCompany');
    Route::get('master/GetInsCompanyList', 'API\MasterController@GetInsCompanyList');
    Route::post('master/UpdateInsCompanyStatus', 'API\MasterController@UpdateInsCompanyStatus');
    Route::post('master/DeleteInsCompany', 'API\MasterController@DeleteInsCompany');
    Route::post('master/ViewInsCompany', 'API\MasterController@ViewInsCompany');
    Route::get('master/GetInsMainCompanyList', 'API\MasterController@GetInsMainCompanyList');
    Route::get('master/GetInsSubCompanyList/{id}', 'API\MasterController@GetInsSubCompanyList');

    //Industry
    Route::post('master/AddNewIndustry', 'API\MasterController@AddNewIndustry');
    Route::post('master/UpdateIndustry', 'API\MasterController@UpdateIndustry');
    Route::post('master/DeleteIndustry', 'API\MasterController@DeleteIndustry');
    Route::get('master/GetIndustryList', 'API\MasterController@GetIndustryList');

    //VisaType
    Route::post('master/AddVisaType', 'API\MasterController@AddVisaType');
    Route::post('master/UpdateVisaType', 'API\MasterController@UpdateVisaType');
    Route::post('master/DeleteVisaType', 'API\MasterController@DeleteVisaType');
    Route::get('master/GetVisaType', 'API\MasterController@GetVisaType');
    Route::get('master/GetSingleVisaType/{id}', 'API\MasterController@GetSingleVisaType');

    //Qualification
    Route::post('master/AddQualification', 'API\MasterController@AddQualification');
    Route::post('master/UpdateQualification', 'API\MasterController@UpdateQualification');
    Route::post('master/DeleteQualification', 'API\MasterController@DeleteQualification');
    Route::get('master/GetQualification', 'API\MasterController@GetQualification');
    Route::get('master/GetSingleQualification/{id}', 'API\MasterController@GetSingleQualification');

    //Qualification
    Route::post('master/AddDoctorProfession', 'API\MasterController@AddDoctorProfession');
    Route::post('master/UpdateDoctorProfession', 'API\MasterController@UpdateDoctorProfession');
    Route::post('master/DeleteDoctorProfession', 'API\MasterController@DeleteDoctorProfession');
    Route::get('master/GetDoctorProfession', 'API\MasterController@GetDoctorProfession');
    Route::get('master/GetSingleDoctorProfession/{id}', 'API\MasterController@GetSingleDoctorProfession');
    
    //CancelReason
    Route::post('master/AddCancelReason', 'API\MasterController@AddCancelReason');
    Route::post('master/UpdateCancelReason', 'API\MasterController@UpdateCancelReason');
    Route::post('master/DeleteCancelReason', 'API\MasterController@DeleteCancelReason');
    Route::get('master/GetCancelReason', 'API\MasterController@GetCancelReason');
    Route::get('master/GetSingleCancelReason/{id}', 'API\MasterController@GetSingleCancelReason');


    //Shift Master
    Route::get('master/GetSingleShift/{id}', 'API\MasterController@GetSingleShift');
    Route::post('master/AddNewShift', 'API\MasterController@AddNewShift');
    Route::post('master/UpdateShift', 'API\MasterController@UpdateShift');
    Route::get('master/GetShiftList', 'API\MasterController@GetShiftList');
    Route::post('master/DeleteShift', 'API\MasterController@DeleteShift');


    Route::get('master/GetBloodGroupList', 'API\MasterController@GetBloodGroupList');
    Route::get('master/GetPaymentModeList', 'API\MasterController@GetPaymentModeList');
    Route::get('master/GetReferralSourceList', 'API\MasterController@GetReferralSourceList');
    Route::get('master/GetReferralChannelList', 'API\MasterController@GetReferralChannelList');
    Route::get('master/GetCompanyTypes', 'API\MasterController@GetCompanyTypes');
    Route::get('master/GetChargeTypes', 'API\MasterController@GetChargeTypes');
    Route::post('master/EnableAdminAccess', 'API\MasterController@EnableAdminAccess');
    Route::post('master/RemoveAdminAccess', 'API\MasterController@RemoveAdminAccess');
    Route::post('master/GetAdminAccessNurse', 'API\MasterController@GetAdminAccessNurse');
    Route::post('master/UpdateDoctorSetting', 'API\MasterController@UpdateDoctorSetting');
    Route::get('master/GetVisitTypeList', 'API\MasterController@GetVisitTypeList');
    Route::get('master/GetExamList', 'API\MasterController@GetExamList');
    Route::post('master/GetMasterLogs', 'API\MasterController@GetMasterLogs');
    Route::resource('master', MasterController::class);

    //Doctor
    Route::post('mastertwo/AddReferralDoctor', 'API\MasterTwoController@AddReferralDoctor');
    Route::post('mastertwo/UpdateReferralDoctor', 'API\MasterTwoController@UpdateReferralDoctor');
    Route::post('mastertwo/DeleteReferralDoctor', 'API\MasterTwoController@DeleteReferralDoctor');
    Route::get('mastertwo/GetSingleReferralDoctor/{id}', 'API\MasterTwoController@GetSingleReferralDoctor');
    Route::get('mastertwo/GetReferralDoctorList', 'API\MasterTwoController@GetReferralDoctorList');
    Route::get('mastertwo/ViewReferralDoctor', 'API\MasterTwoController@ViewReferralDoctor');

    //Clinic
    Route::post('mastertwo/AddReferralClinic', 'API\MasterTwoController@AddReferralClinic');
    Route::post('mastertwo/UpdateReferralClinic', 'API\MasterTwoController@UpdateReferralClinic');
    Route::post('mastertwo/DeleteClinic', 'API\MasterTwoController@DeleteClinic');
    Route::get('mastertwo/GetSingleReferralClinic/{id}', 'API\MasterTwoController@GetSingleReferralClinic');
    Route::get('mastertwo/GetReferralClinicList', 'API\MasterTwoController@GetReferralClinicList');
    Route::get('mastertwo/ViewReferralClinic', 'API\MasterTwoController@ViewReferralClinic');

    //Service
    Route::post('mastertwo/AddEnquiryService', 'API\MasterTwoController@AddEnquiryService');
    Route::post('mastertwo/UpdateEnquiryService', 'API\MasterTwoController@UpdateEnquiryService');
    Route::post('mastertwo/DeleteService', 'API\MasterTwoController@DeleteService');
    Route::get('mastertwo/GetSingleEnquiryService/{id}', 'API\MasterTwoController@GetSingleEnquiryService');
    Route::get('mastertwo/EnquiryServiceList', 'API\MasterTwoController@EnquiryServiceList');

    //Reason
    Route::post('mastertwo/AddEnquiryReason', 'API\MasterTwoController@AddEnquiryReason');
    Route::post('mastertwo/UpdateEnquiryReason', 'API\MasterTwoController@UpdateEnquiryReason');
    Route::post('mastertwo/DeleteReason', 'API\MasterTwoController@DeleteReason');
    Route::get('mastertwo/GetSingleEnquiryReason/{id}', 'API\MasterTwoController@GetSingleEnquiryReason');
    Route::get('mastertwo/EnquiryReasonList', 'API\MasterTwoController@EnquiryReasonList');

    //Insurance Network
    Route::post('mastertwo/AddNetwork', 'API\MasterTwoController@AddNetwork');
    Route::post('mastertwo/UpdateNetwork', 'API\MasterTwoController@UpdateNetwork');
    Route::post('mastertwo/DeleteNetwork', 'API\MasterTwoController@DeleteNetwork');
    Route::get('mastertwo/GetSingleNetwork/{id}', 'API\MasterTwoController@GetSingleNetwork');
    Route::get('mastertwo/GetNetworkInsCompanyWise/{id}', 'API\MasterTwoController@GetNetworkInsCompanyWise');
    Route::get('mastertwo/ViewNetworkList', 'API\MasterTwoController@ViewNetworkList');

    //Insurance Package
    Route::post('mastertwo/AddInsPackage', 'API\MasterTwoController@AddInsPackage');
    Route::post('mastertwo/UpdateInsPackage', 'API\MasterTwoController@UpdateInsPackage');
    Route::post('mastertwo/DeleteInsPackage', 'API\MasterTwoController@DeleteInsPackage');
    Route::get('mastertwo/GetSingleInsPackage/{id}', 'API\MasterTwoController@GetSingleInsPackage');
    Route::get('mastertwo/GetPackageInsCompanyWise/{id}', 'API\MasterTwoController@GetPackageInsCompanyWise');
    Route::get('mastertwo/ViewInsPackageList', 'API\MasterTwoController@ViewInsPackageList');

    //Insurance Plans
    Route::post('mastertwo/AddInsPlan', 'API\MasterTwoController@AddInsPlan');
    Route::post('mastertwo/UpdateInsPlan', 'API\MasterTwoController@UpdateInsPlan');
    Route::post('mastertwo/DeleteInsPlan', 'API\MasterTwoController@DeleteInsPlan');
    Route::get('mastertwo/GetSingleInsPlan/{id}', 'API\MasterTwoController@GetSingleInsPlan');
    Route::get('mastertwo/GetPlanNetworkWise/{id}', 'API\MasterTwoController@GetPlanNetworkWise');
    Route::get('mastertwo/ViewInsPlanList', 'API\MasterTwoController@ViewInsPlanList');

    //Insurance Plan Detail
    Route::post('mastertwo/AddInsPlanDetail', 'API\MasterTwoController@AddInsPlanDetail');
    Route::post('mastertwo/UpdateInsPlanDetail', 'API\MasterTwoController@UpdateInsPlanDetail');
    Route::post('mastertwo/DeleteInsPlanDetail', 'API\MasterTwoController@DeleteInsPlanDetail');
    Route::post('mastertwo/DeletePlanDesc', 'API\MasterTwoController@DeletePlanDesc');
    Route::get('mastertwo/GetSingleInsPlanDetail/{id}', 'API\MasterTwoController@GetSingleInsPlanDetail');
    Route::get('mastertwo/ViewInsPlanDetail', 'API\MasterTwoController@ViewInsPlanDetail');
    Route::post('mastertwo/GetNetworkPlans', 'API\MasterTwoController@GetNetworkPlans'); //Based on Location & Company
    Route::post('mastertwo/CopyPlanDetail', 'API\MasterTwoController@CopyPlanDetail');
    Route::post('mastertwo/GetServiceCategory', 'API\MasterTwoController@GetServiceCategory');

    //SMS Template
    Route::post('mastertwo/AddSMSTemplate', 'API\MasterTwoController@AddSMSTemplate');
    Route::post('mastertwo/UpdateSMSTemplate', 'API\MasterTwoController@UpdateSMSTemplate');
    Route::post('mastertwo/DeleteSmsTemplate', 'API\MasterTwoController@DeleteSmsTemplate');
    Route::get('mastertwo/GetSingleSMSTemplate/{id}', 'API\MasterTwoController@GetSingleSMSTemplate');
    Route::get('mastertwo/ViewSmsTemplate', 'API\MasterTwoController@ViewSmsTemplate');
    
    //Types of Abuse
    Route::post('mastertwo/AddTypesAbuse', 'API\MasterTwoController@AddTypesAbuse');
    Route::post('mastertwo/UpdateTypesAbuse', 'API\MasterTwoController@UpdateTypesAbuse');
    Route::post('mastertwo/DeleteTypesAbuse', 'API\MasterTwoController@DeleteTypesAbuse');
    Route::get('mastertwo/GetSingleTypesAbuse/{id}', 'API\MasterTwoController@GetSingleTypesAbuse');
    Route::get('mastertwo/GetTypesAbuse', 'API\MasterTwoController@GetTypesAbuse');
    
    //RegComments
    Route::post('mastertwo/AddRegComments', 'API\MasterTwoController@AddRegComments');
    Route::post('mastertwo/UpdateRegComments', 'API\MasterTwoController@UpdateRegComments');
    Route::get('mastertwo/GetRegComments', 'API\MasterTwoController@GetRegComments');
    Route::post('mastertwo/DeleteRegComments', 'API\MasterTwoController@DeleteRegComments');
    Route::get('mastertwo/GetSingleRegComments/{id}', 'API\MasterTwoController@GetSingleRegComments');
    Route::get('mastertwo/getAllCommentsByPatientID/{id}', 'API\MasterTwoController@getAllCommentsByPatientID');
    Route::get('mastertwo/GetCancelledRegComments', 'API\MasterTwoController@GetCancelledRegComments');

    //Appointment Status
    Route::post('mastertwo/AddAppointmentStatus', 'API\MasterTwoController@AddAppointmentStatus');
    Route::post('mastertwo/UpdateAppointmentStatus', 'API\MasterTwoController@UpdateAppointmentStatus');
    Route::post('mastertwo/DeleteAppointmentStatus', 'API\MasterTwoController@DeleteAppointmentStatus');
    Route::get('mastertwo/GetSingleAppointmentStatus/{id}', 'API\MasterTwoController@GetSingleAppointmentStatus');
    Route::get('mastertwo/GetAppointmentStatusList', 'API\MasterTwoController@GetAppointmentStatusList');
    Route::get('mastertwo/ViewAppointmentStatus', 'API\MasterTwoController@ViewAppointmentStatus');

    Route::resource('mastertwo', MasterTwoController::class);

    //Hospital 6
    Route::get('hospital/GetClientList', 'API\HospitalDetailController@GetBranchList');
    Route::post('hospital/GetClientDetailData', 'API\HospitalDetailController@GetClientDetailData');
    Route::post('hospital/CreateClient', 'API\HospitalDetailController@CreateClient');
    Route::post('hospital/UpdateClient', 'API\HospitalDetailController@UpdateClient');
    Route::post('hospital/GetSingleClient', 'API\HospitalDetailController@GetSingleClient');
    Route::post('hospital/DeleteClient', 'API\HospitalDetailController@DeleteClient');
    Route::get('hospital/GetHospitalSettingData/{id}', 'API\HospitalDetailController@GetHospitalSettingData');
    Route::post('hospital/SaveHospitalSetting', 'API\HospitalDetailController@SaveHospitalSetting');
    Route::post('hospital/HospitalDetailsSetting', 'API\HospitalDetailController@HospitalDetailsSetting');
    Route::get('hospital/GetHospitalDetails/{id}', 'API\HospitalDetailController@GetHospitalDetails');
    Route::resource('hospital', HospitalDetailController::class);

    //Dashboard menu Module 9
    Route::post('dashboard/CreateDashboardMenu', 'API\DashboardModuleController@CreateDashboardMenu');
    Route::post('dashboard/UpdateDashboardMenu', 'API\DashboardModuleController@UpdateDashboardMenu');
    Route::post('dashboard/ReorderDashboardMenu', 'API\DashboardModuleController@ReorderDashboardMenu');
    Route::post('dashboard/DashboardMenuList', 'API\DashboardModuleController@DashboardMenuList');
    Route::get('dashboard/GetSingleDashboardMenu/{id}', 'API\DashboardModuleController@GetSingleDashboardMenu');
    Route::post('dashboard/DeleteDashboardModule', 'API\DashboardModuleController@DeleteDashboardModule');
    Route::post('dashboard/AssignDashboardMenu', 'API\DashboardModuleController@AssignDashboardMenu');
    Route::post('dashboard/DisplayDashboardMenu', 'API\DashboardModuleController@DisplayDashboardMenu');
    Route::post('dashboard/GetRoleWiseDashboardMenu', 'API\DashboardModuleController@GetRoleWiseDashboardMenu');
    Route::resource('dashboard', DashboardModuleController::class);

    //Main menu Module 9
    Route::post('mainmenu/CreateMainMenu', 'API\MainModuleController@CreateMainMenu');
    Route::post('mainmenu/UpdateMainMenu', 'API\MainModuleController@UpdateMainMenu');
    Route::post('mainmenu/ReorderMainMenu', 'API\MainModuleController@ReorderMainMenu');
    Route::post('mainmenu/MainMenuList', 'API\MainModuleController@MainMenuList');
    Route::get('mainmenu/GetMainMenuList', 'API\MainModuleController@GetMainMenuList'); //Use it in sub menu 
    Route::get('mainmenu/GetSingleMainMenu/{id}', 'API\MainModuleController@GetSingleMainMenu');
    Route::post('mainmenu/GetRoleWiseMainMenu', 'API\MainModuleController@GetRoleWiseMainMenu');
    Route::post('mainmenu/DeleteMainMenu', 'API\MainModuleController@DeleteMainMenu');
    Route::post('mainmenu/AssignMainMenu', 'API\MainModuleController@AssignMainMenu');
    Route::resource('mainmenu', MainModuleController::class);

    //Sub menu Module 9
    Route::post('submenu/CreateSubMenu', 'API\SubModuleController@CreateSubMenu');
    Route::post('submenu/UpdateSubMenu', 'API\SubModuleController@UpdateSubMenu');
    Route::post('submenu/ReorderSubMenu', 'API\SubModuleController@ReorderSubMenu');
    Route::post('submenu/SubMenuList', 'API\SubModuleController@SubMenuList');
    Route::get('submenu/GetSingleSubMenu/{id}', 'API\SubModuleController@GetSingleSubMenu');
    Route::get('submenu/GetMenuWiseSubMenu/{id}', 'API\SubModuleController@GetMenuWiseSubMenu');
    Route::post('submenu/GetRoleWiseSubMenu', 'API\SubModuleController@GetRoleWiseSubMenu');
    Route::post('submenu/DeleteSubMenu', 'API\SubModuleController@DeleteSubMenu');
    Route::post('submenu/AssignSubMenu', 'API\SubModuleController@AssignSubMenu');
    Route::post('submenu/GetSideMenuList', 'API\SubModuleController@SideMenuList');
    Route::resource('submenu', SubModuleController::class);


    Route::post('enquiry/ViewPatientEnquiry', 'API\PatientEnquiryController@ViewPatientEnquiry');
    Route::post('enquiry/AddPatientEnquiry', 'API\PatientEnquiryController@AddPatientEnquiry');
    Route::post('enquiry/UpdatePhoneEnquiryStatus', 'API\PatientEnquiryController@UpdatePhoneEnquiryStatus');
    Route::post('enquiry/GetPhoneByAppointment', 'API\PatientEnquiryController@GetPhoneByAppointment');
    Route::post('enquiry/DeletePhoneEnquiry', 'API\PatientEnquiryController@DeletePhoneEnquiry');
    Route::post('enquiry/AddPhoneEnquiry', 'API\PatientEnquiryController@AddPhoneEnquiry');
    Route::post('enquiry/UpdatePhoneEnquiry', 'API\PatientEnquiryController@UpdatePhoneEnquiry');
    Route::post('enquiry/AddWaitingList', 'API\PatientEnquiryController@AddWaitingList');
    Route::post('enquiry/ViewWaitingList', 'API\PatientEnquiryController@ViewWaitingList');
    Route::post('enquiry/DeleteWaitingList', 'API\PatientEnquiryController@DeleteWaitingList');
    Route::post('enquiry/UpdatePatientEnquiry', 'API\PatientEnquiryController@UpdatePatientEnquiry');
    Route::get('enquiry/GetSinglePatientEnquiry/{id}', 'API\PatientEnquiryController@GetSinglePatientEnquiry');
    Route::post('enquiry/DeletePatientEnquiry', 'API\PatientEnquiryController@DeletePatientEnquiry');
    Route::resource('enquiry', PatientEnquiryController::class);

    //Patient Module
    Route::post('patient/CreatePatient', 'API\PatientDetailController@CreatePatient');
    Route::post('patient/UpdatePatient', 'API\PatientDetailController@UpdatePatient');
    Route::post('patient/GetSinglePatient', 'API\PatientDetailController@GetSinglePatient');
    Route::post('patient/SavePatientSubdData', 'API\PatientDetailController@SavePatientSubdData');
    Route::post('patient/ViewPatientList', 'API\PatientDetailController@ViewPatientList');
    Route::post('patient/SearchPatientRelation', 'API\PatientDetailController@SearchPatientRelation');
    Route::post('patient/SaveReferral', 'API\PatientDetailController@SaveReferral');
    Route::post('patient/SaveOthers', 'API\PatientDetailController@SaveOthers');
    Route::post('patient/SavePatientInsurance', 'API\PatientDetailController@SavePatientInsurance');
    Route::post('patient/CheckPolicyNumber', 'API\PatientDetailController@CheckPolicyNumber');
    Route::post('patient/CheckMrNumber', 'API\PatientDetailController@CheckMrNumber');
    Route::post('patient/CheckEmirateId', 'API\PatientDetailController@CheckEmirateId');
    Route::post('patient/DeletePatientInsurance', 'API\PatientDetailController@DeletePatientInsurance');
    Route::post('patient/SearchPatientName', 'API\PatientDetailController@SearchPatientName');
    Route::get('patient/GetPatientProfile/{id}', 'API\PatientDetailController@GetPatientProfile');
    Route::get('patient/GetPatientInsurance/{id}', 'API\PatientDetailController@GetPatientInsurance');
    Route::resource('patient', PatientDetailController::class);


    Route::get('appointment/GetSingleBlockAppointment/{id}', 'API\AppointmentController@GetSingleBlockAppointment');
    Route::post('appointment/AddBlockAppointment', 'API\AppointmentController@AddBlockAppointment');
    Route::post('appointment/UpdateBlockAppointment', 'API\AppointmentController@UpdateBlockAppointment');
    Route::post('appointment/ViewBlockAppointment', 'API\AppointmentController@ViewBlockAppointment');
    Route::post('appointment/DeleteBlockAppointment', 'API\AppointmentController@DeleteBlockAppointment');

    Route::get('appointment/GetSingleBlockTempAppointment/{id}', 'API\AppointmentController@GetSingleBlockTempAppointment');
    Route::post('appointment/AddBlockTempAppointment', 'API\AppointmentController@AddBlockTempAppointment');
    Route::post('appointment/UpdateBlockTempAppointment', 'API\AppointmentController@UpdateBlockTempAppointment');
    Route::post('appointment/ViewBlockTempAppointment', 'API\AppointmentController@ViewBlockTempAppointment');
    Route::post('appointment/DeleteBlockTempAppointment', 'API\AppointmentController@DeleteBlockTempAppointment');
    Route::post('appointment/ViewAppointment', 'API\AppointmentController@ViewAppointment');
    Route::post('appointment/BookAppointment', 'API\AppointmentController@BookAppointment');
    Route::post('appointment/UpdateAppointmentStatus', 'API\AppointmentController@UpdateAppointmentStatus');
    Route::post('appointment/ViewPatientAppointment', 'API\AppointmentController@ViewPatientAppointment');
    Route::post('appointment/GetSingleAppointment', 'API\AppointmentController@GetSingleAppointment');
    Route::post('appointment/UpdateAppointment', 'API\AppointmentController@UpdateAppointment');
    Route::post('appointment/DeleteAppointment', 'API\AppointmentController@DeleteAppointment');
    Route::post('appointment/GetAppointmentLogs', 'API\AppointmentController@GetAppointmentLogs');
    Route::post('appointment/AddBlockPatientAppointment', 'API\AppointmentController@AddBlockPatientAppointment');
    Route::post('appointment/UpdateBlockPatientAppointment', 'API\AppointmentController@UpdateBlockPatientAppointment');
    Route::post('appointment/ViewBlockPatientByDoctor', 'API\AppointmentController@ViewBlockPatientByDoctor');
    Route::post('appointment/CheckAppointmentExistence', 'API\AppointmentController@CheckAppointmentExistence');
    Route::resource('appointment', AppointmentController::class);


    Route::post('emirate/AddEmirateDeatil', 'API\EmirateMasterController@AddEmirateDeatil');
    Route::post('emirate/GetSingleEmirateDeatil', 'API\EmirateMasterController@GetSingleEmirateDeatil');
    Route::resource('emirate', EmirateMasterController::class);

});
