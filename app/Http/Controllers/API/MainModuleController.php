<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\API\BaseController;
use App\MainModule;
use App\MainModulePermission;
use App\ViewMainModulePermission;
use App\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use \Validator;

class MainModuleController extends BaseController
{

    public function MainMenuList(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            $image_path = config('app.image_path');
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $list = MainModule::select('id', 'name', 'menu_icon', 'menu_order', 'menu_link', 'is_main_menu')
                        ->WHERE(['is_active' => 1])->orderBy('menu_order', 'ASC')->get();

                $response = [];
                $response['image_path'] = $image_path.'menu_icon/';
                $response['menu_list'] = $list;
                return $this->sendResponse(1,200, 'Success', 'data', $response);

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API MainMenuList:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function CreateMainMenu(Request $request) {
        try{

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                //'is_main_menu' => 'required|integer',
                'menu_icon' => 'required|string',
                'menu_name' => 'required|string|min:3|max:30',
                //'menu_link' => 'required|string|min:3|max:100'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){

                $menu_count = MainModule::where(['is_active' => 1, 'name'=>$request->menu_name])->get();
                if(count($menu_count) == 0) {
                    $order_count = MainModule::select('id', 'menu_order')
                                ->where(['is_active' => 1])
                                ->orderBy('menu_order', 'DESC')
                                ->limit(1)->get();
                    if(count($order_count) == 0) {
                        $menu_order = 1;
                    } else {
                        $menu_order = $order_count[0]->menu_order+1;
                    }

                    /* $menu_icon_name = '';
                    if($request->hasFile('menu_icon'))
                    {

                        $menu_icon = $request->file('menu_icon');
                        $extension = $request->file('menu_icon')->extension();
                        $menu_icon_name = time().str_replace(' ', '_',$request->menu_name).'.'.$extension;                    
                        $destinationPath = public_path('/menu_icon');
                        $menu_icon->move($destinationPath, $menu_icon_name);
                    } */

                    $menu = MainModule::create([
                        'name' => $request->menu_name,
                        'menu_link' => $request->menu_link,
                        //'is_main_menu' => $request->is_main_menu,
                        'menu_order' => $menu_order,
                        'menu_icon' => $request->menu_icon,
                        'created_by' => $request->user_id,
                        'updated_by' => $request->user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);

                    if($menu->id) {
                        $this->CreateMainMenuForRole($menu->id, $request->user_id);
                        return $this->sendResponse(1,200, 'Created successfully', '');
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
            Log::debug("API CreateMainMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function GetSingleMainMenu($id) {
        try {
            $image_path = config('app.image_path');
            $menu = MainModule::SELECT('id as menu_id', 'name as menu_name', 'menu_icon', 'menu_order', 'menu_link', 'is_main_menu')
                ->where(['id' => $id, 'is_active' => 1])->get();
            if($menu->count() == 1) {
                $response = [];
                $response['image_path'] = $image_path.'menu_icon/';
                $response['menu_list'] = $menu;
                return $this->sendResponse(1,200, 'Success', 'data', $response);
            } else {
                return $this->sendResponse(0,200, 'Record not found', 'count', $menu->count());
            }
        } catch(\Exception $e) {
            Log::debug('API GetSingleMainMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //Load in while creating Submenu 
    public function GetMainMenuList(Request $request) {
        try {
            $menu = MainModule::SELECT('id as main_menu_id', 'name as menu_name')
                ->where(['is_main_menu' => 0, 'is_active' => 1])
                ->orderby('menu_order', 'ASC')->get();
            
            return $this->sendResponse(1,200, 'Success', 'data', $menu);
           
        } catch(\Exception $e) {
            Log::debug('API GetMainMenuList :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function UpdateMainMenu(Request $request) {
        try{

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'menu_id' => 'required|integer',
                //'is_main_menu' => 'required|integer',
                'menu_name' => 'required|string|min:3|max:30',
                //'menu_link' => 'min:0|max:100'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){

                $old_value = MainModule::where(['id' => $request->menu_id, 'is_active'=>1])->get();
                if(count($old_value) == 1) {
                    $menu_count = MainModule::where(['is_active' => 1, 'name'=>$request->menu_name])
                            ->where('id', '!=', $request->menu_id)->get();
                    if(count($menu_count) == 0) {

                        $menu = MainModule::find($request->menu_id);
                        $menu->name = $request->menu_name;
                        //$menu->is_main_menu = $request->is_main_menu;
                        $menu->menu_link = $request->menu_link;
                        if($request->menu_icon != ''){
                            $menu->menu_icon = $request->menu_icon;
                        }
                        $menu->updated_by = $request->user_id;
                        $menu->updated_at = date('Y-m-d h:i:s');
                        $menu->update();

                        $field_names = [
                            'name' => 'Main module name updated', 
                            'menu_link' => 'Menu link updated', 
                            'menu_icon' => 'Menu icon changed'
                        ];
                        $update_logs = $this->UpdateLogs($request->user_id, $menu->id, 'AccessModuleLog', 'MainModule', $old_value, $menu, $field_names);
                        
                        return $this->sendResponse(1,200, 'Menu updated successfully', '');
                        
                    } else {
                        return $this->sendResponse(0,200, 'Duplicate menu name', '');
                    }
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'menu_id', $request->menu_id);
                }

            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API UpdateMainMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function ReorderMainMenu(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $menulists = $request->menulists;
                if(!empty($menulists)) {
                    foreach($menulists as $list) {
                        $menu_id = $list['menu_id'];
                        $menu_order = $list['order_number'];

                        $rec_count = MainModule::where(['id' => $menu_id, 'is_active'=>1])->get();
                        if(count($rec_count) == 1) {
                            $menu = MainModule::find($menu_id);
                            $menu->menu_order = $menu_order;
                            $menu->updated_by = $request->user_id;
                            $menu->updated_at = date('Y-m-d h:i:s');
                            $menu->update();
                        }
                    }
                    return $this->sendResponse(1,200, 'Menu order updated successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Menu order number is required', '');
                }

            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug("API ReorderMainMenu:: ".$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    public function DeleteMainMenu(Request $request) {
        try {

            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'menu_id' => 'required|integer'
            ]);
            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
               
                $rec_count = MainModule::where(['id' => $request->menu_id, 'is_active'=>1])->get();
                if(count($rec_count) == 1) {
                    $menu = MainModule::find($request->menu_id);
                    $menu->is_active = 0;
                    $menu->updated_by = $request->user_id;
                    $menu->updated_at = date('Y-m-d h:i:s');
                    $menu->update();
                    $this->DeleteMainMenuForRole($request->menu_id, $request->user_id);

                    $delete_logs = $this->DeleteLogs($request->user_id, $menu->id, 'AccessModuleLog', 'MainModule', 'Description');

                    return $this->sendResponse(1,200, 'Main menu deleted successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Record not found', 'menu_id', $request->menu_id);
                }
            }  else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            }
        } catch(\Exception $e) {
            Log::debug('API DeleteMainMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //While creating new main menu call this function
    public function CreateMainMenuForRole($menu_id, $user_id) {
        try {
            $roles = Role::SELECT('id', 'name')->WHERE(['is_active' => 1])->get();
            foreach ($roles as $role) {
                $role_id = $role->id;
                if($role_id == 1) {
                    $module = MainModulePermission::create([
                        'role_id' => $role_id,
                        'main_module_id' => $menu_id,
                        'is_permission' => 1,
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                } else {
                    $module = MainModulePermission::create([
                        'role_id' => $role_id,
                        'main_module_id' => $menu_id,
                        'created_by' => $user_id,
                        'updated_by' => $user_id,
                        'created_at' => date('Y-m-d h:i:s'),
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
                }
            }
            return true;
        } catch (\Exception $e) {
            Log::debug('API CreateMainMenuForRole :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }

    //If deleted main menu call this function
    public function DeleteMainMenuForRole($menu_id, $user_id) {
        try {

            $update = MainModulePermission::where(['main_module_id'=>$menu_id])
                    ->update([
                        'is_active'=>0, 
                        'updated_by'=> $user_id, 
                        'updated_at' => date('Y-m-d h:i:s')
                    ]);
            
            return true;
        } catch (\Exception $e) {
            Log::debug('API DeleteMainMenuForRole :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }


    //Assign section will use this API
    public function GetRoleWiseMainMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $list = ViewMainModulePermission::select('main_module_id as menu_id', 'main_menu_name as menu_name', 'is_permission')
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

    public function AssignMainMenu(Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'role_id' => 'required|integer'
            ]);

            if ($validator->fails()) {
                return $this->sendResponse(0, 200, $validator->errors()->first(), '');
            } 
            if($this->VerifyAuthUser($request->user_id, 1) === true){
                $modules = $request->modules;
                if(!empty($modules)) {
                    foreach($modules as $module) {
                        $module_id = $module['menu_id'];
                        $is_permission = $module['is_permission'];

                        $rec_count = MainModulePermission::where(['main_module_id' => $module_id, 'role_id'=>$request->role_id, 'is_active'=>1])->get();
                        if(count($rec_count) == 1) {
                            $update = MainModulePermission::where(['main_module_id'=>$module_id, 'role_id'=>$request->role_id])
                                ->update([
                                    'is_permission'=>$is_permission, 
                                    'updated_by'=> $request->user_id, 
                                    'updated_at' => date('Y-m-d h:i:s')
                                ]);
                        }
                    }
                    return $this->sendResponse(1,200, 'Main module permission updated successfully', '');
                } else {
                    return $this->sendResponse(0,200, 'Menu ID is required', '');
                }
            } else {
                return $this->sendResponse(0,200, 'Login user token is not matching. Invalid user or token', '');
            } 
        } catch (\Exception $e) {
            Log::debug('API AssignMainMenu :: '.$e->getMessage());
            return $this->sendResponse(0, 200, 'Something went wrong try again after sometime.', 'error', $e->getMessage());
        }
    }



}