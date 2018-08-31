<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 10:27
 */
return [
    'page_rows'                     =>  env('PAGE_ROWS',12),
    'page_max_rows'                 =>  env('PAGE_MAX_ROWS',100),
    'mobile_browser_prefix'         =>  env('MOBILE_BROWSER_PREFIX','mobile'),
    'pc_browser_prefix'             =>  env('PC_BROWSER_PREFIX','pc'),
    'restful_app_directory'         =>  env('RESTFUL_APP_PATH',app_path('Http')),
    'guard'                         =>  env('RESTFUL_GUARD','web'),
    'validate_access'               =>  env('RESTFUL_VALIDATE_ACCESS',true)
];