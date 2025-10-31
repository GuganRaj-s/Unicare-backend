<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\DashboardModule;
use App\Role;
use App\DashboardModulePermission;
use App\ViewDashboardModulePermission;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;

class DashboardModuleController extends BaseController
{

    //To display menu in dashboard section use this API
    public function DisplayDashboardMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            //$image_path = config('app.image_path');
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $get_role_id = $this->GetUserRole($request->user_id); 
                if($get_role_id !== false && $request->role_id == $get_role_id){

                    $list = DB::table('dashboard_module_permissions as dmp')
                        ->join('dashboard_modules as dm', 'dmp.dashboard_module_id', '=', 'dm.id')
                        ->select('dmp.dashboard_module_id as module_id', 'dm.name as module_name', 'dm.menu_icon','dm.menu_link', 'dm.menu_count')
                        ->where(['dmp.is_active' => 1, 'dm.is_active' => 1, 'dmp.role_id' => $get_role_id, 'dmp.is_permission' => 1 ])
                        ->orderby('dm.menu_order', 'ASC')->get();

                    $response = [];
                    //$response['image_path'] = $image_path.'menu_icon/';
                    $response['module_list'] = $list;
                    return $this->sendResponse(1,200, 'Success', 'data', $response);
                } else {
                    return $this->sendResponse(2,200, 'Role is not matching. Login again and continue', '');
                }

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug("API DashboardMenuList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DashboardMenuList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            //$image_path = config('app.image_path');
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $list = DashboardModule::select('id as module_id', 'name as module_name', 'menu_icon', 'menu_order', 'menu_link')
                        ->WHERE(['is_active' => 1])->orderBy('menu_order', 'ASC')->get();

                $response = [];
                //$response['image_path'] = $image_path.'menu_icon/';
                $response['module_list'] = $list;
                return $this->sendResponse(1,200, 'Success', 'data', $response);

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API DashboardMenuList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CreateDashboardMenu(Request $request) {
        try{

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'module_name' => 'required|string|min:3|max:30',
                'menu_link' => 'nullable|string|min:0|max:100',
                'menu_icon' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){

                $menu_count = DashboardModule::where(['is_active' => 1, 'name'=>$request->module_name])->get();
                if(count($menu_count) == 0) {

                    $order_count = DashboardModule::select('id', 'menu_order')
                                ->where(['is_active' => 1])
                                ->orderBy('menu_order', 'DESC')
                                ->limit(1)->get();
                    if(count($order_count) == 0) {
                        $menu_order = 1;
                    } else {
                        $menu_order = $order_count[0]->menu_order+1;
                    }

                    $dashboard = DashboardModule::create([
                        'name' => $request->module_name,
                        'menu_link' => $request->menu_link,
                        'menu_order' => $menu_order,
                        'menu_icon' => $request->menu_icon,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);

                    if($dashboard->id) {
                        $this->CreateDashboardMenuForRole($dashboard->id, $request->user_id);
                        return $this->sendResponse(1,200, 'Dashboard module created successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate module name', '');
                }

            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API CreateDashboardMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleDashboardMenu($id) {
        try {
            $image_path = config('app.image_path');
            $Dashboard = DashboardModule::SELECT('id as module_id', 'name  as module_name', 'menu_icon', 'menu_link')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($Dashboard->count() == 1) {
                $response = [];
                //$response['image_path'] = $image_path.'menu_icon/';
                $response['module'] = $Dashboard;
                return $this->sendResponse(1,200, 'Success', 'data', $response);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $Dashboard->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleDashboardMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateDashboardMenu(Request $request) {
        try{

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'module_id' => 'required|integer',
                'module_name' => 'required|string|min:3|max:30',
                'menu_link' => 'nullable|string|min:3|max:30',
                'menu_icon' => 'required'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){

                $old_value = DashboardModule::where(['id' => $request->module_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $menu_count = DashboardModule::where(['is_active' => 1, 'name'=>$request->module_name])
                            ->where('id', '!=', $request->module_id)->get();
                    if(count($menu_count) == 0) {

                        $dashboard = DashboardModule::find($request->module_id);
                        $dashboard->name = $request->module_name;
                        $dashboard->menu_link = $request->menu_link;
                        $dashboard->menu_icon = $request->menu_icon;
                        $dashboard->updated_by = $request->user_id;
                        $dashboard->updated_at = date('Y-m-d h:i:s');
                        $dashboard->update();
                        
                        $field_names = [
                            'name' => 'Dashboard name updated', 
                            'menu_link' => 'Dashboard link updated', 
                            'menu_icon' => 'Dashboard icon changed'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $dashboard->id, 'AccessModuleLog', 'DashboardModule', $old_value, $dashboard, $field_names);

                        return $this->sendResponse(1,200, 'Dashboard module updated successfully', '');
                        
                    } else {
                        return $this->sendResponse(0,200, 'Duplicate module name', '');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'module_id', $request->module_id);
                }

            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API UpdateDashboardMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ReorderDashboardMenu(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $modules = $request->modules;
                if(!empty($modules)) {
                    foreach($modules as $module) {
                        $module_id = $module['module_id'];
                        $menu_order = $module['order_number'];

                        $rec_count = DashboardModule::where(['id' => $module_id, 'is_active'=>1])->get();
                        if(count($rec_count) == 1) {
                            $dashboard = DashboardModule::find($module_id);
                            $dashboard->menu_order = $menu_order;
                            $dashboard->updated_by = $request->user_id;
                            $dashboard->updated_at = date('Y-m-d h:i:s');
                            $dashboard->update();
                        }
                    }
                    return $this->sendResponse(1,200, 'Dashboard module order updated successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Module order number is required', '');
                }

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API ReorderDashboardMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteDashboardModule(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'module_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
               
                $rec_count = DashboardModule::where(['id' => $request->module_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $menu = DashboardModule::find($request->module_id);
                    $menu->is_active = 0;
                    $menu->updated_by = $request->user_id;
                    $menu->updated_at = date('Y-m-d h:i:s');
                    $menu->update();
                    $this->DeleteDashboardMenuForRole($request->module_id, $request->user_id);

                    $delete_logs = $this->DeleteLogs($request->user_id, $menu->id, 'AccessModuleLog', 'DashboardModule', 'Description');

                    return $this->sendResponse(1,200, 'Dashboard module deleted successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'module_id', $request->module_id);
                }
            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug('API DeleteDashboardModule :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //While creating new dashboard menu call this function
    public function CreateDashboardMenuForRole($dashboard_id, $user_id) {
        try {
            $roles = Role::SELECT('id', 'name')->WHERE(['is_active' => 1])->get();
            foreach ($roles as $role) {
                $role_id = $role->id;
                if($role_id == 1) {
                    $module = DashboardModulePermission::create([
                        'role_id' => $role_id,
                        'dashboard_module_id' => $dashboard_id,
                        'is_permission' => 1,
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                } else {
                    $module = DashboardModulePermission::create([
                        'role_id' => $role_id,
                        'dashboard_module_id' => $dashboard_id,
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::debug('API CreateDashboardMenuForRole :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //If deleted dashboard menu call this function
    public function DeleteDashboardMenuForRole($dashboard_id, $user_id) {
        try {

            $update = DashboardModulePermission::where(['dashboard_module_id'=>$dashboard_id])
                    ->update([
                        'is_active'=>0, 
                        'updated_by'=> $user_id, 
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
            
            return true;
        } catch (\Exception $e) {
            Log::debug('API DeleteDashboardMenuForRole :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetRoleWiseDashboardMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $list = ViewDashboardModulePermission::select('dashboard_module_id as module_id', 'module_name', 'is_permission')
                        ->where(['role_id'=>$request->role_id])->get();

                return $this->sendResponse(1, 200, 'Success', 'data', $list);
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API GetRoleWiseMainMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AssignDashboardMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 0) === true){
                $modules = $request->modules;
                if(!empty($modules)) {
                    foreach($modules as $module) {
                        $module_id = $module['module_id'];
                        $is_permission = $module['is_permission'];

                        $rec_count = DashboardModulePermission::where(['dashboard_module_id' => $module_id, 'role_id'=>$request->role_id, 'is_active'=>1])->get();
                        if(count($rec_count) == 1) {
                            $update = DashboardModulePermission::where(['dashboard_module_id'=>$module_id, 'role_id'=>$request->role_id])
                                ->update([
                                    'is_permission'=>$is_permission, 
                                    'updated_by'=> $request->user_id, 
                                    'updated_at' => date('Y-m-d h:i:s')
                                ]);
                        }
                    }
                    return $this->sendResponse(1,200, 'Dashboard module permission updated successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Module ID is required', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            } 
        } catch (\Exception $e) {
            Log::debug('API AssignDashboardMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


}