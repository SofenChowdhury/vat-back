<?php

namespace App\Repositories;

use App\Classes\FileUpload;
use App\Models\Admin;
use Illuminate\Auth\Events\Validated;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class AdminRepository implements BaseRepository
{

     protected  $model;
     protected  $file;

     public function __construct(Admin $model, FileUpload $file)
     {
          $this->model = $model;
          $this->file = $file;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll()
     {
          return $this->model::with('roles.permissions', 'permissions', 'company')->get();
     }

     /**
      *  specified resource get .
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */

     public function getById(int $id)
     {
          $admin = $this->model::with('roles.permissions', 'permissions', 'company', 'branch')->find($id);
          return response()->json(['status' => true, 'data' => $admin]);
     }

     /**
      * resource create
      * @param $request
      * @return \Illuminate\Http\Response
      */

     public function create($request)
     {
          try {
               $admin = $this->model;
               $admin->name = $request->name;
               $admin->email = $request->email;
               $admin->phone = $request->phone;
               $admin->company_id = $request->company_id ? $request->company_id : NULL;
               $admin->branch_id = $request->branch_id ? $request->branch_id : NULL;
               $admin->password = Hash::make($request->password);
               $admin->status = 1;
               $admin->save();
               $admin->assignRole($request->admin_roles);
               return $admin;
               // return response()->json(['status'=>true ,'data'=> $admin]);

          } catch (\Throwable $th) {
               return $th->getMessage();
               // dd($th->getMessage());
          }
     }

     /**
      * specified resource update
      *
      * @param int $id
      * @param $request
      * @return \Illuminate\Http\Response
      */

     public function update(int $id, $request)
     {


          $validator = Validator::make($request->all(), [
               'name' => 'required|string|max:255',
               'email' => 'required|string|email|unique:admins,email,' . $id,
               'phone' => 'required|string|min:11|max:18|unique:admins,phone,' . $id
          ]);

          if ($validator->fails()) {
               return response()->json(['status' => false, 'errors' => $validator->errors()]);
          }

          $admin = $this->model::with('roles', 'permissions')->find($id);
          $admin->name = $request->name;
          $admin->email = $request->email;
          $admin->phone = $request->phone;
          $admin->status = $request->status;
          $admin->company_id = $request->company_id ? $request->company_id : NULL;
          $admin->branch_id = $request->branch_id ? $request->branch_id : NULL;
          if ($request->password) {
               $admin->password = Hash::make($request->password);
          }
          $admin->update();
          $admin->syncRoles($request->roles);
          $admin->syncPermissions($request->permissions);
          return response()->json(['status' => true, 'data' => $admin]);
     }

     /**
      * Remove the specified resource from storage.
      *
      * @param  int  $id
      * @return \Illuminate\Http\Response
      */

     public function delete($id)
     {
          return $this->getById($id)->delete();
     }

     public function changePassword(int $id, $request)
     {
          $request->validate([
               'password' => 'required|confirmed|min:8'
          ]);
          // return $request->all();
          $admin = $this->getById($id);
          $admin->password = Hash::make($request->password);
          $admin->update();
     }

     public function updateProfile($request)
     {
          $user = auth()->user();
          $request->validate([
               'name' => 'required',
               'phone' => 'required'
          ]);
          $admin = $this->getById($user->id);
          $admin->name = $request->name;
          $admin->phone = $request->phone;
          if ($request->filename != "") {
               $photo = $this->file->base64ImgUpload($request, $fieldname = "picture", $file = $admin->photo, $folder = "admin/profile");
               $admin->photo =  $photo;
          }
          $admin->update();
     }

     public function findOrFail($id)
     {
          $admin = $this->model->find($id);

          return $admin;
     }

     public function searchByPhoneNameEmail($query)
     {
          return $this->model::with('company')
          ->where('company_id', auth()->user()->company_id)
          ->where(function ($q) use ($query)
          {
               $q->where('phone','LIKE','%'.$query.'%')
               ->orWhere('name', 'LIKE', '%'.$query.'%')
               ->orWhere('email', 'LIKE', '%'.$query.'%');
          })
          ->get();
     }
}