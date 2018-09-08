<?php

use Jcove\Restful\Result;
use Jcove\Restful\Utils;

/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/9
 * Time: 10:30
 */
if(!function_exists('respond')){
    function respond($data=null,$status = 200, array $headers = [], $options = 0,$view=''){
        if(request()->ajax()){
            return response()->json( $data , $status ,  $headers , $options);
        }

        if(request()->acceptsHtml()){
            return restful_view($view,$data);
        }
        if(request()->acceptsJson()){
            return response()->json($data , $status ,  $headers , $options);
        }


    }
}
if(!function_exists('restful_vies')){
    function restful_view($view=null , $data){
        if(null==$view){
            $view               =   request()->route()->getName();
            if(Utils::isMobileBrowser()){
                $view           =   config('restful.mobile_browser_prefix').'.'.$view;
            }else{
                $view           =   config('restful.pc_browser_prefix').'.'.$view;
            }
        }

        return view($view,$data);
    }
}
if(!function_exists('storage_url')){
    function storage_url($path){
        if(strpos($path,'http')!==false || empty($path)){
            return $path;
        }
        return config('app.url').'/storage'.'/'.$path;
    }
}
if(!function_exists('original_path')){
    function original_path($url){
        if(strpos($url,'http')===false || empty($url)){
            return $url;
        }
        return str_replace(config('app.url').'/storage/','',$url);
    }
}

