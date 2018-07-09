<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/9
 * Time: 10:30
 */
function respond($data=null,$code = 0, $msg = 'success'){
    if(request()->ajax()){
        return $this->returnJson(new Result($code,$msg,$data));
    }
    if(request()->acceptsJson()){
        return $this->returnJson(new Result($code,$msg,$data));
    }
    return $this->view($data);

}