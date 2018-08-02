<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 17:13
 */

namespace Jcove\Restful;


use Illuminate\Support\ServiceProvider;

class RestfulServiceProvider extends ServiceProvider
{
    protected $commands =   [
      'Jcove\Restful\Console\RestfulCommand'
    ];
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([__DIR__.'/../config' => config_path()], 'restful-config');
        }
    }
    public function register()
    {
        $this->commands($this->commands);
    }
}