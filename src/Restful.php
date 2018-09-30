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
use Illuminate\Support\Facades\Auth;
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

    protected $title                =   '';

    protected $isUpdate               =   false;



    public function index(){
        $where                      =   [];
        if(method_exists($this,'where')){
            $where                  =   $this->where();
            if(null==$where){
                $where              =   [];
            }
        }

        $list                           =   $this->paginate($this->model,$where);

        $this->setData($list);

        if(method_exists($this,'beforeIndex')){
            $this->beforeIndex();
        }

        return $this->respond($this->data);

    }
    public function setData($list){
        if(!$this->canJson()){
            $this->data['list']         =   $list;
        }else{
            $this->data                 =   $list;
        }
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

    protected function sort($model){
        if($model){
            return $model->orderByDesc('id');
        }
        $this->model                        =   $this->model->orderByDesc('id');
    }
    /**
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     * @throws AuthorizationException
     */
    public function destroy($id){

        if(config('restful.validate_access')){
            $guard                                      =   Auth::guard() ? Auth::guard(): Auth::guard(config('restful.guard'));
            if (!$guard->user()->can('delete',   $this->model->findOrFail($id))) {
                throw new AuthorizationException(trans('message.access_denied'),403);
            }
        }

        if(method_exists($this,'beforeDelete')){
            $this->beforeDelete();
        }
        DB::transaction(function () use ($id) {
            $this->model->where('id', $id)->delete();
            if(method_exists($this,'deleted')){
                $this->deleted();
            }
        });
        return $this->success();
    }
    public function store(Request $request){
        if(method_exists($this,'validator')){
            $this->validator($request->all())->validate();
        }
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value ? $value :'');
            }
        }
        $this->save();
        $this->data['data']                              =   $this->model;
        if(method_exists($this,'beforeShow')){
            $this->beforeShow();
        }
        return $this->respond($this->data);
    }

    protected function save(){
        //执行事务
        DB::transaction(function (){
            $exist                      =   $this->model->exists;
            if($exist){
                if(method_exists($this,'beforeUpdate')){
                    $this->beforeUpdate();
                }
            }else{
                if(method_exists($this,'prepareSave')){
                    $this->prepareSave();
                }
            }
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

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\JsonResponse|\Illuminate\View\View
     * @throws AuthorizationException
     */
    public function update(Request $request,$id){


        $this->model                        =   $this->model->where('id',$id)->firstOrFail();
        $this->isUpdate                     =   true;

        if(config('restful.validate_access')){
            $guard                                      =   Auth::guard() ? Auth::guard(): Auth::guard(config('restful.guard'));
            if (!$guard->user()->can('update',  $this->model)) {
                throw new AuthorizationException(trans('message.access_denied'),403);
            }
        }

//        if(method_exists($this,'validator')){
//            $this->validator($request->all())->validate();
//        }
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->save();
        $this->data['data']                         =   $this->model;
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
     * @param Model $model
     * @param $where
     * @return array
     */
    public function paginate($model,$where){
        $all                            =   request()->input('all');
        $model                          =   $model->where($where);

        $model                          =   $this->sort($model);
        $list                           =   [];
        if($all){
            $list                       =   $model->paginate(config('restful.page_max_rows'));
        }else{
            $list                       =   $model->paginate(request()->page_size ? request()->page_size: config('restful.page_rows'));
        }
        return $list;

    }
    /**
     * @param mixed $model
     */
    public function setModel(Model $model)
    {
        $this->model = $model;
    }


    public function success($message='success'){
        $this->setTitle($message);
        return $this->respond(['message'=>$message,'code'=>0]);
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
        if(!$this->canJson()){
            if(!empty($this->title)){
                $data['title']        =   $this->title;
            }
        }
        return respond($data,$status,$headers,$options);
    }

    public function canJson(){
        return (!request()->acceptsHtml()) || request()->ajax();
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title)
    {
        $this->title = $title;
    }

    /**
     * @return bool
     */
    public function isUpdate(): bool
    {
        return $this->isUpdate;
    }

    /**
     * @param bool $isUpdate
     */
    public function setIsUpdate(bool $isUpdate)
    {
        $this->isUpdate = $isUpdate;
    }


}