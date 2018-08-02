<?php

use Jcove\Restful\Result;
use Jcove\Restful\Utils;

/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/9
 * Time: 10:30
 */
if(!function_exists('respond')){
    function respond($data=null,$status = 200, array $headers = [], $options = 0){
        if(request()->ajax()){
            return response()->json( $data , $status ,  $headers , $options);
        }
        if(request()->acceptsJson()){
            return response()->json($data , $status ,  $headers , $options);
        }
        return restful_view($data);

    }
}
if(!function_exists('restful_vies')){
    function restful_view($view=null , $data){
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
}
