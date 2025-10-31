<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\City;
use App\Region;
use App\Country;
use App\Role;
use App\Department;
use App\Gender;
use App\User;
use App\Title;
use App\JobTitle;
use App\DeptCategory;
use App\UserAuditLog;
use App\HospitalType;
use App\Nationality;
use App\Religion;
use App\Education;
use App\Ethnic;
use App\IncomeRange;
use App\Language;
use App\Occupation;
use App\PatientClass;
use App\Relationship;
use App\MaritalStatus;
use App\BloodGroup;
use App\PaymentMode;
use App\ReferralChannel;
use App\ReferralSource;
use App\Industry;
use App\InsuranceCompanyDetail;
use App\ViewInsuranceCompanyDetail;
use App\CompanyType;
use App\ChargeType;
use App\ShiftMaster;
use App\DoctorSetting;
use App\AdminAccess;
use App\ViewAdminAccess;
use App\VisaType;
use App\Qualification;
use App\VisitType;
use App\Exam;
use App\DoctorProfession;
use App\CancelReason;
use App\DoctorFee;
use App\MasterLog;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use \Validator;


class MasterController extends BaseController
{
    public function GetCountryList(Request $request) {
        try {

            $country = Country::SELECT('id', 'name', 'short_name')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetCountryList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetRegionList(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'country_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $region = Region::SELECT('id', 'name', 'short_name')
                        ->WHERE(['country_id'=> $request->country_id, 'is_active' => 1, 'is_health_autority' => 0])
                        ->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $region);
            
        } catch(\Exception $e) {
            Log::debug("API GetRegionList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetHealthAuthorityRegionList(Request $request) {
        try {

            $region = Region::SELECT('id', 'name', 'short_name')
                        ->WHERE(['is_active' => 1, 'is_health_autority' => 1])
                        ->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $region);
            
        } catch(\Exception $e) {
            Log::debug("API GetRegionList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetCityList(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'region_id' => 'required|integer',
                'country_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $city = City::SELECT('id', 'name', 'short_name')
                        ->WHERE(['country_id'=> $request->country_id, 'region_id'=> $request->region_id, 'is_active' => 1])
                        ->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $city);
            
        } catch(\Exception $e) {
            Log::debug("API GetCityList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetAllCityList(Request $request) {
        try {

            $city = DB::table('cities as city')
                    ->join('regions as reg', 'city.region_id', '=', 'reg.id')
                    ->join('countries as co', 'city.country_id', '=', 'co.id')
                    ->select('city.id as city_id', 'city.name as city_name', 'reg.name as region_name', 'co.name as country_name', 'city.short_name')
                    ->where(['city.is_active' => 1, 'co.is_active' => 1, 'reg.is_active' => 1 ])->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $city);
            
        } catch(\Exception $e) {
            Log::debug("API GetAllCityList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetAllRegionList(Request $request) {
        try {

            $region = DB::table('regions as reg')
                    ->join('countries as co', 'reg.country_id', '=', 'co.id')
                    ->select('reg.id as region_id', 'reg.name as region_name', 'co.name as country_name', 'reg.short_name', 'reg.is_health_autority')
                    ->where(['co.is_active' => 1, 'reg.is_active' => 1 ])
                    ->ORDERBY('reg.id', 'DESC')->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $region);
            
        } catch(\Exception $e) {
            Log::debug("API GetAllRegionList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetTitleList(Request $request) {
        try {

            $Title = Title::SELECT('id', 'name', 'description')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $Title);
            
        } catch(\Exception $e) {
            Log::debug("API GetTitleList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewIndustry(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'name' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

                $Industry = Industry::create([
                    'name' => $request->name,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($Industry->id) {
                    return $this->sendResponse(1,200, 'Industry created successfully', 'industry_id', $Industry->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            
        } catch(\Exception $e) {
            Log::debug("API AddNewIndustry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateIndustry(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'industry_id' => 'required|integer|exists:industries,id,is_active,1',
                'name' => 'required|string|min:3|max:30'
                //'name' => 'required|string|unique:industries,name,industry_id,!id|min:3|max:30',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $Industry = Industry::find($request->industry_id);
            $Industry->name = $request->name;
            $Industry->updated_by = $request->user_id;
            $Industry->updated_at = date('Y-m-d H:i:s');
            $Industry->update();

            if($Industry->id) {
                return $this->sendResponse(1,200, 'Industry updated successfully', 'industry_id', $Industry->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API UpdateIndustry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteIndustry(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'industry_id' => 'required|integer|exists:industries,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $Industry = Industry::find($request->industry_id);
            $Industry->is_active = 0;
            $Industry->updated_by = $request->user_id;
            $Industry->updated_at = date('Y-m-d H:i:s');
            $Industry->update();

            if($Industry->id) {
                return $this->sendResponse(1,200, 'Industry deleted successfully', 'industry_id', $Industry->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteIndustry:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetIndustryList(Request $request) {
        try {

            $Title = Industry::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $Title);
            
        } catch(\Exception $e) {
            Log::debug("API GetIndustryList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetJobTitlList(Request $request) {
        try {

            $Title = JobTitle::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $Title);
            
        } catch(\Exception $e) {
            Log::debug("API GetJobTitlList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetNationalityList(Request $request) {
        try {

            $nationality = Nationality::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->ORDERBY('id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $nationality);
            
        } catch(\Exception $e) {
            Log::debug("API GetNationalityList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetDeptCategoryList(Request $request) {
        try {

            $Title = DeptCategory::SELECT('id', 'name', 'category_type', 'short_code')
                        ->WHERE(['is_active' => 1])->ORDERBY('name', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $Title);
            
        } catch(\Exception $e) {
            Log::debug("API GetDeptCategoryList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetDepartmentList(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'category_id' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            $category = DeptCategory::SELECT('id', 'name')
                        ->WHEREIN('id', $request->category_id)
                        ->ORDERBY('name', 'ASC')->get();
            $response = [];
            $result = array();
            if($category->count() != 0) {
                foreach($category as $cat) {
                    $cat_id = $cat->id;
                    $dept = Department::SELECT('id', 'name', 'short_code')
                            ->WHERE(['dept_catgory_id' => $cat_id, 'is_active' => 1])
                            ->ORDERBY('name', 'ASC')->get();
                    $res1[] = [
                        'category_id'=>$cat->id, 
                        'category_name'=>$cat->name, 
                        'department' =>$dept
                    ]; 
                }
                $result['list'] = $res1;
                $response = $result;
                return $this->sendResponse(1, 200, 'Success', 'data', $response);
            } else {
                return $this->sendResponse(0,200, 'Record not found');
            }
            
        } catch(\Exception $e) {
            Log::debug("API GetDepartmentList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetAllDepartmentList(Request $request) {
        try {

            $department = DB::table('departments as dept')
                    ->join('dept_categories as cat', 'dept.dept_catgory_id', '=', 'cat.id')
                    ->select('dept.id as department_id', 'dept.name as department_name', 'cat.name as category_name','cat.category_type', 'dept.short_code', 'dept.srvc_cat_code', 'dept.malaffi_code', 'dept.malaffi_specialty', 'dept.malaffi_dept_name', 'dept.appointment', 'dept.is_hl', 'dept.is_revenue', 'dept.is_sms', 'dept.suppress', 'dept.supact')
                    ->where(['dept.is_active' => 1, 'cat.is_active' => 1 ])
                    ->ORDERBY('dept.id', 'DESC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $department);
            
        } catch(\Exception $e) {
            Log::debug("API GetDepartmentList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetRoleList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'role_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            /*if($request->role_id == 1) {
                $Title = Role::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])->get();
            } else {
                $Title = Role::SELECT('id', 'name')
                        ->WHERE('id', '!=', 1)
                        ->WHERE(['is_active' => 1])->get();
            }*/

            $Title = Role::SELECT('id', 'name')
                    ->WHERE('id', '!=', 1)
                    ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $Title);
            
        } catch(\Exception $e) {
            Log::debug("API GetRoleList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetHospitalTypeList(Request $request) {
        try {

            $list = HospitalType::SELECT('id', 'name')
                    ->WHERE(['is_active' => 1])->get();
           
            return $this->sendResponse(1, 200, 'Success', 'data', $list);
            
        } catch(\Exception $e) {
            Log::debug("API GetHospitalTypeList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetGenderList(Request $request) {
        try {

            $list = Gender::SELECT('id', 'name', 'short_code')
                    ->WHERE(['is_active' => 1])->get();
           
            return $this->sendResponse(1, 200, 'Success', 'data', $list);
            
        } catch(\Exception $e) {
            Log::debug("API GetGenderList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Fetch single record
    public function GetSingleRegion($id) {
        try {
            $region = Region::SELECT('id', 'name', 'short_name', 'country_id')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($region->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $region);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $region->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleRegion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleNationality($id) {
        try {
            $Nationality = Nationality::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($Nationality->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $Nationality);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $Nationality->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleNationality :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleDepartment($id) {
        try {
            $Department = Department::SELECT('id', 'dept_catgory_id', 'name', 'short_code', 'srvc_cat_code', 'malaffi_code', 'malaffi_specialty', 'malaffi_dept_name', 'appointment', 'is_hl', 'is_revenue', 'is_sms', 'suppress', 'supact')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($Department->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $Department);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $Department->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleDepartment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetSingleCategory($id) {
        try {
            $city = DeptCategory::SELECT('id', 'name', 'short_code', 'category_type')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleCategory :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleCity($id) {
        try {
            $city = City::SELECT('id', 'name', 'short_name', 'region_id', 'country_id')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleCity :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleJobTitle($id) {
        try {
            $title = JobTitle::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($title->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $title);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $title->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleJobTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleTitle($id) {
        try {
            $title = Title::SELECT('id', 'name', 'description')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($title->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $title);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $title->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Add Functionality
    public function AddNewRegion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'country_id' => 'required|integer',
                'region_name' => 'required|string|min:3|max:30',
                'short_name' => 'required|min:1|max:30',
                'is_health_autority' => 'required|integer' //0 or 1
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){

                $region = Region::create([
                    'name' => $request->region_name,
                    'country_id' => $request->country_id,
                    'short_name' => $request->short_name,
                    'is_health_autority' => $request->is_health_autority,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($region->id) {
                    return $this->sendResponse(1,200, 'Region created successfully', 'region_id', $region->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewRegion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewCity(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'country_id' => 'required|integer',
                'region_id' => 'required|integer',
                'city_name' => 'required|string|min:3|max:30',
                'short_name' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $city = City::create([
                    'name' => $request->city_name,
                    'country_id' => $request->country_id,
                    'short_name' => $request->short_name,
                    'region_id' => $request->region_id,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($city->id) {
                    return $this->sendResponse(1,200, 'City created successfully', 'city_id', $city->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewCity :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewJobTitle(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'title_name' => 'required|string|min:3|max:30',
                'short_name' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $title = JobTitle::create([
                    'name' => $request->title_name,
                    'short_code' => $request->short_name,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($title->id) {
                    return $this->sendResponse(1,200, 'Job Title created successfully', 'job_title_id', $title->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewJobTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }    

    public function AddNewTitle(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'title_name' => 'required|string|min:2|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $title = Title::create([
                    'name' => $request->title_name,
                    'description' => $request->description,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($title->id) {
                    return $this->sendResponse(1,200, 'Title created successfully', 'title_id', $title->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewDepartment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'dept_catgory_id' => 'required|integer',
                'department_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30',
                'srvc_cat_code' => 'required|min:1|max:30',
                'malaffi_code' => 'required|min:1|max:30',
                'malaffi_specialty' => 'required|min:1|max:30',
                'malaffi_dept_name' => 'required|min:1|max:30',
                'appointment' => 'nullable|string|min:1|max:30', 
                'is_hl' => 'required|in:0,1',
                'is_revenue' => 'required|in:0,1',
                'is_sms' => 'required|in:0,1', 
                'suppress' => 'nullable|min:1|max:30', 
                'supact' => 'nullable|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $department = Department::create([
                    'name' => $request->department_name,
                    'dept_catgory_id' => $request->dept_catgory_id,
                    'short_code' => $request->short_code,
                    'srvc_cat_code' => $request->srvc_cat_code,
                    'malaffi_code' => $request->malaffi_code,
                    'malaffi_specialty' => $request->malaffi_specialty,
                    'malaffi_dept_name' => $request->malaffi_dept_name,
                    'appointment' => $request->appointment,
                    'is_hl' => $request->is_hl,
                    'is_revenue' => $request->is_revenue,
                    'suppress' => $request->suppress,
                    'supact' => $request->supact,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($department->id) {
                    return $this->sendResponse(1,200, 'Department created successfully', 'department_id', $department->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewDepartment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    } 

    public function AddNewCategory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'category_name' => 'required|string|min:3|max:30',
                'short_code' => 'nullable|string|min:3|max:30',
                'category_type' => 'required|string|in:Category,Service'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $category = DeptCategory::create([
                    'name' => $request->category_name,
                    'short_code' => $request->short_code,
                    'category_type' => $request->category_type,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($category->id) {
                    return $this->sendResponse(1,200, 'Category created successfully', 'category_id', $category->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewCategory :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewNationality(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'nationality_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $nationality = Nationality::create([
                    'name' => $request->nationality_name,
                    'short_code' => $request->short_code,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($nationality->id) {
                    return $this->sendResponse(1,200, 'Nationality created successfully', 'nationality_id', $nationality->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewNationality :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //Update Functionality
    public function UpdateTitle(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'title_id' => 'required|integer',
                'title_name' => 'required|string|min:2|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Title::where(['id' => $request->title_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $title = Title::find($request->title_id);
                    $title->name = $request->title_name;
                    $title->description = $request->description;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    $field_names = [
                        'name' => 'Title name updated', 
                        'description' => 'Title description updated'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $request->title_id, 'MasterLog', 'Title', $old_value, $title, $field_names);

                    return $this->sendResponse(1,200, 'Title updated successfully', 'title_id', $request->title_id);
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'title_id', $request->title_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
            
        } catch(\Exception $e) {
            Log::debug('API UpdateTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateDepartment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'department_id' => 'required|integer',
                'dept_catgory_id' => 'required|integer',
                'department_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30',
                'srvc_cat_code' => 'required|min:1|max:30',
                'malaffi_code' => 'required|min:1|max:30',
                'malaffi_specialty' => 'required|min:1|max:30',
                'malaffi_dept_name' => 'required|min:1|max:30',
                'appointment' => 'nullable|string|min:1|max:30', 
                'is_hl' => 'required|in:0,1',
                'is_revenue' => 'required|in:0,1',
                'is_sms' => 'required|in:0,1', 
                'suppress' => 'nullable|min:1|max:30', 
                'supact' => 'nullable|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
            $old_value = Department::where(['id' => $request->department_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $department = Department::find($request->department_id);
                    $department->name = $request->department_name;
                    $department->dept_catgory_id = $request->dept_catgory_id;
                    $department->short_code = $request->short_code;
                    $department->srvc_cat_code = $request->srvc_cat_code; 
                    $department->malaffi_code = $request->malaffi_code;
                    $department->malaffi_specialty = $request->malaffi_specialty;
                    $department->malaffi_dept_name = $request->malaffi_dept_name;
                    $department->appointment = $request->appointment;
                    $department->is_hl = $request->is_hl;
                    $department->is_revenue = $request->is_revenue;
                    $department->is_sms = $request->is_sms;
                    $department->suppress = $request->suppress;
                    $department->supact = $request->supact;
                    $department->updated_by = $request->user_id;
                    $department->updated_at = date('Y-m-d H:i:s');
                    $department->update();
                    

                    if($request->department_id) {
                        $field_names = [
                            'name' => 'Department name updated', 
                            'dept_catgory_id' => 'Department category changed',
                            'short_code' => 'Short code updated', 
                            'srvc_cat_code' => 'Service catgory code updated',
                            'malaffi_code' => 'Malaffi code updated', 
                            'malaffi_specialty' => 'Malaffi specialty updated',
                            'malaffi_dept_name' => 'Malaffi department name changed',
                            'appointment' => 'Appointment updated',
                            'is_hl' => 'HL7 updated',
                            'is_revenue' => 'Revenue updated',
                            'is_sms' => 'SMS updated',
                            'suppress' => 'Suppress updated',
                            'supact' => 'supact updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $department->id, 'MasterLog', 'Department', $old_value, $department, $field_names);
    
                        return $this->sendResponse(1,200, 'Department updated successfully', 'department_id', $request->department_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'department_id', $request->department_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateDepartment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateCategory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "category_id" => 'required|integer',
                'category_name' => 'required|string|min:3|max:30',
                'short_code' => 'nullable|string|min:3|max:30',
                'category_type' => 'required|string|in:Category,Service'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = DeptCategory::where(['id' => $request->category_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $category = DeptCategory::find($request->category_id);
                    $category->name = $request->category_name;
                    $category->short_code = $request->short_code;
                    $category->category_type = $request->category_type;
                    $category->updated_by = $request->user_id;
                    $category->updated_at = date('Y-m-d H:i:s');
                    $category->update();

                    if($category->id) {
                        $field_names = [
                            'name' => 'Department Category name updated',
                            'short_code' => 'Short code  updated',
                            'category_type' => 'Category type changed'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $category->id, 'MasterLog', 'DeptCategory', $old_value, $category, $field_names);
                        return $this->sendResponse(1,200, 'Category updated successfully', 'category_id', $request->category_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'category_id', $request->category_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateCategory :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateNationality(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "nationality_id" => 'required|integer',
                'nationality_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Nationality::where(['id' => $request->nationality_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $nationality = Nationality::find($request->nationality_id);
                    $nationality->name = $request->nationality_name;
                    $nationality->short_code = $request->short_code;
                    $nationality->updated_by = $request->user_id;
                    $nationality->updated_at = date('Y-m-d H:i:s');
                    $nationality->update();
                    

                    if($request->nationality_id) {
                        $field_names = [
                            'name' => 'Nationality name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $nationality->id, 'MasterLog', 'Nationality', $old_value, $nationality, $field_names);
                        return $this->sendResponse(1,200, 'Nationality updated successfully', 'nationality_id', $request->nationality_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'nationality_id', $request->nationality_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateNationality :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateJobTitle(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'job_title_id' => 'required|integer',
                'title_name' => 'required|string|min:3|max:30',
                'short_name' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = JobTitle::where(['id' => $request->job_title_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $title = JobTitle::find($request->job_title_id);
                    $title->name = $request->title_name;
                    $title->short_code = $request->short_name;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    $field_names = [
                        'name' => 'JobTitle name updated',
                        'short_code' => 'Title Short code updated'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $request->job_title_id, 'MasterLog', 'JobTitle', $old_value, $title, $field_names);
                    return $this->sendResponse(1,200, 'Job Title updated successfully', 'job_title_id', $request->job_title_id);
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'job_title_id', $request->job_title_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
            

        } catch(\Exception $e) {
            Log::debug('API UpdateJobTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateCity(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'city_id'=> 'required|integer',
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'country_id' => 'required|integer',
                'region_id' => 'required|integer',
                'city_name' => 'required|string|min:3|max:30',
                'short_name' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = City::where(['id' => $request->city_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $city = City::find($request->city_id);
                    $city->name = $request->city_name;
                    $city->country_id = $request->country_id;
                    $city->short_name = $request->short_name;
                    $city->region_id = $request->region_id;
                    $city->updated_by = $request->user_id;
                    $city->updated_at = date('Y-m-d H:i:s');
                    $city->update();

                    $field_names = [
                        'name' => 'City name updated',
                        'short_name' => 'Short name updated',
                        'country_id' => 'Country changed',
                        'region_id' => 'Region changed'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $request->city_id, 'MasterLog', 'City', $old_value, $city, $field_names);
                    return $this->sendResponse(1,200, 'City updated successfully', 'city_id', $request->city_id);
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'city_id', $request->city_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateCity :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateRegion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'region_id' => 'required|integer',
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'country_id' => 'required|integer',
                'region_name' => 'required|string|min:3|max:30',
                'short_name' => 'required|min:1|max:30',
                'is_health_autority' => 'required|integer' //0 or 1
            ]);


            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Region::where(['id' => $request->region_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $region = Region::find($request->region_id);
                    $region->name = $request->region_name;
                    $region->country_id = $request->country_id;
                    $region->short_name = $request->short_name;
                    $region->is_health_autority = $request->is_health_autority;
                    $region->updated_by = $request->user_id;
                    $region->updated_at = date('Y-m-d H:i:s');
                    $region->update();

                    $field_names = [
                        'name' => 'Region name updated',
                        'short_name' => 'Short name updated',
                        'country_id' => 'Country changed'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $request->region_id, 'MasterLog', 'Region', $old_value, $region, $field_names);

                    return $this->sendResponse(1,200, 'Region updated successfully', 'region_id', $request->region_id);
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'region_id', $request->region_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateRegion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Delete functionality
    public function DeleteCity(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'city_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = City::where(['id' => $request->city_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $city = City::find($request->city_id);
                    $city->is_active = 0;
                    $city->updated_by = $request->user_id;
                    $city->updated_at = date('Y-m-d H:i:s');
                    $city->update();

                    if($request->city_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $request->city_id, 'MasterLog', 'City', 'Description');
                        return $this->sendResponse(1,200, 'City deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'city_id', $request->city_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteCity :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteRegion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'region_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Region::where(['id' => $request->region_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $region = Region::find($request->region_id);
                    $region->is_active = 0;
                    $region->updated_by = $request->user_id;
                    $region->updated_at = date('Y-m-d H:i:s');
                    $region->update();

                    if($request->region_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $region->id, 'MasterLog', 'Region', 'Description');
                        return $this->sendResponse(1,200, 'Region deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'region_id', $request->region_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteRegion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteDepartment(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'department_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Department::where(['id' => $request->department_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $department = Department::find($request->department_id);
                    $department->is_active = 0;
                    $department->updated_by = $request->user_id;
                    $department->updated_at = date('Y-m-d H:i:s');
                    $department->update();

                    if($request->department_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $department->id, 'MasterLog', 'Department', 'Description');
                        return $this->sendResponse(1,200, 'Department deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'department_id', $request->department_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteDepartment :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteTitle(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'title_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Title::where(['id' => $request->title_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $title = Title::find($request->title_id);
                    $title->is_active = 0;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    if($request->title_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $title->id, 'MasterLog', 'Title', 'Description');
                        return $this->sendResponse(1,200, 'Title deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'title_id', $request->title_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteCategory(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'category_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = DeptCategory::where(['id' => $request->category_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $title = DeptCategory::find($request->category_id);
                    $title->is_active = 0;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    if($request->category_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $title->id, 'MasterLog', 'DeptCategory', 'Description');
                        return $this->sendResponse(1,200, 'Category deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'category_id', $request->category_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteCategory :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteNationality(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'nationality_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Nationality::where(['id' => $request->nationality_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $title = Nationality::find($request->nationality_id);
                    $title->is_active = 0;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    if($request->nationality_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $title->id, 'MasterLog', 'Nationality', 'Description');
                        return $this->sendResponse(1,200, 'Nationality deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', '', $request->nationality_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteNationality :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function DeleteJobTitle(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'job_title_id' => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = JobTitle::where(['id' => $request->job_title_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $title = JobTitle::find($request->job_title_id);
                    $title->is_active = 0;
                    $title->updated_by = $request->user_id;
                    $title->updated_at = date('Y-m-d H:i:s');
                    $title->update();

                    if($request->job_title_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $title->id, 'MasterLog', 'JobTitle', 'Description');
                        return $this->sendResponse(1,200, 'Job Title deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'job_title_id', $request->job_title_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteJobTitle :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    

    public function GetReligionList(Request $request) {
        try {

            $country = Religion::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetReligionList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetEducationList(Request $request) {
        try {

            $country = Education::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetEducationList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetEthnicList(Request $request) {
        try {

            $country = Ethnic::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetEthnicList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetIncomeRangeList(Request $request) {
        try {

            $country = IncomeRange::SELECT('id', 'income_range')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetIncomeRangeList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetLanguageList(Request $request) {
        try {

            $country = Language::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetLanguageList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetOccupationList(Request $request) {
        try {

            $country = Occupation::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetOccupationList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetPatientClassList(Request $request) {
        try {

            $country = PatientClass::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetPatientClassList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetRelationshipList(Request $request) {
        try {

            $country = Relationship::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetRelationshipList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetMaritalStatusList(Request $request) {
        try {

            $country = MaritalStatus::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetMaritalStatusList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetSingleReligion($id) {
        try {
            $city = Religion::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleReligion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleEducation($id) {
        try {
            $city = Education::SELECT('id', 'name')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleEducation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleEthnic($id) {
        try {
            $city = Ethnic::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleEthnic :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleIncomeRange($id) {
        try {
            $city = IncomeRange::SELECT('id', 'income_range')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleIncomeRange :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetSingleLanguage($id) {
        try {
            $city = Language::SELECT('id', 'name','short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleLanguage :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleOccupation($id) {
        try {
            $city = Occupation::SELECT('id', 'name')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleOccupation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSinglePatientClass($id) {
        try {
            $city = PatientClass::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSinglePatientClass :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleRelationship($id) {
        try {
            $city = Relationship::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleRelationship :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleMaritalStatus($id) {
        try {
            $city = MaritalStatus::SELECT('id', 'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($city->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $city);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $city->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleMaritalStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddNewReligion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'religion_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Religion::where(['name' => $request->religion_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $religion = Religion::create([
                        'name' => $request->religion_name,
                        'short_code' => $request->short_code,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($religion->id) {
                        return $this->sendResponse(1,200, 'Religion created successfully', 'religion_id', $religion->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewReligion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewEducation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'education_name' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Education::where(['name' => $request->education_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $education = Education::create([
                        'name' => $request->education_name,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($education->id) {
                        return $this->sendResponse(1,200, 'Education created successfully', 'education_id', $education->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewEducation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewEthnic(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'ethnic_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Ethnic::where(['name' => $request->ethnic_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $ethnic = Ethnic::create([
                        'name' => $request->ethnic_name,
                        'short_code' => $request->short_code,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($ethnic->id) {
                        return $this->sendResponse(1,200, 'Ethnic created successfully', 'ethnic_id', $ethnic->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewEthnic :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewIncomeRange(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'income_range' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = IncomeRange::where(['income_range' => $request->income_range, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $income = IncomeRange::create([
                        'income_range' => $request->income_range,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($income->id) {
                        return $this->sendResponse(1,200, 'EthIncomeRangenic created successfully', 'income_id', $income->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewIncomeRange :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function AddNewLanguage(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'language_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Language::where(['name' => $request->language_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $language = Language::create([
                        'name' => $request->language_name,
                        'short_code' => $request->short_code,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($language->id) {
                        return $this->sendResponse(1,200, 'Language created successfully', 'language_id', $language->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewLanguage :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewOccupation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'occupation_name' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Occupation::where(['name' => $request->occupation_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $Occupation = Occupation::create([
                        'name' => $request->occupation_name,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($Occupation->id) {
                        return $this->sendResponse(1,200, 'Occupation created successfully', 'occupation_id', $Occupation->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewOccupation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewPatientClass(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'class_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = PatientClass::where(['name' => $request->class_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $class = PatientClass::create([
                        'name' => $request->class_name,
                        'short_code' => $request->short_code,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($class->id) {
                        return $this->sendResponse(1,200, 'PatientClass created successfully', 'class_id', $class->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewPatientClass :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewRelationship(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'relationship_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Relationship::where(['name' => $request->relationship_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $relationship = Relationship::create([
                        'name' => $request->relationship_name,
                        'short_code' => $request->short_code,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($relationship->id) {
                        return $this->sendResponse(1,200, 'Relationship created successfully', 'relationship_id', $relationship->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewRelationship :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewMaritalStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'status_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = MaritalStatus::where(['name' => $request->status_name, 'is_active'=>1])->get();
                if(count($rec_count) == 0) {
                    $status = MaritalStatus::create([
                        'name' => $request->status_name,
                        'short_code' => $request->short_code,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);

                    if($status->id) {
                        return $this->sendResponse(1,200, 'MaritalStatus created successfully', 'status_id', $status->id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate record not allowed.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewMaritalStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateReligion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "religion_id" => 'required|integer',
                'religion_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Religion::where(['id' => $request->religion_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $religion = Religion::find($request->religion_id);
                    $religion->name = $request->religion_name;
                    $religion->short_code = $request->short_code;
                    $religion->updated_by = $request->user_id;
                    $religion->updated_at = date('Y-m-d H:i:s');
                    $religion->update();
                    
                    if($request->religion_id) {
                        $field_names = [
                            'name' => 'Religion name updated', 
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $religion->id, 'MasterLog', 'Religion', $old_value, $religion, $field_names);
                        return $this->sendResponse(1,200, 'Religion updated successfully', 'religion_id', $request->religion_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateReligion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateEducation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "education_id" => 'required|integer',
                'education_name' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Education::where(['id' => $request->education_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $Education = Education::find($request->education_id);
                    $Education->name = $request->education_name;
                    $Education->updated_by = $request->user_id;
                    $Education->updated_at = date('Y-m-d H:i:s');
                    $Education->update();
                    
                    if($request->education_id) {
                        $field_names = [
                            'name' => 'Education name updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $Education->id, 'MasterLog', 'Education', $old_value, $Education, $field_names);
                        return $this->sendResponse(1,200, 'Education updated successfully', 'education_id', $request->education_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateEducation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateEthnic(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "ethnic_id" => 'required|integer',
                'ethnic_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Ethnic::where(['id' => $request->ethnic_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $religion = Ethnic::find($request->ethnic_id);
                    $religion->name = $request->ethnic_name;
                    $religion->short_code = $request->short_code;
                    $religion->updated_by = $request->user_id;
                    $religion->updated_at = date('Y-m-d H:i:s');
                    $religion->update();
                    
                    if($request->ethnic_id) {
                        $field_names = [
                            'name' => 'Ethnic name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $religion->id, 'MasterLog', 'Ethnic', $old_value, $religion, $field_names);
                        return $this->sendResponse(1,200, 'Ethnic updated successfully', 'ethnic_id', $request->ethnic_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateEthnic :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateIncomeRange(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "income_id" => 'required|integer',
                'income_range' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = IncomeRange::where(['id' => $request->income_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $religion = IncomeRange::find($request->income_id);
                    $religion->income_range = $request->income_range;
                    $religion->updated_by = $request->user_id;
                    $religion->updated_at = date('Y-m-d H:i:s');
                    $religion->update();
                    
                    if($request->income_id) {
                        $field_names = [
                            'income_range' => 'Income Range updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $religion->id, 'MasterLog', 'IncomeRange', $old_value, $religion, $field_names);
                        return $this->sendResponse(1,200, 'IncomeRange updated successfully', 'income_id', $request->income_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateIncomeRange :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateLanguage(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "language_id" => 'required|integer',
                'language_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Language::where(['id' => $request->language_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $language = Language::find($request->language_id);
                    $language->name = $request->language_name;
                    $language->short_code = $request->short_code;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->language_id) {
                        $field_names = [
                            'name' => 'Language name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $language->id, 'MasterLog', 'Language', $old_value, $language, $field_names);
                        return $this->sendResponse(1,200, 'Language updated successfully', 'language_id', $request->language_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateLanguage :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateOccupation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "occupation_id" => 'required|integer',
                'occupation_name' => 'required|string|min:3|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Occupation::where(['id' => $request->occupation_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $language = Occupation::find($request->occupation_id);
                    $language->name = $request->occupation_name;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->occupation_id) {
                        $field_names = [
                            'name' => 'Occupation name updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $language->id, 'MasterLog', 'Occupation', $old_value, $language, $field_names);
                        return $this->sendResponse(1,200, 'Occupation updated successfully', 'occupation_id', $request->occupation_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateOccupation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdatePatientClass(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "class_id" => 'required|integer',
                'class_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = PatientClass::where(['id' => $request->class_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $language = PatientClass::find($request->class_id);
                    $language->name = $request->class_name;
                    $language->short_code = $request->short_code;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->class_id) {
                        $field_names = [
                            'name' => 'Occupation name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $language->id, 'MasterLog', 'PatientClass', $old_value, $language, $field_names);
                        return $this->sendResponse(1,200, 'PatientClass updated successfully', 'class_id', $request->class_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdatePatientClass :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function UpdateRelationship(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "relationship_id" => 'required|integer',
                'relationship_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Relationship::where(['id' => $request->relationship_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $language = Relationship::find($request->relationship_id);
                    $language->name = $request->relationship_name;
                    $language->short_code = $request->short_code;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->relationship_id) {
                        $field_names = [
                            'name' => 'Relationship name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $language->id, 'MasterLog', 'Relationship', $old_value, $language, $field_names);
                        return $this->sendResponse(1,200, 'Relationship updated successfully', 'relationship_id', $request->relationship_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateRelationship :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateMaritalStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "status_id" => 'required|integer',
                'status_name' => 'required|string|min:3|max:30',
                'short_code' => 'required|string|min:1|max:30'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = MaritalStatus::where(['id' => $request->status_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $language = MaritalStatus::find($request->status_id);
                    $language->name = $request->status_name;
                    $language->short_code = $request->short_code;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->status_id) {
                        $field_names = [
                            'name' => 'MaritalStatus name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $language->id, 'MasterLog', 'MaritalStatus', $old_value, $language, $field_names);
                        return $this->sendResponse(1,200, 'MaritalStatus updated successfully', 'status_id', $request->status_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateMaritalStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function DeleteReligion(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "religion_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Religion::where(['id' => $request->religion_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $religion = Religion::find($request->religion_id);
                    $religion->is_active = 0;
                    $religion->updated_by = $request->user_id;
                    $religion->updated_at = date('Y-m-d H:i:s');
                    $religion->update();
                    
                    if($request->religion_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $religion->id, 'MasterLog', 'Religion', 'Description');
                        return $this->sendResponse(1,200, 'Religion deleted successfully', 'religion_id', $request->religion_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteReligion :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteEducation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "education_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Education::where(['id' => $request->education_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $Education = Education::find($request->education_id);
                    $Education->is_active = 0;
                    $Education->updated_by = $request->user_id;
                    $Education->updated_at = date('Y-m-d H:i:s');
                    $Education->update();
                    
                    if($request->education_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $Education->id, 'MasterLog', 'Education', 'Description');
                        return $this->sendResponse(1,200, 'Education deleted successfully', 'education_id', $request->education_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteEducation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteEthnic(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "ethnic_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Ethnic::where(['id' => $request->ethnic_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $religion = Ethnic::find($request->ethnic_id);
                    $religion->is_active = 0;
                    $religion->updated_by = $request->user_id;
                    $religion->updated_at = date('Y-m-d H:i:s');
                    $religion->update();
                    
                    if($request->ethnic_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $religion->id, 'MasterLog', 'Ethnic', 'Description');
                        return $this->sendResponse(1,200, 'Ethnic deleted successfully', 'ethnic_id', $request->ethnic_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteEthnic :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteIncomeRange(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "income_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = IncomeRange::where(['id' => $request->income_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $religion = IncomeRange::find($request->income_id);
                    $religion->is_active = 0;
                    $religion->updated_by = $request->user_id;
                    $religion->updated_at = date('Y-m-d H:i:s');
                    $religion->update();
                    
                    if($request->income_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $religion->id, 'MasterLog', 'IncomeRange', 'Description');
                        return $this->sendResponse(1,200, 'IncomeRange deleted successfully', 'income_id', $request->income_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateIncomeRange :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteLanguage(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "language_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Language::where(['id' => $request->language_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $language = Language::find($request->language_id);
                    $language->is_active = 0;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->language_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $language->id, 'MasterLog', 'Language', 'Description');
                        return $this->sendResponse(1,200, 'Language deleted successfully', 'language_id', $request->language_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteLanguage :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteOccupation(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "occupation_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Occupation::where(['id' => $request->occupation_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $language = Occupation::find($request->occupation_id);
                    $language->is_active = 0;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->occupation_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $language->id, 'MasterLog', 'Occupation', 'Description');
                        return $this->sendResponse(1,200, 'Occupation deleted successfully', 'occupation_id', $request->occupation_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteOccupation :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeletePatientClass(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "class_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = PatientClass::where(['id' => $request->class_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $language = PatientClass::find($request->class_id);
                    $language->is_active = 0;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->class_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $language->id, 'MasterLog', 'PatientClass', 'Description');
                        return $this->sendResponse(1,200, 'PatientClass deleted successfully', 'class_id', $request->class_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeletePatientClass :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function DeleteRelationship(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "relationship_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = Relationship::where(['id' => $request->relationship_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $language = Relationship::find($request->relationship_id);
                    $language->is_active = 0;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->relationship_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $language->id, 'MasterLog', 'Relationship', 'Description');
                        return $this->sendResponse(1,200, 'Relationship deleted successfully', 'relationship_id', $request->relationship_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteRelationship :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteMaritalStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "status_id" => 'required|integer'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = MaritalStatus::where(['id' => $request->status_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $language = MaritalStatus::find($request->status_id);
                    $language->is_active = 0;
                    $language->updated_by = $request->user_id;
                    $language->updated_at = date('Y-m-d H:i:s');
                    $language->update();
                    
                    if($request->status_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $language->id, 'MasterLog', 'MaritalStatus', 'Description');
                        return $this->sendResponse(1,200, 'MaritalStatus deleted successfully', 'status_id', $request->status_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteMaritalStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetBloodGroupList(Request $request) {
        try {

            $country = BloodGroup::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetBloodGroupList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetPaymentModeList(Request $request) {
        try {

            $country = PaymentMode::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $country);
            
        } catch(\Exception $e) {
            Log::debug("API GetPaymentModeList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetReferralChannelList(Request $request) {
        try {

            $list = ReferralChannel::SELECT('id', 'name')
                    ->WHERE(['is_active' => 1])->get();
           
            return $this->sendResponse(1, 200, 'Success', 'data', $list);
            
        } catch(\Exception $e) {
            Log::debug("API GetReferralChannelList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetReferralSourceList(Request $request) {
        try {

            $list = ReferralSource::SELECT('id', 'name')
                    ->WHERE(['is_active' => 1])->get();
           
            return $this->sendResponse(1, 200, 'Success', 'data', $list);
            
        } catch(\Exception $e) {
            Log::debug("API GetReferralSourceList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetCompanyTypes(Request $request) {
        try {

            $list = CompanyType::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $list);
            
        } catch(\Exception $e) {
            Log::debug("API GetCompanyTypes:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetChargeTypes(Request $request) {
        try {

            $list = ChargeType::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $list);
            
        } catch(\Exception $e) {
            Log::debug("API GetChargeTypes:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewInsCompany(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'form_type'     => 'required|integer|in:1,2',
                'mediator_id'   => 'required_if:form_type,==,2|nullable|integer|exists:insurance_company_details,id,is_active,1',
                'name'          => 'required|string|min:3|max:50',
                'short_code'    => 'required|string|min:3|max:30',
                'payer_ids'     => 'nullable|string|min:0|max:30',
                'receiver_ids'  => 'nullable|string|min:0|max:30',
                'provider_code' => 'nullable|string|min:0|max:30',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date',
                'min_limit'      => 'nullable|integer',
                'max_limit'      => 'nullable|integer',
                'claim_no'       => 'required|integer|in:0,1',
                'outsource_lab' => 'required|integer|in:0,1',
                'e_auth'        => 'required|integer|in:0,1',
                'no_lab_xml'    => 'required|integer|in:0,1',
                'eligiblity'    => 'required|integer|in:0,1',
                'benefit_package'    => 'required|integer|in:0,1',
                'pharmacy_token'    => 'required|integer|in:0,1',
                'activity_clinician'=> 'required|integer|in:0,1',
                'company_type_id'   => 'required|integer|exists:company_types,id,is_active,1',
                'charge_type_id'    => 'required|integer|exists:charge_types,id,is_active,1',
                'contact_person'    => 'required|string|min:3|max:30',
                'designation'       => 'nullable|string|min:3|max:30',
                'department'        => 'required|string|min:3|max:30',
                'contact_phone'     => 'nullable|string|min:3|max:20',
                'contact_mobile'    => 'nullable|string|min:3|max:20',
                'contact_fax'       => 'nullable|string|min:3|max:30',
                'contact_email'     => 'nullable|email|min:3|max:50',
                'address'           => 'nullable|string|min:3|max:200',
                'country_id'        => 'nullable|integer|exists:countries,id',
                'region_id'         => 'required|integer|exists:regions,id',
                'city_id'           => 'nullable|integer|exists:cities,id',
                'pincode'           => 'nullable|string|min:3|max:10',
                'billing_phone'     => 'nullable|string|min:3|max:20',
                'billing_fax'       => 'nullable|string|min:3|max:30',
                'website'           => 'nullable|string|min:3|max:50',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('insurance-list', 'is_add') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $start_date = null;
            $end_date = null;
            if($request->start_date != ''){
                $start_date = date("Y-m-d", strtotime($request->start_date)); 
            }
            if($request->end_date != ''){
                $end_date = date("Y-m-d", strtotime($request->end_date)); 
            }

            if($request->form_type  == 1 && $request->mediator_id != ''){
                return $this->sendResponse(0,200, 'For main company mediator name is not required');
            }
            if($request->form_type  == 2 && $request->mediator_id != ''){
                $insurance = InsuranceCompanyDetail::SELECT('id', 'form_type')
                    ->where(['id' => $request->mediator_id, 'form_type'=> 1, 'is_active' => 1])->get();

                if($insurance->count() == 0) {
                    return $this->sendResponse(0,200, 'Selected mediator name is wrong');
                }
            }

            $IsExistName = InsuranceCompanyDetail::where(['name' => $request->name, 'is_active' => 1])->get();
            if($IsExistName->count() != 0) {
                return $this->sendResponse(0,200, 'Insurance company name is already exist.');
            }
            
            $IsExistShortName = InsuranceCompanyDetail::where(['short_code' => $request->short_code, 'is_active' => 1])->get();
            if($IsExistShortName->count() != 0) {
                return $this->sendResponse(0,200, 'Insurance company short name is already exist.');
            }

            if($request->payer_ids != ''){
                $IsExistPayerId = InsuranceCompanyDetail::where(['payer_ids' => $request->payer_ids, 'is_active' => 1])->get();
                if($IsExistPayerId->count() != 0) {
                    return $this->sendResponse(0,200, 'Insurance company Payer ID is already exist.');
                }
            }

            

            $company = InsuranceCompanyDetail::create([
                'form_type'         => $request->form_type,
                'name'              => $request->name,
                'insurance_company_detail_id' => $request->mediator_id,
                'short_code'        => $request->short_code,
                'receiver_ids'      => $request->receiver_ids,
                'payer_ids'         => $request->payer_ids,
                'provider_code'     => $request->provider_code,
                'company_type_id'   => $request->company_type_id,
                'charge_type_id'    => $request->charge_type_id,
                'start_date'        => $start_date,
                'end_date'          => $end_date,
                'min_limit'         => $request->min_limit,
                'max_limit'         => $request->max_limit,
                'claim_no'          => $request->claim_no,
                'outsource_lab'     => $request->outsource_lab,
                'e_auth'            => $request->e_auth,
                'activity_clinician'=> $request->activity_clinician,
                'eligiblity'        => $request->eligiblity,
                'benefit_package'   => $request->benefit_package,
                'pharmacy_token'    => $request->pharmacy_token,
                'no_lab_xml'        => $request->no_lab_xml,
                'contact_person'    => $request->contact_person,
                'designation'       => $request->designation,
                'department'        => $request->department,
                'contact_phone'     => $request->contact_phone,
                'contact_mobile'    => $request->contact_mobile,
                'contact_fax'       => $request->contact_fax,
                'contact_email'     => $request->contact_email,
                'address'           => $request->address,
                'country_id'        => $request->country_id,
                'region_id'         => $request->region_id,
                'city_id'           => $request->city_id,
                'pincode'           => $request->pincode,
                'billing_phone'     => $request->billing_phone,
                'billing_fax'       => $request->billing_fax,
                'website'    => $request->website,
                'created_by' => $request->user_id,
                'updated_by' => $request->user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            if($company->id) {
                return $this->sendResponse(1,200, 'Company details added scessfully', 'company_id', $company->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }


        } catch(\Exception $e) {
            Log::debug('API AddNewInsCompany :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateInsCompany(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'company_id'    => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'form_type'     => 'required|integer|in:1,2',
                'mediator_id'   => 'required_if:form_type,==,2|nullable|integer|exists:insurance_company_details,id,is_active,1',
                'name'          => 'required|string|min:3|max:50',
                'short_code'    => 'required|string|min:3|max:20',
                'payer_ids'     => 'nullable|string|min:0|max:30',
                'receiver_ids'  => 'nullable|string|min:0|max:30',
                'provider_code' => 'nullable|string|min:0|max:30',
                'start_date'    => 'nullable|date',
                'end_date'      => 'nullable|date',
                'min_limit'     => 'nullable|integer',
                'max_limit'     => 'nullable|integer',
                'claim_no'       => 'required|integer|in:0,1',
                'outsource_lab' => 'required|integer|in:0,1',
                'e_auth'        => 'required|integer|in:0,1',
                'no_lab_xml'    => 'required|integer|in:0,1',
                'eligiblity'    => 'required|integer|in:0,1',
                'pharmacy_token'    => 'required|integer|in:0,1',
                'activity_clinician'=> 'required|integer|in:0,1',
                'is_status'        => 'required|integer|in:0,1',
                'benefit_package'    => 'required|integer|in:0,1',
                'company_type_id'   => 'required|integer|exists:company_types,id,is_active,1',
                'charge_type_id'    => 'required|integer|exists:charge_types,id,is_active,1',
                'contact_person'    => 'required|string|min:3|max:30',
                'designation'       => 'nullable|string|min:3|max:30',
                'department'        => 'required|string|min:3|max:30',
                'contact_phone'     => 'nullable|string|min:3|max:20',
                'contact_mobile'    => 'nullable|string|min:3|max:20',
                'contact_fax'       => 'nullable|string|min:3|max:30',
                'contact_email'     => 'nullable|email|min:3|max:50',
                'address'           => 'nullable|string|min:3|max:200',
                'country_id'        => 'nullable|integer|exists:countries,id',
                'region_id'         => 'required|integer|exists:regions,id',
                'city_id'           => 'nullable|integer|exists:cities,id',
                'pincode'           => 'nullable|string|min:3|max:10',
                'billing_phone'     => 'nullable|string|min:3|max:20',
                'billing_fax'       => 'nullable|string|min:3|max:30',
                'website'           => 'nullable|string|min:3|max:50',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            // if($this->VerifyPageAccess('insurance-list', 'is_update') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $start_date = null;
            $end_date = null;
            if($request->start_date != ''){
                $start_date = date("Y-m-d", strtotime($request->start_date)); 
            }
            if($request->end_date != ''){
                $end_date = date("Y-m-d", strtotime($request->end_date)); 
            }

            if($request->form_type  == 1 && $request->mediator_id != ''){
                return $this->sendResponse(0,200, 'For main company mediator name is not required');
            }
            if($request->form_type  == 2 && $request->mediator_id != ''){
                $insurance = InsuranceCompanyDetail::SELECT('id', 'form_type')
                    ->where(['id' => $request->mediator_id, 'form_type'=> 1, 'is_active' => 1])->get();

                if($insurance->count() == 0) {
                    return $this->sendResponse(0,200, 'Sub company mediator name is not required');
                }
            }

            $insurance = InsuranceCompanyDetail::SELECT('id', 'form_type')
                    ->where(['id' => $request->company_id, 'is_active' => 1])->get();

            if($insurance[0]->form_type != $request->form_type) {
                return $this->sendResponse(0,200, 'Company mode is not editable.');
            } 

            $IsExistName = InsuranceCompanyDetail::where(['name' => $request->name, 'is_active' => 1])
                            ->where('id', '!=', $request->company_id)->get();
            if($IsExistName->count() != 0) {
                return $this->sendResponse(0,200, 'Insurance company name is already exist.');
            }
            
            $IsExistShortName = InsuranceCompanyDetail::where(['short_code' => $request->short_code, 'is_active' => 1])
                    ->where('id', '!=', $request->company_id)->get();
            if($IsExistShortName->count() != 0) {
                return $this->sendResponse(0,200, 'Insurance company short name is already exist.');
            }

            if($request->payer_ids != ''){
                $IsExistPayerId = InsuranceCompanyDetail::where(['payer_ids' => $request->payer_ids, 'is_active' => 1])
                                ->where('id', '!=', $request->company_id)->get();
                if($IsExistPayerId->count() != 0) {
                    return $this->sendResponse(0,200, 'Insurance company Payer ID is already exist.');
                }
            }

            $old_value = InsuranceCompanyDetail::where(['id' => $request->company_id, 'is_active' => 1])->get();
            $company = InsuranceCompanyDetail::find($request->company_id);
            $company->insurance_company_detail_id = $request->mediator_id;
            $company->name              = $request->name;
            $company->short_code        = $request->short_code;
            $company->receiver_ids      = $request->receiver_ids;
            $company->payer_ids         = $request->payer_ids;
            $company->provider_code     = $request->provider_code;
            $company->company_type_id   = $request->company_type_id;
            $company->charge_type_id    = $request->charge_type_id;
            $company->start_date        = $start_date;
            $company->end_date          = $end_date;
            $company->min_limit         = $request->min_limit;
            $company->max_limit         = $request->max_limit;
            $company->claim_no          = $request->claim_no;
            $company->outsource_lab     = $request->outsource_lab;
            $company->e_auth            = $request->e_auth;
            $company->activity_clinician= $request->activity_clinician;
            $company->benefit_package   = $request->benefit_package;
            $company->eligiblity        = $request->eligiblity;
            $company->pharmacy_token    = $request->pharmacy_token;
            $company->no_lab_xml        = $request->no_lab_xml;
            $company->contact_person    = $request->contact_person;
            $company->designation       = $request->designation;
            $company->department        = $request->department;
            $company->contact_phone     = $request->contact_phone;
            $company->contact_mobile    = $request->contact_mobile;
            $company->contact_fax       = $request->contact_fax;
            $company->contact_email     = $request->contact_email;
            $company->address           = $request->address;
            $company->country_id        = $request->country_id;
            $company->region_id         = $request->region_id;
            $company->city_id           = $request->city_id;
            $company->pincode           = $request->pincode;
            $company->billing_phone     = $request->billing_phone;
            $company->billing_fax       = $request->billing_fax;
            $company->is_status         = $request->is_status;
            $company->website    = $request->website;
            $company->updated_by = $request->user_id;
            $company->updated_at = date('Y-m-d H:i:s');
            $company->update();

            if($company->id) {
                $field_names = [
                    'name' => 'Comapny name updated', 
                    'short_code' => 'Short code updated', 
                    'insurance_company_detail_id' => 'Parent company changed', 
                    'payer_ids' => 'payer id updated', 
                    'receiver_ids' => 'receiver id updated', 
                    'provider_code' => 'Provider code updated', 
                    'company_type_id' => 'Company type updated', 
                    'charge_type_id' => 'Charge type updated', 
                    'start_date' => 'Start date updated', 
                    'end_date' => 'End date updated', 
                    'min_limit' => 'min_limit updated', 
                    'max_limit' => 'max_limit updated',
                    'claim_no' => 'Claim number updated',
                    'e_auth' => 'e_auth updated',
                    'activity_clinician' => 'activity clinician updated',
                    'eligiblity' => '',
                    'pharmacy_token' => 'pharmacy token updated',
                    'no_lab_xml' => 'No Lab XML updated',
                    'contact_person' => 'Contact person name updated',
                    'designation' => 'designation name updated',
                    'department' => 'department name changed',
                    'contact_phone' => 'Contact phone number changed',
                    'contact_mobile' => 'Contact mobile number changed',
                    'contact_fax' => 'Contact FAX number updated',
                    'contact_email' => 'Email ID updated',
                    'address' => 'Address updated',
                    'country_id' => 'Country changed',
                    'region_id' => 'Region changed',
                    'city_id' => 'City changed',
                    'pincode' => 'Pincode number updated',
                    'billing_phone' => 'Billing phone number updated',
                    'billing_fax' => 'Billing FAX number updated',
                    'website' => 'Website address updated',
                    'is_status' => 'Status changed'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $company->id, 'MasterLog', 'InsuranceCompanyDetail', $old_value, $company, $field_names);
                return $this->sendResponse(1,200, 'Company details updated scessfully', 'company_id', $company->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }


        } catch(\Exception $e) {
            Log::debug('API UpdateInsCompany :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleInsCompany(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'company_id'    => 'required|integer|exists:insurance_company_details,id,is_active,1'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

            // if($this->VerifyPageAccess('insurance-list', 'is_edit') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

                $main_company = ViewInsuranceCompanyDetail::select('company_id', 'form_type', 'form_type_name', 'name', 'short_code', 'mediator_id', 'mediator_short_code', 'mediator_company_name', 'payer_ids', 'receiver_ids', 'provider_code', 'company_type_id', 'charge_type_id', 'start_date', 'end_date', 'contact_person', 'designation', 'department', 'contact_phone', 'contact_mobile', 'contact_fax', 'contact_email', 'address', 'country_id', 'region_id', 'city_id', 'pincode', 'billing_phone', 'billing_fax', 'website', 'min_limit', 'max_limit', 'claim_no', 'outsource_lab', 'e_auth', 'activity_clinician', 'eligiblity', 'pharmacy_token', 'no_lab_xml', 'benefit_package', 'is_status')
                ->where(['company_id' => $request->company_id])->get();
                
                return $this->sendResponse(1,200, 'Success', 'data', $main_company);
            


        } catch(\Exception $e) {
            Log::debug('API GetSingleInsCompany :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function GetInsCompanyList(Request $request) {
        try {
            $main_company = ViewInsuranceCompanyDetail::select('company_id', 'form_type', 'form_type_name', 'name', 'short_code')
            ->WHERE(['form_type' => 1, 'is_status' => 1])
            ->ORDERBY('name', 'ASC')->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $main_company);
        } catch(\Exception $e) {
            Log::debug('API GetInsCompanyList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ViewInsCompany(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

            // if($this->VerifyPageAccess('insurance-list', 'is_view') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }
                $main_company = ViewInsuranceCompanyDetail::select('company_id', 'form_type', 'form_type_name', 'name', 'short_code', 'mediator_id', 'payer_ids', 'receiver_ids', 'provider_code', 'company_type_id', 'company_type_name','charge_type_name','charge_type_id', 'start_date', 'end_date', 'contact_person', 'designation', 'department', 'contact_phone', 'contact_mobile', 'contact_fax', 'contact_email', 'address', 'country_id', 'country_name', 'region_id', 'region_name', 'city_id', 'city_name', 'pincode', 'billing_phone', 'billing_fax', 'website', 'min_limit', 'max_limit', 'claim_no', 'outsource_lab', 'e_auth', 'activity_clinician', 'eligiblity', 'pharmacy_token', 'no_lab_xml', 'is_status', 'benefit_package')
                        ->where(['form_type' => 1])
                        ->ORDERBY('name', 'ASC')->get();
                $response = [];
                $result = array();
                foreach($main_company as $main) {
                    $main_company_id = $main->company_id;
                    $sub_company = ViewInsuranceCompanyDetail::select('company_id', 'form_type', 'form_type_name', 'name as company_name' , 'short_code', 'mediator_id', 'payer_ids', 'receiver_ids', 'company_type_name','charge_type_name', 'provider_code', 'company_type_id', 'charge_type_id', 'start_date', 'end_date', 'contact_person', 'designation', 'department', 'contact_phone', 'contact_mobile', 'contact_fax', 'contact_email', 'address', 'country_id', 'country_name', 'region_id', 'region_name', 'city_id', 'city_name', 'pincode', 'billing_phone', 'billing_fax', 'website', 'min_limit', 'max_limit', 'claim_no', 'outsource_lab', 'e_auth', 'activity_clinician', 'eligiblity', 'pharmacy_token', 'no_lab_xml', 'is_status', 'benefit_package')
                        ->where(['mediator_id'=> $main_company_id, 'form_type' => 2])
                        ->ORDERBY('name', 'ASC')->get();
                        $res1[] = [
                            'company_id'=>$main->company_id, 
                            'form_type_name' =>$main->form_type_name,
                            'company_name' => $main->name,
                            'company_type_name' => $main->company_type_name,
                            'charge_type_name' => $main->charge_type_name,
                            'short_code' => $main->short_code,
                            'payer_ids'  => $main->payer_ids,
                            'receiver_ids'=>$main->receiver_ids, 
                            'provider_code' =>$main->provider_code,
                            'start_date' => ($main->start_date != null ? date('d-m-Y', strtotime($main->start_date)) : ''),
                            'end_date' => ($main->end_date != null ? date('d-m-Y', strtotime($main->end_date)) : ''),
                            'contact_person'  => $main->contact_person,
                            'designation'=>$main->designation, 
                            'department' =>$main->department,
                            'contact_phone' => $main->contact_phone,
                            'contact_mobile' => $main->contact_mobile,
                            'contact_fax'  => $main->contact_fax,
                            'contact_email'=>$main->contact_email, 
                            'address' =>$main->address,
                            'country_name' => $main->country_name,
                            'region_name' => $main->region_name,
                            'city_name'  => $main->city_name,
                            'pincode' => $main->pincode,
                            'billing_fax' => $main->billing_fax,
                            'website'  => $main->website,
                            'min_limit' => $main->min_limit,
                            'max_limit'  => $main->max_limit,
                            'claim_no'=>$main->claim_no, 
                            'outsource_lab' =>$main->outsource_lab,
                            'e_auth' => $main->e_auth,
                            'activity_clinician' => $main->activity_clinician,
                            'eligiblity'  => $main->eligiblity,
                            'pharmacy_token' => $main->pharmacy_token,
                            'no_lab_xml' => $main->no_lab_xml,
                            'benefit_package' => $main->benefit_package,
                            'is_status' => $main->is_status,
                            'sub_company' => $sub_company,
                        ];
                }
                $result['company_list'] = $res1;
                //$res1 = array();

                $response = $result;
                return $this->sendResponse(1,200, 'Success', 'data', $response);
            


        } catch(\Exception $e) {
            Log::debug('API ViewInsCompany :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteInsCompany(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'company_id' => 'required|integer|exists:insurance_company_details,id,is_active,1',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

            // if($this->VerifyPageAccess('insurance-list', 'is_delete') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }

            $city = InsuranceCompanyDetail::find($request->company_id);
            $city->is_active = 0;
            $city->updated_by = $request->user_id;
            $city->updated_at = date('Y-m-d H:i:s');
            $city->update();

            if($request->company_id) {
                $rec_count = InsuranceCompanyDetail::where(['insurance_company_detail_id' => $request->company_id, 'is_active'=>1])->get();
                if(count($rec_count) != 0) {
                    $update = InsuranceCompanyDetail::where(['insurance_company_detail_id'=>$request->company_id])
                        ->update([
                            'is_active'=>0, 
                            'updated_by'=> $request->user_id, 
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);

                        $delete_logs = $this->DeleteLogs($request->user_id, $request->company_id, 'MasterLog', 'InsuranceCompanyDetail', 'Description');
                }
                
                return $this->sendResponse(1,200, 'Insurance company deleted successfully', '');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }


        } catch(\Exception $e) {
            Log::debug('API DeleteInsCompany :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateInsCompanyStatus(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'company_id'    => 'required|integer|exists:insurance_company_details,id,is_active,1',
                'field_value'   => 'required|integer|in:0,1',
                'field_name'    => 'required|string|in:is_status,claim_no,outsource_lab,e_auth,activity_clinician,eligiblity,pharmacy_token,no_lab_xml,benefit_package'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

            // if($this->VerifyPageAccess('insurance-list', 'is_edit') === false){
            //     return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            // }
            $old_value = InsuranceCompanyDetail::SELECT('id', 'is_status','claim_no','outsource_lab','e_auth','activity_clinician','eligiblity','pharmacy_token','no_lab_xml')
            ->where(['id' => $request->company_id])->get();

            $field_name = $request->field_name;
            $city = InsuranceCompanyDetail::find($request->company_id);
            $city->$field_name = $request->field_value;
            $city->updated_by = $request->user_id;
            $city->updated_at = date('Y-m-d H:i:s');
            $city->update();

            if($request->company_id) { 
                $field_names = [
                    $field_name => $field_name.' updated'
                ];
                $update_logs = $this->UpdateLogs($request->user_id, $request->company_id, 'MasterLog', 'ReferralDoctor', $old_value, $city, $field_names);               
                return $this->sendResponse(1,200, 'Updated Successfully');
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateInsCompanyStatus :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetInsMainCompanyList(Request $request) {
        try {

            $main_company = ViewInsuranceCompanyDetail::select('company_id',  'name', 'short_code')
                ->where(['is_status' => 1, 'form_type'=>1])
                ->ORDERBY('name', 'ASC')->get();
                
            return $this->sendResponse(1,200, 'Success', 'data', $main_company);

        } catch(\Exception $e) {
            Log::debug('API GetInsMainCompanyList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetInsMainCompanyListNew(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'       => 'required|integer|exists:users,id,is_active,1',
                'company_id'    => 'required',
                'is_status'     => 'required|integer|in:0,1',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $company_id = $request->company_id;
            $is_status = $request->is_status;
            if($is_status == 1){
                $main_company = ViewInsuranceCompanyDetail::select('company_id',  'name', 'short_code')
                    ->whereIn('id', $company_id)
                    ->where(['is_status' => 1, 'form_type'=>1])
                    ->ORDERBY('name', 'ASC')->get();
            } else {
                $main_company = ViewInsuranceCompanyDetail::select('company_id',  'name', 'short_code')
                    ->whereIn('id', $company_id)
                    ->where(['form_type'=>1])
                    ->ORDERBY('name', 'ASC')->get();
            }
                
            return $this->sendResponse(1,200, 'Success', 'data', $main_company);

        } catch(\Exception $e) {
            Log::debug('API GetInsMainCompanyList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetInsSubCompanyList($id) {
        try {

            $sub_company = ViewInsuranceCompanyDetail::select('company_id',  'name', 'short_code')
                ->where(['is_status' => 1, 'mediator_id' => $id, 'form_type'=>2])
                ->ORDERBY('name', 'ASC')->get();
                
            return $this->sendResponse(1,200, 'Success', 'data', $sub_company);

        } catch(\Exception $e) {
            Log::debug('API GetInsSubCompanyList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetShiftList(Request $request) {
        try {

            $master = ShiftMaster::SELECT('id',  'shift_name', 'start_time', 'end_time', 'total_hours', 'start_date', 'end_date', 'work_session', 'is_enabled')
                        ->WHERE(['is_active' => 1])->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $master);
            
        } catch(\Exception $e) {
            Log::debug("API GetShiftList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AddNewShift(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'shift_name' => 'required|string|min:3|max:30',
                'start_time' => 'required',
                'end_time' => 'required',
                'total_hours' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'work_session' => 'required|string|min:1|max:30',
                'is_enabled' => 'required|in:1,0'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $master = ShiftMaster::create([
                    'shift_name' => $request->shift_name,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                    'total_hours' => $request->total_hours,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'work_session' => $request->work_session,
                    'is_enabled' => $request->is_enabled,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($master->id) {
                    return $this->sendResponse(1,200, 'Shift master created successfully', 'master_id', $master->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddNewShift :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateShift(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'shift_id' => 'required|integer|exists:shift_masters,id,is_active,1',
                'shift_name' => 'required|string|min:3|max:30',
                'start_time' => 'required',
                'end_time' => 'required',
                'total_hours' => 'required',
                'start_date' => 'required|date',
                'end_date' => 'required|date',
                'work_session' => 'required|string|min:1|max:30',
                'is_enabled' => 'required|in:1,0'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = ShiftMaster::where(['id' => $request->shift_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $master = ShiftMaster::find($request->shift_id);
                    $master->shift_name = $request->shift_name;
                    $master->start_time = $request->start_time;
                    $master->end_time = $request->end_time;
                    $master->total_hours = $request->total_hours;
                    $master->start_date = $request->start_date;
                    $master->end_date = $request->end_date;
                    $master->work_session = $request->work_session;
                    $master->is_enabled = $request->is_enabled;
                    $master->updated_by = $request->user_id;
                    $master->updated_at = date('Y-m-d H:i:s');
                    $master->update();
                    

                    if($request->shift_id) {
                        $field_names = [
                            'shift_name' => 'Shift name updated',
                            'start_time' => 'Shift start_time updated',
                            'end_time' => 'Shift end_time updated',
                            'total_hours' => 'Shift total_hours updated',
                            'start_date' => 'Shift start_date updated',
                            'end_date' => 'Shift end_date updated',
                            'work_session' => 'Shift work_session updated',
                            'is_enabled' => 'Shift status updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $master->id, 'MasterLog', 'ShiftMaster', $old_value, $master, $field_names);
                        return $this->sendResponse(1,200, 'Shift master updated successfully', 'shift_id', $request->shift_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'nationality_id', $request->nationality_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateShift :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function DeleteShift(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'shift_id' => 'required|integer|exists:shift_masters,id,is_active,1',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $rec_count = ShiftMaster::where(['id' => $request->shift_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $master = ShiftMaster::find($request->shift_id);
                    $master->is_active = 0;
                    $master->updated_by = $request->user_id;
                    $master->updated_at = date('Y-m-d H:i:s');
                    $master->update();

                    if($request->shift_id) {
                        $delete_logs = $this->DeleteLogs($request->user_id, $master->id, 'MasterLog', 'ShiftMaster', 'Description');
                        return $this->sendResponse(1,200, 'Shift master deleted successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', '', $request->shift_id);
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API DeleteShift :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleShift($id) {
        try {
            $master = ShiftMaster::SELECT('id',  'shift_name', 'start_time', 'end_time', 'total_hours', 'start_date', 'end_date', 'work_session', 'is_enabled')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($master->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $master);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $master->count());
            }
        } catch(\Exception $e) {
            Log::debug('API ShiftMaster :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetAdminAccessNurse(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctor_id' => 'required|integer|exists:users,id,is_active,1',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $users = ViewAdminAccess::SELECT('admin_access', 'nurse_id', 'nurse_name')
                        ->where(['doctor_id' => $request->doctor_id])->get();

            return $this->sendResponse(1,200, 'Success', 'data', $users);

        } catch(\Exception $e) {
            Log::debug("API GetAdminAccessNurse:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function EnableAdminAccess(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctor_id' => 'required|integer|exists:users,id,role_id,4',
                'nurse_id' => 'required',
                'admin_access' => 'required|integer|in:0,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $nurse_ids = $request->nurse_id;

            foreach ($nurse_ids as $nurse) {
                $old_value = AdminAccess::SELECT('id', 'admin_access')->where(['doctor_id' => $request->doctor_id, 'nurse_id'=> $nurse, 'is_active' => 1])->get();
                if($old_value->count() != 0) {
                    $up_desc = AdminAccess::find($old_value[0]->id);
                    $up_desc->admin_access  = $request->admin_access;
                    $up_desc->doctor_id  = $request->doctor_id;
                    $up_desc->nurse_id  = $nurse;
                    $up_desc->updated_by    = $request->user_id;
                    $up_desc->updated_at    = date('Y-m-d H:i:s');
                    $up_desc->update();
                    $field_names = [
                        'admin_access' => 'Admin access changed'
                    ];
                    $update_logs = $this->UpdateLogs($request->user_id, $request->doctor_id, 'UserLog', 'AdminAccess', $old_value, $up_desc, $field_names);
                } else {
                    $doctor = AdminAccess::create([
                        'doctor_id' => $request->doctor_id,
                        'admin_access'   => $request->admin_access,
                        'nurse_id'     => $nurse,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                }
            }                                                                   
            return $this->sendResponse(1,200, 'Admin access updated');
            
            
        } catch(\Exception $e) {
            Log::debug("API EnableAdminAccess:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function RemoveAdminAccess(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctor_id' => 'required|integer|exists:users,id,role_id,4,is_active,1',
                'nurse_id' => 'required|integer|exists:users,id,role_id,5,is_active,1',
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }
            $nurse_id = $request->nurse_id;

            
            $old_value = AdminAccess::SELECT('id', 'admin_access')->where(['doctor_id' => $request->doctor_id, 'nurse_id'=> $nurse_id, 'is_active' => 1])->get();
            if($old_value->count() != 0) {
                $up_desc = AdminAccess::find($old_value[0]->id);
                $up_desc->is_active  = 0;
                $up_desc->updated_by    = $request->user_id;
                $up_desc->updated_at    = date('Y-m-d H:i:s');
                $up_desc->update();

                $delete_logs = $this->DeleteLogs($request->user_id, $old_value[0]->id, 'UserLog', 'AdminAccess', 'Description');
                                
            }
            return $this->sendResponse(1,200, 'Admin access removed');
            
            
        } catch(\Exception $e) {
            Log::debug("API RemoveAdminAccess:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    // public function UpdateDoctorSetting(Request $request) {
    //     try {

    //         $validator = Validator::make($request->all(), [
    //             'user_id'           => 'required|integer|exists:users,id,is_active,1',
    //             'doctor_id'         => 'required|integer|exists:view_doctor_lists,id',
    //             'location_id'       => 'required|integer|exists:hospital_details,id,is_active,1',
    //             'license_no'        => 'nullable|min:3|max:20',
    //             'active'            => 'required|integer|in:0,1',
    //             'qualification_id'  => 'required|integer|exists:qualification,id,is_active,1',
    //             'expiry_date'       => 'nullable|date',
    //             'notify_expiry_days'=> 'nullable|integer',
    //             // 'shift_master_id'   => 'required|integer|exists:shift_masters,id,is_active,1',
    //             'slot_interval'     => 'required|integer',
    //             'view_appointment'  => 'required|integer|in:0,1',
    //             'clinician_type'    => 'nullable|in:Ordinary Clinician,Activity Clinician',
    //             'em_guidelines'     => 'nullable|in:1995 Guidelines,1997 Guidelines',
    //             'em_validator'      => 'required|integer|in:0,1',
    //             'lock_encounter_days'=> 'nullable|integer',
    //             'maternity_chart'   => 'required|integer|in:0,1',
    //             'followUp_required_EMR' => 'required|integer|in:0,1',
    //             'child_mental_health' => 'required|integer|in:0,1',
    //             'disable_SMS_doctor' => 'required|integer|in:0,1',
    //             'disable_exam_normal' => 'required|integer|in:0,1',
    //             'copy_prescription' => 'required|integer|in:0,1',
    //             'unsigned_charts'   => 'required|integer|in:0,1',
    //             'refresh_time_unsigned_charts'  => 'nullable|string|min:1|max:100'
    //         ]);

    //         if($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         }

    //         if($this->VerifyAuthUser($request->user_id, 0) === false){
    //             return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
    //         }

    //         $expiry_date = null;
    //         if($request->expiry_date != ''){
    //             $expiry_date = date("Y-m-d", strtotime($request->expiry_date)); 
    //         }

    //         $old_value = DoctorSetting::where(['user_id' => $request->doctor_id, 'is_active' =>1])->get();

    //         if($old_value->count() != 0) {
    //                 $up_desc = DoctorSetting::find($old_value[0]->id);
    //                 // $up_desc->shift_master_id   = $request->shift_master_id;
    //                 $up_desc->location_id     = $request->location_id;
    //                 $up_desc->license_no        = $request->license_no;
    //                 $up_desc->active        = $request->active;
    //                 $up_desc->qualification_id        = $request->qualification_id;
    //                 $up_desc->expiry_date       = $expiry_date;
    //                 $up_desc->notify_expiry_days= $request->notify_expiry_days;
    //                 $up_desc->slot_interval     = $request->slot_interval;
    //                 $up_desc->view_appointment  = $request->view_appointment;
    //                 $up_desc->clinician_type    = $request->clinician_type;
    //                 $up_desc->em_guidelines     = $request->em_guidelines;
    //                 $up_desc->em_validator      = $request->em_validator;
    //                 $up_desc->lock_encounter_days      = $request->lock_encounter_days;
    //                 $up_desc->maternity_chart      = $request->maternity_chart;
    //                 $up_desc->followUp_required_EMR      = $request->followUp_required_EMR;
    //                 $up_desc->child_mental_health      = $request->child_mental_health;
    //                 $up_desc->disable_SMS_doctor      = $request->disable_SMS_doctor;
    //                 $up_desc->disable_exam_normal      = $request->disable_exam_normal;
    //                 $up_desc->copy_prescription      = $request->copy_prescription;
    //                 $up_desc->unsigned_charts      = $request->unsigned_charts;
    //                 $up_desc->refresh_time_unsigned_charts      = $request->refresh_time_unsigned_charts;
    //                 $up_desc->updated_by        = $request->user_id;
    //                 $up_desc->updated_at        = date('Y-m-d H:i:s');
    //                 $up_desc->update();
    //                 $field_names = [
    //                     'location_id' => 'Location updated',
    //                     'license_no' => 'License No. updated',
    //                     'active' => 'Active updated',
    //                     'qualification_id' => 'Qualification updated',
    //                     'expiry_date' => 'Expiry date updated',
    //                     'notify_expiry_days' => 'Notifing Expiry days updated',
    //                     'slot_interval' => 'Slot interval duration updated',
    //                     'view_appointment' => 'View appointment access changed',
    //                     'clinician_type' => 'Clinician Type changed',
    //                     'em_guidelines' => 'E amd M guidelines changed',
    //                     'em_validator' => 'E amd M Validator changed',
    //                     'lock_encounter_days' => 'Lock encounter days changed',
    //                     'maternity_chart' => 'Maternity chart changed',
    //                     'followUp_required_EMR' => 'Follow-up Required in EMR changed',
    //                     'child_mental_health' => 'Child Mental Health changed',
    //                     'disable_SMS_doctor' => 'Disable SMS for this Doctor changed',
    //                     'disable_exam_normal' => 'Disable in Exam Normal changed',
    //                     'copy_prescription' => 'Copy Prescription changed',
    //                     'unsigned_charts' => 'Unsigned Charts changed',
    //                     'refresh_time_unsigned_charts' => 'Refresh time for unsigned Charts changed',
    //                 ];
    //             $update_logs = $this->UpdateLogs($request->user_id, $old_value[0]->id, 'UserLog', 'DoctorSetting', $old_value, $up_desc, $field_names);
    //             return $this->sendResponse(1,200, 'Doctor setting updated');
    //         } else {
    //             $doctor = DoctorSetting::create([
    //                 'user_id' => $request->doctor_id,
    //                 'location_id' => $request->location_id,
    //                 'license_no'    => $request->license_no,
    //                 'active'        => $request->active,
    //                 'qualification_id' => $request->qualification_id,
    //                 'expiry_date'       => $expiry_date,
    //                 'notify_expiry_days'=> $request->notify_expiry_days,
    //                 // 'shift_master_id'   => $request->shift_master_id,
    //                 'slot_interval'     => $request->slot_interval,
    //                 'view_appointment'  => $request->view_appointment,
    //                 'clinician_type'    => $request->clinician_type,
    //                 'em_guidelines'     => $request->em_guidelines,
    //                 'em_validator'     => $request->em_validator,
    //                 'lock_encounter_days' => $request->lock_encounter_days,
    //                 'maternity_chart'     => $request->maternity_chart,
    //                 'view_Appt'     => $request->view_Appt,
    //                 'followUp_required_EMR'     => $request->followUp_required_EMR,
    //                 'child_mental_health'     => $request->child_mental_health,
    //                 'disable_SMS_doctor'     => $request->disable_SMS_doctor,
    //                 'disable_exam_normal'     => $request->disable_exam_normal,
    //                 'copy_prescription'     => $request->copy_prescription,
    //                 'unsigned_charts'     => $request->unsigned_charts,
    //                 'refresh_time_unsigned_charts'     => $request->refresh_time_unsigned_charts,
    //                 'created_by' => $request->user_id,
    //                 'updated_by' => $request->user_id,
    //                 'created_at' => date('Y-m-d H:i:s'),
    //                 'updated_at' => date('Y-m-d H:i:s')
    //             ]);
    //             return $this->sendResponse(1,200, 'Doctor setting updated');
    //         }
            
    //     } catch(\Exception $e) {
    //         Log::debug("API UpdateDoctorSetting:: ".$e->getMessage());
    //         return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
    //     }
    // }
    
    // public function UpdateDoctorSetting(Request $request) {
    //     try {
    //         // Validation rules
    //         $validator = Validator::make($request->all(), [
    //             'user_id'           => 'required|integer|exists:users,id,is_active,1',
    //             'doctor_id'         => 'required|integer|exists:view_doctor_lists,id',
    //             'license_no'        => 'nullable|min:3|max:20',
    //             'active'            => 'required|integer|in:0,1',
    //             'expiry_date'       => 'nullable|date',
    //             'notify_expiry_days' => 'nullable|integer',
    //             'slot_interval'     => 'required|integer|in:5,10,15,20,25,30,35,40,45,50,55,60',
    //             'view_appointment'  => 'required|integer|in:0,1',
    //             'clinician_type'    => 'nullable|in:Ordinary Clinician,Activity Clinician',
    //             'em_guidelines'     => 'nullable|in:1995 Guidelines,1997 Guidelines',
    //             'em_validator'      => 'required|integer|in:0,1',
    //             'lock_encounter_days' => 'nullable|integer',
    //             'maternity_chart'   => 'required|integer|in:0,1',
    //             'followUp_required_EMR' => 'required|integer|in:0,1',
    //             'child_mental_health' => 'required|integer|in:0,1',
    //             'disable_SMS_doctor' => 'required|integer|in:0,1',
    //             'disable_exam_normal' => 'required|integer|in:0,1',
    //             'copy_prescription' => 'required|integer|in:0,1',
    //             'unsigned_charts'   => 'required|integer|in:0,1',
    //             'refresh_time_unsigned_charts' => 'nullable|string|min:1|max:100',
    //             'department_category_id' => 'nullable|integer',
    //             'qualification_id'        => 'nullable|integer',
    //             'location_id'   => 'nullable|integer',
    //             'title_id'      => 'required|integer',
    //             'first_name'    => 'required|string|min:3|max:30',
    //             'middle_name'   => 'nullable|string|min:0|max:20',
    //             'last_name'     => 'required|string|min:1|max:30',
    //             'username'      => 'required|string|min:3|max:30',
    //             'password'      => 'required|string|min:3|max:100',
    //             'role_id'       => 'required|integer',
    //             'department_id'         => 'nullable|integer',
    //             'current_address_line' => 'nullable|string|min:0|max:200',
    //             'current_address_postbox' => 'nullable|string|min:0|max:200',
    //             'current_country_id'    => 'nullable|integer',
    //             'current_city_id'       => 'nullable|integer',
    //             'current_region_id'     => 'nullable|integer',
    //             'current_email'         => 'nullable|email|max:50',
    //             'current_mobile'        => 'nullable|string|min:7|max:20',
    //             'current_phone'        => 'nullable|string|min:7|max:20',
    //             'profile_img'   => 'mimes:jpeg,jpg,png|required|max:5120',
    //             'signature1' => 'nullable|mimes:jpeg,jpg,png|max:5120',
    //             'signature2' => 'nullable|mimes:jpeg,jpg,png|max:5120',
    //             'signature3' => 'nullable|mimes:jpeg,jpg,png|max:5120',
    //             'consultations'     => 'nullable|array',
    //             'consultations.*.consultation' => 'required_with:consultations|string|max:255',
    //             'consultations.*.charge' => 'required_with:consultations|numeric|min:0'
    //         ]);
    
    //         if ($validator->fails()) {
    //             return $this->sendResponse(0, 200, $validator->errors()->first(), '');
    //         }
    
    //         if ($this->VerifyAuthUser($request->user_id, 0) === false) {
    //             return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
    //         }
    
    //         // Handle expiry_date
    //         $expiry_date = null;
    //         if ($request->filled('expiry_date')) {
    //             $expiry_date = date('Y-m-d', strtotime($request->expiry_date));
    //         }
    
    //         // Start a transaction
    //         DB::beginTransaction();
    
    //         // Fetch DoctorSetting with locking
    //         $old_value = DoctorSetting::where(['user_id' => $request->doctor_id, 'is_active' => 1])
    //             ->lockForUpdate()
    //             ->first();
    
    //         // Log query result
    //         Log::debug("DoctorSetting query for user_id: {$request->doctor_id}, is_active: 1", [
    //             'found' => $old_value ? 'Yes' : 'No',
    //             'doctor_id' => $request->doctor_id,
    //             'user_id' => $request->user_id,
    //         ]);
    
    //         if (!$old_value) {
    //             Log::info("No DoctorSetting found, creating new for user_id: {$request->doctor_id}");
    //             // Create new DoctorSetting
    //             $doctor = DoctorSetting::create([
    //                 'user_id' => $request->doctor_id,
    //                 'license_no' => $request->license_no,
    //                 'active' => $request->active,
    //                 'expiry_date' => $expiry_date,
    //                 'notify_expiry_days' => $request->notify_expiry_days,
    //                 'slot_interval' => $request->slot_interval,
    //                 'view_appointment' => $request->view_appointment,
    //                 'clinician_type' => $request->clinician_type,
    //                 'em_guidelines' => $request->em_guidelines,
    //                 'em_validator' => $request->em_validator,
    //                 'lock_encounter_days' => $request->lock_encounter_days,
    //                 'maternity_chart' => $request->maternity_chart,
    //                 'followUp_required_EMR' => $request->followUp_required_EMR,
    //                 'child_mental_health' => $request->child_mental_health,
    //                 'disable_SMS_doctor' => $request->disable_SMS_doctor,
    //                 'disable_exam_normal' => $request->disable_exam_normal,
    //                 'copy_prescription' => $request->copy_prescription,
    //                 'unsigned_charts' => $request->unsigned_charts,
    //                 'refresh_time_unsigned_charts' => $request->refresh_time_unsigned_charts,
    //                 'department_category_id' => $request->department_category_id,
    //                 'is_active' => 1,
    //                 'created_by' => $request->user_id,
    //                 'updated_by' => $request->user_id,
    //                 'created_at' => now(),
    //                 'updated_at' => now(),
    //             ]);
    //         } else {
    //             Log::info("Updating DoctorSetting for user_id: {$request->doctor_id}");
    //             // Update existing DoctorSetting
    //             $original_value = $old_value->replicate(); // Store original for logging
    //             $old_value->license_no = $request->license_no;
    //             $old_value->active = $request->active;
    //             $old_value->expiry_date = $expiry_date;
    //             $old_value->notify_expiry_days = $request->notify_expiry_days;
    //             $old_value->slot_interval = $request->slot_interval;
    //             $old_value->view_appointment = $request->view_appointment;
    //             $old_value->clinician_type = $request->clinician_type;
    //             $old_value->em_guidelines = $request->em_guidelines;
    //             $old_value->em_validator = $request->em_validator;
    //             $old_value->lock_encounter_days = $request->lock_encounter_days;
    //             $old_value->maternity_chart = $request->maternity_chart;
    //             $old_value->followUp_required_EMR = $request->followUp_required_EMR;
    //             $old_value->child_mental_health = $request->child_mental_health;
    //             $old_value->disable_SMS_doctor = $request->disable_SMS_doctor;
    //             $old_value->disable_exam_normal = $request->disable_exam_normal;
    //             $old_value->copy_prescription = $request->copy_prescription;
    //             $old_value->unsigned_charts = $request->unsigned_charts;
    //             $old_value->refresh_time_unsigned_charts = $request->refresh_time_unsigned_charts;
    //             $old_value->department_category_id = $request->department_category_id;
    //             $old_value->updated_by = $request->user_id;
    //             $old_value->updated_at = now();
    //             $old_value->save();
    
    //             // Log DoctorSetting updates
    //             $field_names = [
    //                 'license_no' => 'License No. updated',
    //                 'active' => 'Active updated',
    //                 'expiry_date' => 'Expiry date updated',
    //                 'notify_expiry_days' => 'Notifying Expiry days updated',
    //                 'slot_interval' => 'Slot interval duration updated',
    //                 'view_appointment' => 'View appointment access changed',
    //                 'clinician_type' => 'Clinician Type changed',
    //                 'em_guidelines' => 'E and M guidelines changed',
    //                 'em_validator' => 'E and M Validator changed',
    //                 'lock_encounter_days' => 'Lock encounter days changed',
    //                 'maternity_chart' => 'Maternity chart changed',
    //                 'followUp_required_EMR' => 'Follow-up Required in EMR changed',
    //                 'child_mental_health' => 'Child Mental Health changed',
    //                 'disable_SMS_doctor' => 'Disable SMS for this Doctor changed',
    //                 'disable_exam_normal' => 'Disable in Exam Normal changed',
    //                 'copy_prescription' => 'Copy Prescription changed',
    //                 'unsigned_charts' => 'Unsigned Charts changed',
    //                 'refresh_time_unsigned_charts' => 'Refresh time for unsigned Charts changed',
    //                 'department_category_id' => 'Department category updated',
    //             ];
    //             $this->UpdateLogs($request->user_id, $old_value->id, 'UserLog', 'DoctorSetting', $original_value, $old_value, $field_names);
    //         }
    
    //         // Handle consultations for DoctorFee table
    //         if ($request->has('consultations') && is_array($request->consultations)) {
    //             $existingFees = DoctorFee::where('doctor_id', $request->doctor_id)
    //                 ->where('is_active', 1)
    //                 ->get()
    //                 ->keyBy('consultation');
    
    //             foreach ($request->consultations as $consultation) {
    //                 $consultationName = $consultation['consultation'];
    //                 $charge = $consultation['charge'];
    
    //                 if (isset($existingFees[$consultationName])) {
    //                     $fee = $existingFees[$consultationName];
    //                     $oldFee = $fee->replicate();
    //                     $fee->charges = $charge;
    //                     $fee->updated_by = $request->user_id;
    //                     $fee->updated_at = now();
    //                     $fee->save();
    
    //                     $feeFieldNames = [
    //                         'charges' => 'Consultation charge updated for ' . $consultationName,
    //                     ];
    //                     $this->UpdateLogs($request->user_id, $fee->id, 'UserLog', 'DoctorFee', $oldFee, $fee, $feeFieldNames);
    //                 } else {
    //                     DoctorFee::create([
    //                         'doctor_id' => $request->doctor_id,
    //                         'consultation' => $consultationName,
    //                         'charges' => $charge,
    //                         'is_active' => 1,
    //                         'created_by' => $request->user_id,
    //                         'updated_by' => $request->user_id,
    //                         'created_at' => now(),
    //                         'updated_at' => now(),
    //                     ]);
    //                 }
    //             }
    
    //             $providedConsultations = array_column($request->consultations, 'consultation');
    //             $deactivatedFees = DoctorFee::where('doctor_id', $request->doctor_id)
    //                 ->whereNotIn('consultation', $providedConsultations)
    //                 ->where('is_active', 1)
    //                 ->get();
    
    //             foreach ($deactivatedFees as $fee) {
    //                 $oldFee = $fee->replicate();
    //                 $fee->is_active = 0;
    //                 $fee->updated_by = $request->user_id;
    //                 $fee->updated_at = now();
    //                 $fee->save();
    
    //                 $feeFieldNames = [
    //                     'is_active' => 'Consultation ' . $fee->consultation . ' deactivated',
    //                 ];
    //                 $this->UpdateLogs($request->user_id, $fee->id, 'UserLog', 'DoctorFee', $oldFee, $fee, $feeFieldNames);
    //             }
    //         }
    
    //         DB::commit();
    //         return $this->sendResponse(1, 200, 'Doctor setting and consultation fees updated successfully');
    
    //     } catch (\Exception $e) {
    //         DB::rollBack();
    //         Log::error("UpdateDoctorSetting Error: {$e->getMessage()} | Line: {$e->getLine()} | File: {$e->getFile()}", [
    //             'doctor_id' => $request->doctor_id,
    //             'user_id' => $request->user_id,
    //             'old_value' => $old_value ? $old_value->toArray() : null,
    //         ]);
    //         return $this->sendResponse(0, 200, 'Failed to update doctor settings: ' . $e->getMessage(), 'error', $e->getMessage());
    //     }
    // }
    
    public function UpdateDoctorSetting(Request $request) {
        DB::beginTransaction();
        try {
            Log::debug("UpdateDoctorSetting: Starting with user_id={$request->user_id}, doctor_id={$request->doctor_id}");
    
            // Validation rules
            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,user_status,1',
                'doctor_id'         => 'required|integer|exists:users,id,user_status,1',
                'license_no'        => 'required|string|min:3|max:20',
                'arabic_name'        => 'required|string|min:3|max:20',
                'active'            => 'required|integer|in:0,1',
                'expiry_date'       => 'nullable|date',
                'notify_expiry_days' => 'nullable|integer',
                'slot_interval'     => 'required|integer|in:5,10,15,20,25,30,35,40,45,50,55,60',
                'view_appointment'  => 'required|integer|in:0,1',
                'clinician_type'    => 'nullable|in:Ordinary Clinician,Activity Clinician',
                'em_guidelines'     => 'nullable|in:1995 Guidelines,1997 Guidelines',
                'em_validator'      => 'required|integer|in:0,1',
                'lock_encounter_days' => 'nullable|integer',
                'maternity_chart'   => 'required|integer|in:0,1',
                'followUp_required_EMR' => 'required|integer|in:0,1',
                'child_mental_health' => 'required|integer|in:0,1',
                'disable_SMS_doctor' => 'required|integer|in:0,1',
                'disable_exam_normal' => 'required|integer|in:0,1',
                'copy_prescription' => 'required|integer|in:0,1',
                'unsigned_charts'   => 'required|integer|in:0,1',
                'morningShift_act'   => 'required|integer|in:0,1',
                'morningShift_block'   => 'required|integer|in:0,1',
                'eveningShift_act'   => 'required|integer|in:0,1',
                'eveningShift_block'   => 'required|integer|in:0,1',
                'fullShift_act'   => 'required|integer|in:0,1',
                'fullShift_block'   => 'required|integer|in:0,1',
                'ramadanShift_act'   => 'required|integer|in:0,1',
                'ramadanShift_block'   => 'required|integer|in:0,1',
                'refresh_time_unsigned_charts' => 'nullable|string|min:1|max:100',
                'department_category_id' => 'nullable|integer|exists:dept_categories,id',
                'qualification_id'  => 'nullable|integer|exists:qualification,id',
                'location_id'       => 'nullable|integer|exists:hospital_details,id',
                'title_id'          => 'required|integer|exists:titles,id',
                'first_name'        => 'required|string|min:3|max:30',
                'middle_name'       => 'nullable|string|min:0|max:20',
                'last_name'         => 'required|string|min:1|max:30',
                'username'          => 'required|string|min:3|max:30',
                'password'          => 'nullable|string|min:3|max:100',
                'role_id'           => 'required|integer|exists:roles,id',
                'department_id'     => 'nullable|integer|exists:departments,id',
                'emp_doj'       => 'required|date',
                'current_address_line' => 'nullable|string|min:0|max:200',
                'current_address_postbox' => 'nullable|string|min:0|max:200',
                'current_country_id' => 'nullable|integer|exists:countries,id',
                'current_city_id'   => 'nullable|integer|exists:cities,id',
                'current_region_id' => 'nullable|integer|exists:regions,id',
                'current_email'     => 'nullable|email|max:50',
                'current_mobile'    => 'nullable|string|min:7|max:20',
                'current_phone'     => 'nullable|string|min:7|max:20',
                'profile_img'       => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature1'        => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature2'        => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'signature3'        => 'nullable|mimes:jpeg,jpg,png|max:5120',
                'consultations'     => 'nullable|array',
                'consultations.*.consultation' => 'required_with:consultations|string|max:255',
                'consultations.*.charge' => 'required_with:consultations|numeric|min:0'
            ]);
    
            if ($validator->fails()) {
                DB::rollBack();
                Log::warning("Validation failed: " . $validator->errors()->first());
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
    
            if ($this->VerifyAuthUser($request->user_id, 0) === false) {
                DB::rollBack();
                Log::warning("Auth user verification failed for user_id={$request->user_id}");
                return $this->sendResponse(0, 200, 'Login user token is not matching. Login again and continue', '');
            }
    
            if($this->VerifyPageAccess('user', 'is_edit') === false){
                DB::rollBack();
                return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            }
    
            // User update logic
            if (strrpos($request->username, ' ') !== false){
                DB::rollBack();
                return $this->sendResponse(0,200, 'Space not allowed in Username', '');
            }
    
            $user_count = User::where(['username' => $request->username, 'is_active' => 1])
                        ->where('id', '<>', $request->doctor_id)->count();
            if($user_count > 0) {
                DB::rollBack();
                return $this->sendResponse(0,200, 'Username already exist', '');
            }
    
            $mobile_count = User::where(['current_mobile' => $request->current_mobile, 'is_active' => 1])
                        ->where('id', '<>', $request->doctor_id)->count();
            if($mobile_count > 0) {
                DB::rollBack();
                return $this->sendResponse(0,200, 'Mobile Number already exist', '');
            }
    
            $email_count = User::where(['current_email' => $request->current_email, 'is_active' => 1])
                        ->where('id', '<>', $request->doctor_id)->count();
            if($email_count > 0) {
                DB::rollBack();
                return $this->sendResponse(0,200, 'Email ID already exist', '');
            }
    
            $adduser = User::find($request->doctor_id);
            if(!$adduser) {
                DB::rollBack();
                return $this->sendResponse(0,200, 'Doctor user not found', '');
            }
    
            $old_value = User::where(['id' => $request->doctor_id, 'is_active' => 1])->get();
    
            // Handle profile image and signatures
            if($request->hasFile('profile_img')){
                $profile_logo = $request->file('profile_img');
                $extension = $profile_logo->extension();
                $adduser->profile_img = time().'1_'.str_replace(' ', '_',$request->username).'.'.$extension;
                $destinationPath = public_path('/profile');
                $profile_logo->move($destinationPath, $adduser->profile_img);
            }
            if ($request->hasFile('signature1')) {
                $signature1_file = $request->file('signature1');
                $extension = $signature1_file->extension();
                $adduser->signature1 = time() . '_sig1_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                $destinationPath = public_path('/profile');
                $signature1_file->move($destinationPath, $adduser->signature1);
            }
            if ($request->hasFile('signature2')) {
                $signature2_file = $request->file('signature2');
                $extension = $signature2_file->extension();
                $adduser->signature2 = time() . '_sig2_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                $destinationPath = public_path('/profile');
                $signature2_file->move($destinationPath, $adduser->signature2);
            }
            if ($request->hasFile('signature3')) {
                $signature3_file = $request->file('signature3');
                $extension = $signature3_file->extension();
                $adduser->signature3 = time() . '_sig3_' . str_replace(' ', '_', $request->username) . '.' . $extension;
                $destinationPath = public_path('/profile');
                $signature3_file->move($destinationPath, $adduser->signature3);
            }
    
            $adduser->title_id = $request->title_id;
            $adduser->first_name = $request->first_name;
            $adduser->middle_name = $request->middle_name;
            $adduser->last_name = $request->last_name;
            $adduser->full_name = $request->first_name.' '.$request->middle_name.' '.$request->last_name;
            $adduser->username = $request->username;
            if ($request->filled('password')) {
                $adduser->password = Hash::make($request->password);
            }
            $adduser->hospital_detail_id = $request->location_id;
            $adduser->role_id = $request->role_id;
            $adduser->emp_doj = $request->filled('emp_doj') ? date('Y-m-d', strtotime($request->emp_doj)) : null;
            $adduser->current_address_line = $request->current_address_line;
            $adduser->current_address_postbox = $request->current_address_postbox;
            $adduser->current_country_id = $request->current_country_id;
            $adduser->current_city_id = $request->current_city_id;
            $adduser->current_region_id = $request->current_region_id;
            $adduser->current_email = $request->current_email;
            $adduser->current_mobile = $request->current_mobile;
            $adduser->current_phone  = $request->current_phone;
            $adduser->qualification_id  = $request->qualification_id;
            $adduser->updated_by = $request->user_id;
            $adduser->updated_at = date('Y-m-d H:i:s');
            $adduser->update();
    
            $field_names = [
                'first_name' => 'Firstname changed',
                'middle_name' => 'Middlename changed',
                'last_name' => 'Lastname changed',
                'username' => 'Username changed',
                'password' => 'Password changed',
                'profile_img' => 'Profile image updated',
                'signature1' => 'Signature1 image updated',
                'signature2' => 'Signature2 image updated',
                'signature3' => 'Signature3 image updated',
                'title_id' => 'Title changed',
                'role_id' => 'Role changed',
                'current_address_line' => 'Current address line updated',
                'current_address_postbox' => 'Current address postbox updated',
                'current_country_id' => 'Current country updated',
                'current_city_id' => 'Current city updated',
                'current_region_id' => 'Current region updated',
                'current_email' => 'Current email updated',
                'current_mobile' => 'Current mobile updated',
                'qualification_id' => 'Qualification updated'
            ];
            $this->UpdateLogs($request->user_id, $adduser->id, 'UserLog', 'User', $old_value, $adduser, $field_names);
    
            // DoctorSetting update logic
            $expiry_date = null;
            if ($request->filled('expiry_date')) {
                $expiry_date = date('Y-m-d', strtotime($request->expiry_date));
            }
            
            $oldDoctor_value = DoctorSetting::where(['user_id' => $request->doctor_id, 'is_active' => 1])->first();
            Log::debug("oldDoctor_value: ", [$oldDoctor_value]);
            
            if (!$oldDoctor_value) {
                $doctor = DoctorSetting::create([
                    'user_id' => $request->doctor_id,
                    'license_no' => $request->license_no,
                    'arabic_name' => $request->arabic_name,
                    'active' => $request->active,
                    'expiry_date' => $request->filled('expiry_date') ? date('Y-m-d', strtotime($request->expiry_date)) : null,
                    'notify_expiry_days' => $request->notify_expiry_days,
                    'slot_interval' => $request->slot_interval,
                    'view_appointment' => $request->view_appointment,
                    'clinician_type' => $request->clinician_type,
                    'em_guidelines' => $request->em_guidelines,
                    'em_validator' => $request->em_validator,
                    'lock_encounter_days' => $request->lock_encounter_days,
                    'maternity_chart' => $request->maternity_chart,
                    'followUp_required_EMR' => $request->followUp_required_EMR,
                    'child_mental_health' => $request->child_mental_health,
                    'disable_SMS_doctor' => $request->disable_SMS_doctor,
                    'disable_exam_normal' => $request->disable_exam_normal,
                    'copy_prescription' => $request->copy_prescription,
                    'unsigned_charts' => $request->unsigned_charts,
                    'refresh_time_unsigned_charts' => $request->refresh_time_unsigned_charts,
                    'department_category_id' => $request->department_category_id,
                    'morningShift_act' => $request->morningShift_act,
                    'morningShift_block' => $request->morningShift_block,
                    'eveningShift_act' => $request->eveningShift_act,
                    'eveningShift_block' => $request->eveningShift_block,
                    'fullShift_act' => $request->fullShift_act,
                    'fullShift_block' => $request->fullShift_block,
                    'ramadanShift_act' => $request->ramadanShift_act,
                    'ramadanShift_block' => $request->ramadanShift_block,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                Log::debug("Created DoctorSetting: ", [$doctor]);
            } else {
                $addDoctor = $oldDoctor_value; // Use the existing model instance
                $addDoctor->license_no = $request->license_no;
                $addDoctor->arabic_name = $request->arabic_name;
                $addDoctor->active = $request->active;
                $addDoctor->expiry_date = $request->filled('expiry_date') ? date('Y-m-d', strtotime($request->expiry_date)) : null;
                $addDoctor->notify_expiry_days = $request->notify_expiry_days;
                $addDoctor->slot_interval = $request->slot_interval;
                $addDoctor->view_appointment = $request->view_appointment;
                $addDoctor->clinician_type = $request->clinician_type;
                $addDoctor->em_guidelines = $request->em_guidelines;
                $addDoctor->em_validator = $request->em_validator;
                $addDoctor->lock_encounter_days = $request->lock_encounter_days;
                $addDoctor->maternity_chart = $request->maternity_chart;
                $addDoctor->followUp_required_EMR = $request->followUp_required_EMR;
                $addDoctor->child_mental_health = $request->child_mental_health;
                $addDoctor->disable_SMS_doctor = $request->disable_SMS_doctor;
                $addDoctor->disable_exam_normal = $request->disable_exam_normal;
                $addDoctor->copy_prescription = $request->copy_prescription;
                $addDoctor->unsigned_charts = $request->unsigned_charts;
                $addDoctor->refresh_time_unsigned_charts = $request->refresh_time_unsigned_charts;
                $addDoctor->department_category_id = $request->department_category_id;
                $addDoctor->morningShift_act = $request->morningShift_act;
                $addDoctor->morningShift_block = $request->morningShift_block;
                $addDoctor->eveningShift_act = $request->eveningShift_act;
                $addDoctor->eveningShift_block = $request->eveningShift_block;
                $addDoctor->fullShift_act = $request->fullShift_act;
                $addDoctor->fullShift_block = $request->fullShift_block;
                $addDoctor->ramadanShift_act = $request->ramadanShift_act;
                $addDoctor->ramadanShift_block = $request->ramadanShift_block;
                $addDoctor->is_active = 1;
                $addDoctor->updated_by = $request->user_id;
                $addDoctor->updated_at = now();
                $addDoctor->save();
                Log::debug("Updated DoctorSetting: ", [$addDoctor]);
            }
    
            // Handle consultations for DoctorFee table
            if (!empty($request->consultations) && is_array($request->consultations)) {
                // Fetch existing fees
                $existingFees = DoctorFee::where('doctor_id', $request->doctor_id)
                    ->where('is_active', 1)
                    ->get();
            
                Log::debug('Existing DoctorFee records: ', ['count' => $existingFees->count(), 'records' => $existingFees->toArray()]);
                foreach ($request->consultations as $consultation) {
                    // Validate consultation data
                    
                    if (!isset($consultation['consultation']) || !isset($consultation['charge']) || empty($consultation['consultation']) || !is_numeric($consultation['charge'])) {
                        DB::rollBack();
                        Log::warning('Invalid consultation data: ', ['consultation' => $consultation]);
                        return $this->sendResponse(0, 200, 'Invalid consultation data provided', '');
                    }
            
                    $consultationName = trim($consultation['consultation']);
                    $charge = (float) $consultation['charge'];
            
                    // Find matching fee
                    $fee = null;
                    
                    foreach ($existingFees as $existingFee) {
                        if (strtolower(trim($existingFee->consultation)) === strtolower($consultationName)) {
                            $fee = $existingFee;
                            break;
                        }
                    }
            
                    if ($fee) {
                        
                        // Update existing fee
                        Log::debug('Updating fee for consultation: ', ['consultation' => $consultationName, 'id' => $fee->id]);
                        $oldFee = clone $fee; // Use clone instead of replicate() for older Laravel compatibility
                        $fee->charges = $charge;
                        $fee->updated_by = $request->user_id;
                        $fee->updated_at = date('Y-m-d H:i:s'); // Use raw date for older compatibility
                        $fee->save();
                        
                        // $feeFieldNames = [
                        //     'charges' => 'Consultation charge updated for ' . $consultationName,
                        // ];
                        $this->UpdateLogs($request->user_id, $fee->id, 'UserLog', 'DoctorFee', $oldFee, $fee);
                        
                // return $this->sendResponse(0,200, $feeFieldNames);
                    } else {
                        // Create new fee
                        Log::debug('Creating new fee for consultation: ', ['consultation' => $consultationName]);
                        $newFee = DoctorFee::create([
                            'doctor_id' => $request->doctor_id,
                            'consultation' => $consultationName,
                            'charges' => $charge,
                            'is_active' => 1,
                            'created_by' => $request->user_id,
                            'updated_by' => $request->user_id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                        Log::debug('Created new fee: ', ['id' => $newFee->id]);
                    }
                }
            
                // Deactivate fees not in the request
                $providedConsultations = array_map(function ($consultation) {
                    return trim($consultation['consultation']);
                }, $request->consultations);
            
                $feesToDeactivate = DoctorFee::where('doctor_id', $request->doctor_id)
                    ->where('is_active', 1)
                    ->whereNotIn('consultation', $providedConsultations)
                    ->get();
            
                foreach ($feesToDeactivate as $fee) {
                    $oldFee = clone $fee;
                    $fee->is_active = 0;
                    $fee->updated_by = $request->user_id;
                    $fee->updated_at = date('Y-m-d H:i:s');
                    $fee->save();
            
                    // $feeFieldNames = [
                    //     'is_active' => 'Consultation ' . $fee->consultation . ' deactivated',
                    // ];
                    $this->UpdateLogs($request->user_id, $fee->id, 'UserLog', 'DoctorFee', $oldFee, $fee);
                }
            }
    
            DB::commit();
            Log::info("UpdateDoctorSetting: Successfully updated for doctor_id={$request->doctor_id}");
            return $this->sendResponse(1, 200, 'Doctor settings, user details, and consultation fees updated successfully');
    
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("UpdateDoctorSetting Error: {$e->getMessage()} | Line: {$e->getLine()} | File: {$e->getFile()}", [
                'doctor_id' => $request->doctor_id,
                'user_id' => $request->user_id,
            ]);
            return $this->sendResponse(0, 200, 'Failed to update doctor settings: ' . $e->getMessage(), 'error', $e->getMessage());
        }
    }

    public function GetVisitTypeList(Request $request) {
        try {
            $list = VisitType::SELECT('id', 'name')->where(['is_active' => 1])->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $list);

        } catch(\Exception $e) {
            Log::debug('API GetVisitTypeList:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function GetExamList(Request $request) {
        try {
            $list = Exam::SELECT('id', 'name')->where(['is_active' => 1])->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $list);

        } catch(\Exception $e) {
            Log::debug('API GetExamList:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }


    public function GetMasterLogs(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id'           => 'required|integer|exists:users,id,is_active,1',
                'log_id'            => 'required|integer',
                'log_name'          => 'required|in:City,Region,Country,Role,Department,Gender,Title,JobTitle,DeptCategory,UserAuditLog,HospitalType,Nationality,Religion,Education,Ethnic,IncomeRange,Language,Occupation,PatientClass,Relationship,MaritalStatus,BloodGroup,PaymentMode,ReferralChannel,ReferralSource,Industry,InsuranceCompanyDetail,CompanyType,ChargeType,ShiftMaster,DoctorSetting,AdminAccess,ViewAdminAccess,VisitType,Exam,EnquiryReason,EnquiryService,ReferralClinic,ReferralDoctor,InsuranceNetwork,InsurancePackage,InsurancePlan,AppointmentStatus,SmsTemplate,PlanDetail,PlanDetailDescription,Department,DeptCategory'

            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }
            if($this->VerifyAuthUser($request->user_id, 2) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $log_id = $request->log_id;
            $log_name = $request->log_name;

            $list = MasterLog::SELECT('id', 'description', 'table_id', 'updated_by', 'updated_at')
                    ->WHERE(['table_id' => $log_id, 'table_name' => $log_name, 'action_type'=> 'Update']) ->ORDERBY('id', 'DESC')->LIMIT(30)->get();

            return $this->sendResponse(1, 200, 'Success', 'data', $list);

        } catch(\Exception $e) {
            Log::debug('API GetExamList:: '.$e->getMessage());
            return response()->json([
                'status' => 0,
                'message'   =>  'Something went wrong try again after sometime.',
                'error' => $e->getMessage()
            ]);
        }
    }

    public function AddVisaType(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'visa_type' => 'required|string|min:3|max:100',
                'short_code' => 'nullable|string|min:1|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $visatype = VisaType::create([
                    'visa_type' => $request->visa_type,
                    'short_code' => $request->short_code,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($visatype->id) {
                    return $this->sendResponse(1,200, 'Visa Type created successfully', 'visatype_id', $visatype->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddVisaType :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function UpdateVisaType(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                "visa_type_id" => 'required|integer',
                'visa_type' => 'required|string|min:3|max:100',
                'short_code' => 'nullable|string|min:1|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = VisaType::where(['id' => $request->visa_type_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $visatype = VisaType::find($request->visa_type_id);
                    $visatype->visa_type = $request->visa_type;
                    $visatype->short_code = $request->short_code;
                    $visatype->updated_by = $request->user_id;
                    $visatype->updated_at = date('Y-m-d H:i:s');
                    $visatype->update();
                    
                    if($request->visa_type_id) {
                        $field_names = [
                            'visa_type' => 'VisaType name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $visatype->id, 'MasterLog', 'VisaType', $old_value, $visatype, $field_names);
                        return $this->sendResponse(1,200, 'VisaType updated successfully', 'visa_type_id', $request->visa_type_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateVisaType :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function DeleteVisaType(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'visa_type_id' => 'required|integer|exists:visa_types,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $VisaType = VisaType::find($request->visa_type_id);
            $VisaType->is_active = 0;
            $VisaType->updated_by = $request->user_id;
            $VisaType->updated_at = date('Y-m-d H:i:s');
            $VisaType->update();

            if($VisaType->id) {
                return $this->sendResponse(1,200, 'VisaType deleted successfully', 'visa_type_id', $VisaType->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteVisaType:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetVisaType(Request $request) {
        try {

            $VisaType = VisaType::SELECT('id', 'visa_type', 'short_code')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $VisaType);
            
        } catch(\Exception $e) {
            Log::debug("API GetVisaType:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetSingleVisaType($id) {
        try {
            $VisaType = VisaType::SELECT('id',  'visa_type', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($VisaType->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $VisaType);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $VisaType->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleVisaType :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function AddQualification(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'name' => 'required|string|min:3|max:100',
                'short_code' => 'nullable|string|min:1|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $qualification = Qualification::create([
                    'name' => $request->name,
                    'short_code' => $request->short_code,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($qualification->id) {
                    return $this->sendResponse(1,200, 'Qualification created successfully', 'qualification_id', $qualification->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddQualification :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function UpdateQualification(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'qualification_id' => 'required|integer',
                'name' => 'required|string|min:3|max:100',
                'short_code' => 'nullable|string|min:1|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = Qualification::where(['id' => $request->qualification_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $qualification = Qualification::find($request->qualification_id);
                    $qualification->name = $request->name;
                    $qualification->short_code = $request->short_code;
                    $qualification->updated_by = $request->user_id;
                    $qualification->updated_at = date('Y-m-d H:i:s');
                    $qualification->update();
                    
                    if($request->qualification_id) {
                        $field_names = [
                            'name' => 'Qualification name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $qualification->id, 'MasterLog', 'Qualification', $old_value, $qualification, $field_names);
                        return $this->sendResponse(1,200, 'Qualification updated successfully', 'qualification_id', $request->qualification_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateQualification :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetQualification(Request $request) {
        try {

            $qualification = Qualification::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $qualification);
            
        } catch(\Exception $e) {
            Log::debug("API GetQualification:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    } 
    
    public function GetSingleQualification($id) {
        try {
            $qualification = Qualification::SELECT('id',  'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($qualification->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $qualification);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $qualification->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleQualification :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }   
    
    public function DeleteQualification(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'qualification_id' => 'required|integer|exists:qualification,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $qualification = Qualification::find($request->qualification_id);
            $qualification->is_active = 0;
            $qualification->updated_by = $request->user_id;
            $qualification->updated_at = date('Y-m-d H:i:s');
            $qualification->update();

            if($qualification->id) {
                return $this->sendResponse(1,200, 'Qualification deleted successfully', 'qualification_id', $qualification->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteQualification:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
        
    public function AddDoctorProfession(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'name' => 'required|string|min:3|max:100',
                'short_code' => 'nullable|string|min:1|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $doctorProfession = DoctorProfession::create([
                    'name' => $request->name,
                    'short_code' => $request->short_code,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($doctorProfession->id) {
                    return $this->sendResponse(1,200, 'DoctorProfession created successfully', 'doctorProfession_id', $doctorProfession->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddDoctorProfession :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
        
    public function UpdateDoctorProfession(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctorProfession_id' => 'required|integer|exists:doctor_profession,id,is_active,1',
                'name' => 'required|string|min:3|max:100',
                'short_code' => 'nullable|string|min:1|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = DoctorProfession::where(['id' => $request->doctorProfession_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $doctorProfession = DoctorProfession::find($request->doctorProfession_id);
                    $doctorProfession->name = $request->name;
                    $doctorProfession->short_code = $request->short_code;
                    $doctorProfession->updated_by = $request->user_id;
                    $doctorProfession->updated_at = date('Y-m-d H:i:s');
                    $doctorProfession->update();
                    
                    if($request->doctorProfession_id) {
                        $field_names = [
                            'name' => 'DoctorProfession name updated',
                            'short_code' => 'Short code updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $doctorProfession->id, 'MasterLog', 'DoctorProfession', $old_value, $doctorProfession, $field_names);
                        return $this->sendResponse(1,200, 'DoctorProfession updated successfully', 'doctorProfession_id', $request->doctorProfession_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateDoctorProfession :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetDoctorProfession(Request $request) {
        try {

            $doctorProfession = DoctorProfession::SELECT('id', 'name', 'short_code')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $doctorProfession);
            
        } catch(\Exception $e) {
            Log::debug("API GetQualification:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    } 
    
    public function GetSingleDoctorProfession($id) {
        try {
            $doctorProfession = DoctorProfession::SELECT('id',  'name', 'short_code')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($doctorProfession->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $doctorProfession);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $doctorProfession->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleDoctorProfession :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }      
    
    public function DeleteDoctorProfession(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'doctorProfession_id' => 'required|integer|exists:doctor_profession,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $doctorProfession = DoctorProfession::find($request->doctorProfession_id);
            $doctorProfession->is_active = 0;
            $doctorProfession->updated_by = $request->user_id;
            $doctorProfession->updated_at = date('Y-m-d H:i:s');
            $doctorProfession->update();

            if($doctorProfession->id) {
                return $this->sendResponse(1,200, 'DoctorProfession deleted successfully', 'doctorProfession_id', $doctorProfession->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteQualification:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
            
    public function AddCancelReason(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'name' => 'required|string|min:3|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $cancelReason = CancelReason::create([
                    'name' => $request->name,
                    'created_by' => $request->user_id,
                    'updated_by' => $request->user_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                if($cancelReason->id) {
                    return $this->sendResponse(1,200, 'CancelReason created successfully', 'cancelReason_id', $cancelReason->id);
                } else {
                    return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API AddCancelReason :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
        
    public function UpdateCancelReason(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'cancelReason_id' => 'required|integer|exists:tbl_cancelreason,id,is_active,1',
                'name' => 'required|string|min:3|max:100'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $old_value = CancelReason::where(['id' => $request->cancelReason_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $cancelReason = CancelReason::find($request->cancelReason_id);
                    $cancelReason->name = $request->name;
                    $cancelReason->updated_by = $request->user_id;
                    $cancelReason->updated_at = date('Y-m-d H:i:s');
                    $cancelReason->update();
                    
                    if($request->cancelReason_id) {
                        $field_names = [
                            'name' => 'CancelReason name updated'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $cancelReason->id, 'MasterLog', 'CancelReason', $old_value, $cancelReason, $field_names);
                        return $this->sendResponse(1,200, 'CancelReason updated successfully', 'cancelReason_id', $request->cancelReason_id);
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API UpdateCancelReason :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
    
    public function GetCancelReason(Request $request) {
        try {

            $cancelReason = CancelReason::SELECT('id', 'name')
                        ->WHERE(['is_active' => 1])
                        ->ORDERBY('id', 'ASC')->get();
            
            return $this->sendResponse(1, 200, 'Success', 'data', $cancelReason);
            
        } catch(\Exception $e) {
            Log::debug("API GetCancelReason:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    } 
    
    public function GetSingleCancelReason($id) {
        try {
            $cancelReason = CancelReason::SELECT('id',  'name')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($cancelReason->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $cancelReason);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $cancelReason->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleCancelReason :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }      
    
    public function DeleteCancelReason(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer|exists:users,id,is_active,1',
                'cancelReason_id' => 'required|integer|exists:tbl_cancelreason,id,is_active,1'
            ]);

            if($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Login again and continue', '');
            }

            $cancelReason = CancelReason::find($request->cancelReason_id);
            $cancelReason->is_active = 0;
            $cancelReason->updated_by = $request->user_id;
            $cancelReason->updated_at = date('Y-m-d H:i:s');
            $cancelReason->update();

            if($cancelReason->id) {
                return $this->sendResponse(1,200, 'CancelReason deleted successfully', 'cancelReason_id', $cancelReason->id);
            } else {
                return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
            }
            
        } catch(\Exception $e) {
            Log::debug("API DeleteCancelReason:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }
}
