<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 9:51
 */

namespace Jcove\Restful;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait Restful
{
    protected $model;

    /**
     * 无需保存到数据库的字段
     * @var mixed
     */
    protected $exceptField          =   [];

    protected $prepareSave;
    protected $saved;



    public function index(){
        $where                      =   [];
        if(method_exists($this,'where')){
            $where                  =   $this->where();
            if(null==$where){
                $where              =   [];
            }
        }
        $list                       =   $this->model->where($where)->paginate(config('restful.page_rows'));
        $data['list']               =   $list;
        if(method_exists($this,'beforeIndex')){
            $data                   =   $this->beforeIndex($data);
        }
        return respond($data);

    }
    public function create(){
        return respond();
    }

    public function show($id){
        $info                       =   $this->model->where('id',$id)->firstOrFail();
        $data['info']               =   $info;
        if(method_exists($this,'beforeShow')){
            $data                   =   $this->beforeShow($data);
        }
        return respond($data);
    }
    public function edit($id){
        $info                       =   $this->model->where('id',$id)->firstOrFail();
        $data['info']               =   $info;
        return $this->respond($data);
    }
    public function destroy($id){
        $this->model->where('id',$id)->delete();
        return respond();
    }
    public function store(Request $request){
        if(method_exists($this,'validate')){
            $this->validator($request->all())->validate();
        }
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->save();
        return respond($this->model);
    }

    protected function save(){
        if(method_exists($this,'prepareSave')){
            $this->prepareSave();
        }

        DB::transaction(function (){
            $this->model->save();
            if(method_exists($this,'saved')){
                $this->saved();
            }

        });
    }

    public function update(Request $request,$id){
        if(method_exists($this,'validate')){
            $this->validator($request->all())->validate();
        }
        $this->model                        =   $this->model->where('id',$id)->firstOrFail();
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->save();
        return respond($this->model);
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


    public function success($data){
        return respond($data);
    }

    public function error($code,$msg){
        return respond(null,$code,$msg);
    }
    protected function view($view=null , $data){
        if(null==$view){
            $controller         =   request()->route()->getAction();
            $method             =   request()->route()->getActionMethod();
            $view               =   $controller.'.'.$method;
            if(Utils::isMobileBrowser()){
                $view           =   config('restful.mobile_browser_prefix').'.'.$view;
            }
        }

        return view($view,$data);
    }
    protected function getExceptFields(){
        return array_merge($this->exceptField,['_method','_token']);
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


}