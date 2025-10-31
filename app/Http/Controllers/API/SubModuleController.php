<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\MainModule;
use App\SubModule;
use App\SubModulePermission;
use App\ViewSubModulePermission;
use App\ViewMainModulePermission;
use App\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;

class SubModuleController extends BaseController
{


    public function SubMenuList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){

                if($this->VerifyPageAccess('sub-menu', 'is_view') === false){
                    return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
                }
                $menu_list = DB::table('sub_modules as sub')
                    ->join('main_modules as main', 'sub.main_module_id', '=', 'main.id')
                    ->select('sub.id as sub_menu_id', 'sub.name as sub_menu_name', 'sub.menu_order', 'sub.menu_link', 'main.name as main_menu_name')
                    ->where(['sub.is_active' => 1, 'main.is_active' => 1 ])
                    ->ORDERBY('main.menu_order', 'ASC')->get();
                return $this->sendResponse(1,200, 'Success', 'data', $menu_list);

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API SubMenuList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CreateSubMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'main_menu_id' => 'required|integer',
                'menu_name' => 'required|string|min:3|max:30',
                'menu_link' => 'required|string|min:3|max:100'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){

                if($this->VerifyPageAccess('sub-menu', 'is_add') === false){
                    return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
                }

                $menu_count = SubModule::where(['is_active' => 1, 'name'=>$request->menu_name, 'main_module_id'=> $request->main_menu_id])->get();
                if(count($menu_count) == 0) {
                    $order_count = SubModule::select('id', 'menu_order')
                                ->where(['is_active' => 1])
                                ->orderBy('menu_order', 'DESC')
                                ->limit(1)->get();
                    if(count($order_count) == 0) {
                        $menu_order = 1;
                    } else {
                        $menu_order = $order_count[0]->menu_order+1;
                    }

                    $menu = SubModule::create([
                        'name' => $request->menu_name,
                        'menu_link' => $request->menu_link,
                        'main_module_id' => $request->main_menu_id,
                        'menu_order' => $menu_order,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);

                    if($menu->id) {
                        $this->CreateSubMenuForRole($request->main_menu_id, $menu->id, $request->user_id);
                        return $this->sendResponse(1,200, 'Submenu Created successfully', '');
                    } else {
                        return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Duplicate menu name', '');
                }

            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API CreateSubMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function UpdateSubMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'sub_menu_id' => 'required|integer',
                'main_menu_id' => 'required|integer',
                'menu_name' => 'required|string|min:3|max:30',
                'menu_link' => 'required|string|min:3|max:100'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                if($this->VerifyPageAccess('sub-menu', 'is_edit') === false){
                    return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
                }
                $old_value = SubModule::where(['id' => $request->sub_menu_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $menu_count = SubModule::where(['is_active' => 1, 'name'=>$request->menu_name, 'main_module_id'=> $request->main_menu_id])->where('id', '!=', $request->sub_menu_id)->get();
                    if(count($menu_count) == 0) {
                        $order_count = SubModule::select('id', 'menu_order')
                                    ->where(['is_active' => 1])
                                    ->orderBy('menu_order', 'DESC')
                                    ->limit(1)->get();
                        if(count($order_count) == 0) {
                            $menu_order = 1;
                        } else {
                            $menu_order = $order_count[0]->menu_order+1;
                        }

                        $menu = SubModule::find($request->sub_menu_id);
                        $menu->name = $request->menu_name;
                        $menu->main_module_id = $request->main_menu_id;
                        $menu->menu_link = $request->menu_link;
                        $menu->updated_by = $request->user_id;
                        $menu->updated_at = date('Y-m-d h:i:s');
                        $menu->update();

                        if($request->sub_menu_id) {

                            $update = SubModulePermission::where(['sub_module_id' => $request->sub_menu_id])
                                ->update([
                                    'main_module_id'=>$request->main_menu_id,
                                    'updated_by'=> $request->user_id, 
                                    'updated_at' => date('Y-m-d h:i:s')
                                ]);

                            $field_names = [
                                'name' => 'Sub module name updated', 
                                'menu_link' => 'Menu link updated', 
                                'main_module_id' => 'Main Menu module changed'
                            ];
                            $update_logs = $this->UpdateLogs($request->user_id, $menu->id, 'AccessModuleLog', 'SubModule', $old_value, $menu, $field_names);

                            return $this->sendResponse(1,200, 'Submenu Updated successfully', '');
                        } else {
                            return $this->sendResponse(0,200, 'Something went wrong try again after sometime.');
                        }
                    } else {
                        return $this->sendResponse(0,200, 'Duplicate menu name', '');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'sub_menu_id', $request->sub_menu_id);
                }

            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API UpdateSubMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ReorderSubMenu(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                if($this->VerifyPageAccess('sub-menu', 'is_edit') === false){
                    return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
                }
                $menulists = $request->menulists;
                if(!empty($menulists)) {
                    foreach($menulists as $list) {
                        $menu_id = $list['sub_menu_id'];
                        $menu_order = $list['order_number'];

                        $rec_count = SubModule::where(['id' => $menu_id, 'is_active'=>1])->get();
                        if(count($rec_count) == 1) {
                            $menu = SubModule::find($menu_id);
                            $menu->menu_order = $menu_order;
                            $menu->updated_by = $request->user_id;
                            $menu->updated_at = date('Y-m-d h:i:s');
                            $menu->update();
                        }
                    }
                    return $this->sendResponse(1,200, 'Sub Menu order updated successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Sub Menu order number is required', '');
                }

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API ReorderSubMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleSubMenu($id) {
        try {
            if($this->VerifyPageAccess('sub-menu', 'is_edit') === false){
                return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            }
            $menu = SubModule::SELECT('id as sub_menu_id', 'name as menu_name',  'main_module_id as main_menu_id', 'menu_link')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($menu->count() == 1) {
                return $this->sendResponse(1,200, 'Success', 'data', $menu);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $menu->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleSubMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetMenuWiseSubMenu($id) {
        try {
            $rec_count = SubModule::where(['main_module_id' => $id, 'is_active'=>1])->get();
            //if(count($rec_count) > 0) {
                $menu_list = DB::table('sub_modules as sub')
                    ->join('main_modules as main', 'sub.main_module_id', '=', 'main.id')
                    ->select('sub.id as sub_menu_id', 'sub.name as sub_menu_name', 'sub.menu_order', 'sub.menu_link', 'main.name as main_menu_name', 'sub.main_module_id as main_menu_id')
                    ->where(['sub.is_active' => 1, 'main.is_active' => 1, 'main_module_id' => $id ])
                    ->ORDERBY('sub.menu_order', 'ASC')->get();
                return $this->sendResponse(1,200, 'Success', 'data', $menu_list);

            // } else {
            //     return $this->sendResponse(0,200, 'Record not found', '');
            // }
        } catch(\Exception $e) {
            Log::debug('API GetMenuWiseSubMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteSubMenu(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'sub_menu_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
               
                if($this->VerifyPageAccess('sub-menu', 'is_delete') === false){
                    return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
                }

                $rec_count = SubModule::where(['id' => $request->sub_menu_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $menu = SubModule::find($request->sub_menu_id);
                    $menu->is_active = 0;
                    $menu->updated_by = $request->user_id;
                    $menu->updated_at = date('Y-m-d h:i:s');
                    $menu->update();

                    $this->DeleteSubMenuForRole($request->sub_menu_id, $request->user_id);

                    $delete_logs = $this->DeleteLogs($request->user_id, $menu->id, 'AccessModuleLog', 'SubModule', 'Description');

                    return $this->sendResponse(1,200, 'Sub menu deleted successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'sub_menu_id', $request->sub_menu_id);
                }
            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug('API DeleteSubMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //While creating new main menu call this function
    public function CreateSubMenuForRole($menu_id, $sub_menu_id, $user_id) {
        try {
            $roles = Role::SELECT('id', 'name')->WHERE(['is_active' => 1])->get();
            foreach ($roles as $role) {
                $role_id = $role->id;
                if($role_id == 1) {
                    $module = SubModulePermission::create([
                        'role_id' => $role_id,
                        'main_module_id' => $menu_id,
                        'sub_module_id' => $sub_menu_id,
                        'is_permission' => 1,
                        'is_add' => 1,
                        'is_edit' => 1,
                        'is_view' => 1,
                        'is_delete' => 1,
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                } else {
                    $module = SubModulePermission::create([
                        'role_id' => $role_id,
                        'main_module_id' => $menu_id,
                        'sub_module_id' => $sub_menu_id,
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::debug('API CreateSubMenuForRole :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //If deleted main menu call this function
    public function DeleteSubMenuForRole($sub_menu_id, $user_id) {
        try {

            $update = SubModulePermission::where(['sub_module_id' => $sub_menu_id])
                    ->update([
                        'is_active'=>0, 
                        'updated_by'=> $user_id, 
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
            
            return true;
        } catch (\Exception $e) {
            Log::debug('API DeleteSubMenuForRole :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Assign sub menu section use this API
    public function GetRoleWiseSubMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer',
                'main_menu_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $list = ViewSubModulePermission::select('sub_module_id as sub_menu_id', 'sub_menu_name', 'is_permission', 'is_view', 'is_add', 'is_edit', 'is_delete')
                        ->where(['sub_role_id'=>$request->role_id, 'main_module_id'=>$request->main_menu_id])->get();

                return $this->sendResponse(1, 200, 'Success', 'data', $list);
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

        } catch(\Exception $e) {
            Log::debug('API GetRoleWiseSubMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function AssignSubMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer',
                'main_menu_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }

            if($this->VerifyPageAccess('sub-menu', 'is_edit') === false){
                return $this->sendResponse(0,200, "You don't have access for this action. Contact admin");
            }

            $modules = $request->modules;
            if(!empty($modules)) {
                foreach($modules as $module) {
                    $module_id = $module['sub_menu_id'];
                    $is_permission = $module['is_permission'];
                    $is_add = $module['is_add'];
                    $is_edit = $module['is_edit'];
                    $is_view = $module['is_view'];
                    $is_delete = $module['is_delete'];

                    $rec_count = SubModulePermission::where(['main_module_id' => $request->main_menu_id, 'role_id'=>$request->role_id, 'sub_module_id'=>$module_id, 'is_active'=>1])->get();
                    if(count($rec_count) == 1) {
                        $update = SubModulePermission::where(['main_module_id'=>$request->main_menu_id, 'role_id'=>$request->role_id, 'sub_module_id'=>$module_id,])
                            ->update([
                                'is_permission'=>$is_permission, 
                                'is_add'=>$is_add,
                                'is_edit'=>$is_edit,
                                'is_view'=>$is_view,
                                'is_delete'=>$is_delete,
                                'updated_by'=> $request->user_id, 
                                'updated_at' => date('Y-m-d h:i:s')
                            ]);
                    }
                }
                return $this->sendResponse(1,200, 'Sub module permission updated successfully', '');
            } else {
                return $this->sendResponse(0,200, 'Sub Menu module is required', '');
            }
            
        } catch (\Exception $e) {
            Log::debug('API AssignSubMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    public function SideMenuList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            }

            $image_path = config('app.image_path');
            if($this->VerifyAuthUser($request->user_id, 0) === false){
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
            
            $get_role_id = $this->GetUserRole($request->user_id); 
            if($get_role_id !== false && $request->role_id == $get_role_id){
                $main_menu = ViewMainModulePermission::select('main_module_id as menu_id', 'main_menu_name as menu_name', 'menu_icon', 'menu_link')
                        ->where(['role_id' => $get_role_id, 'is_permission' => 1])
                        ->orderBy('menu_order', 'ASC')->get();
                $response = [];
                $result = array();
                foreach($main_menu as $menu) {
                    $main_menu_id = $menu->menu_id;
                    $sub_menu = ViewSubModulePermission::select('sub_module_id as sub_menu_id', 'sub_menu_name as menu_name', 'sub_menu_link as menu_link')
                        ->where(['main_module_id'=> $main_menu_id, 'sub_role_id' => $get_role_id, 'is_permission' => 1])
                        ->orderBy('sub_menu_order', 'ASC')->get();

                    $res1[] = [
                        'menu_id'=>$main_menu_id, 
                        'menu_name' =>$menu->menu_name,
                        'menu_icon' => $menu->menu_icon,
                        'menu_link' => $menu->menu_link,
                        'sub_menu'  => $sub_menu
                    ]; 
                }
                $result['image_path'] = $image_path.'menu_icon/';
                $result['main_list'] = $res1;
                //$res1 = array();

                $response = $result;
                return $this->sendResponse(1,200, 'Success', 'data', $response);
            } else {
                return $this->sendResponse(2,200, 'Role is not matching. Login again and continue', '');
            }


        } catch(\Exception $e) {
            Log::debug('API GetSideMenuList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

}