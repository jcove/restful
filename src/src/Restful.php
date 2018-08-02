<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 9:51
 */

namespace Jcove\Restful;

use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\UnauthorizedException;

trait Restful
{
    protected $model;

    /**
     * 无需保存到数据库的字段
     * @var mixed
     */
    protected $exceptField          =   [];

    protected $data                 =   [];



    public function index(){
        $where                      =   [];
        if(method_exists($this,'where')){
            $where                  =   $this->where();
            if(null==$where){
                $where              =   [];
            }
        }
        $all                            =   request()->all;
        if($all){
            $list                       =   $this->model->where($where)->paginate(config('restful.page_max_rows'));
        }else{
            $list                       =   $this->model->where($where)->paginate(config('restful.page_rows'));
        }

        $this->data                 =   $list;
        if(method_exists($this,'beforeIndex')){
            $this->beforeIndex();
        }
        return $this->respond($this->data);

    }
    public function create(){
        return $this->respond();
    }

    public function show($id){
        $this->model                        =   $this->model->where('id',$id)->firstOrFail();
        if(method_exists($this,'beforeShow')){
            $this->beforeShow();
        }
        $this->data['data']                 =   $this->model;
        return $this->respond($this->data);
    }
    public function edit($id){
        $info                               =   $this->model->where('id',$id)->firstOrFail();
        $this->data                         =   $info;
        return $this->respond($this->data);
    }
    public function destroy($id){
        $this->model->where('id',$id)->delete();
        return $this->success();
    }
    public function store(Request $request){
        if(method_exists($this,'validator')){
            $this->validator($request->all())->validate();
        }
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->save();
        $this->data                              =   $this->model;
        if(method_exists($this,'beforeShow')){
            $this->beforeShow();
        }
        return $this->respond($this->data);
    }

    protected function save(){
        //执行事务
        DB::transaction(function (){
            if(method_exists($this,'prepareSave')){
                $this->prepareSave();
            }
            $exist                      =   $this->model->exists;
            $this->model->save();

            if($exist){
                if(method_exists($this,'updated')){
                    $this->updated();
                }
            }else{
                if(method_exists($this,'saved')){
                    $this->saved();
                }
            }
        });
    }


    public function update(Request $request,$id){

        if(method_exists($this,'validator')){
            $this->validator($request->all())->validate();
        }
        $this->model                        =   $this->model->where('id',$id)->firstOrFail();
//        if (!$request->user()->can('update',  $this->model)) {
//            throw new AuthorizationException(trans('message.access_denied'),403);
//        }
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->save();
        $this->data                         =   $this->model;
        if(method_exists($this,'beforeShow')){
            $this->beforeShow();
        }

        return $this->respond($this->data);
    }

    /**
     * @return mixed
     */
    public function getModel()
    {
        return $this->model;
    }

    /**
     * @param mixed $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }


    public function success(){
        return $this->respond(['message'=>'success']);
    }

    public function fail($message,$status){
        return $this->respond(['message'=>$message],$status);
    }

    protected function getExceptFields(){
        return array_merge($this->exceptField,['_method','_token','api_token']);
    }
    public function returnJson(Arrayable $array){
        return response()->json($array->toArray());
    }

    /**
     * @param mixed $exceptField
     */
    public function setExceptField($exceptField)
    {
        $this->exceptField = $exceptField;
    }

    public function respond($data = [], $status = 200, array $headers = [], $options = 0){
        return respond($data,$status,$headers,$options);
    }

}