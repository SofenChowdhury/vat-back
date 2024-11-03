<?php
namespace App\Repositories;

use App\Classes\FileUpload;
use App\Models\Setting;
use Illuminate\Support\Str;
use Mahabub\CrudGenerator\Contracts\BaseRepository;

class SettingRepository implements BaseRepository{

     protected  $model;
     protected  $file;
     
     public function __construct(Setting $model, FileUpload $file)
     {
        $this->model=$model;
        $this->file = $file;
     }

     /**
      * all resource get
      * @return Collection
      */
     public function getAll(){
         return $this->model::first();
     }
     
     /**
     *  specified resource get .
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function getById(int $id){
          return $this->model::find($id);
     } 

     /**
     *  specified resource get .
     *
     * @param  string  $uuid
     * @return \Illuminate\Http\Response
     */

     
     public function getByUuid($uuid){
          return $this->model::where('uuid', $uuid)->first();
     }   

     /**
     * resource create
     * @param  $request
     * @return \Illuminate\Http\Response
     */

     public function create($request){
          $series = $this->model;
          $series->title = $request->title;
          $series->uuid = Str::uuid();
          $series->slug = Str::slug($request->title);
          $series->save();
     }  

    /**
      * specified resource update
      *
      * @param string $id
      * @param  $request
      * @return \Illuminate\Http\Response
      */

     public function update($uuid, $request){

          $request->validate([
               "title"    => "required",              
           ]);
           
          $setting = $this->getAll();
          $setting->title = $request->title;
          $setting->contact_phone = $request->contact_phone;
          $setting->contact_email = $request->contact_email;
          $setting->sub_title = $request->sub_title;
          $logo_lg = $this->file->uploadFile($request, $fieldname="picture" ,$file=$setting->logo_lg,$folder="settings");
          $setting->logo_lg = $logo_lg?  $logo_lg: $setting->logo_lg;
          $setting->update();
     } 
        
     /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */

     public function delete($id){
       return $this->getById($id)->delete();
     }

     public function getSlider($limit=null)
     {
         if($limit!=null){
               return $this->model::where('status',1)->latest()->get();
          }else{
               return $this->model::where('status',1)->latest()->limit($limit)->get();
          }
     }
}
