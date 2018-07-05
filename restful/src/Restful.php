<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 9:51
 */

namespace Jcove\Restful;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;

trait Restful
{
    protected $model;

    /**
     * 无需保存到数据库的字段
     * @var mixed
     */
    protected $exceptField;

    public function index(){
        $list                       =   $this->model->paginate(config('restful.page_rows'));
        $data['list']               =   $list;
        return $this->respond($data);

    }
    public function create(){
        return $this->respond();
    }

    public function show($id){
        $info                       =   $this->model->where('id',$id)->firstOrFail();
        $data['info']               =   $info;
        return $this->respond($data);
    }
    public function edit($id){
        $info                       =   $this->model->where('id',$id)->firstOrFail();
        $data['info']               =   $info;
        return $this->respond($data);
    }
    public function destroy($id){
        $this->model->where('id',$id)->delete();
        return $this->respond();
    }
    public function store(FormRequest $request){;
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->model->save();
        return $this->respond($this->model);
    }

    public function update(FormRequest $request, $id){
        $this->model                        =   $this->model->where('id',$id)->firstOrFail();
        foreach ($request->all() as $column => $value) {
            if(!in_array($column,$this->getExceptFields())){
                $this->model->setAttribute($column, $value);
            }
        }
        $this->model->save();
        return $this->respond($this->model);
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

    public function respond($data=null,$code = 0, $msg = 'success'){
        if(request()->ajax()){
            return $this->returnJson(new Result($code,$msg,$data));
        }
        if(request()->acceptsJson()){
            return $this->returnJson(new Result($code,$msg,$data));
        }
        return $this->view($data);
    }
    public function success($data){
        return $this->respond($data);
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
        return array_merge($this->exceptField,['_method,_token']);
    }
    public function returnJson(Arrayable $array){
        return response()->json($array->toArray());
    }

}