<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 16:28
 */

namespace Jcove\Restful\Console;


use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class RestfulCommand extends Command
{
    protected $name                 =   'restful';
    protected $description          =   'app restful';
    protected $signature            =   'restful:generator
    {name : Class (singular) for example User}';
    protected $directory;

    public function handle(){
        $this->directory            =   app_path();
        $name = $this->argument('name');

        $this->createController($name);
        $this->createModel($name);

        File::append(base_path('routes/api.php'), PHP_EOL);
        File::append(base_path('routes/api.php'), 'Route::apiResource(\'' . lcfirst($name) . "', '{$name}Controller');");
        File::append(base_path('routes/web.php'), PHP_EOL);
        File::append(base_path('routes/web.php'), 'Route::resource(\'' . lcfirst($name) . "', '{$name}Controller');");
    }
    protected function getStub($name)
    {
        return $this->laravel['files']->get(__DIR__."/stubs/$name.stub");
    }

    public function createModel($name)
    {
        $model                      =   $this->directory.'/Models/'.$name.'.php';
        if(!file_exists($path = $this->directory.'/Models'))
            mkdir($path, 0777, true);
        if(file_exists($model)){
            $this->line('<error>'.$name.' Model file exist:</error> '.str_replace(base_path(), '', $model));
        }else{
            $modelTemplate              =   str_replace(
                ['{{modelName}}'],
                [$name],
                $this->getStub('Model')
            );

            $this->laravel['files']->put($model, $modelTemplate);
            $this->line('<info>'.$name.' Model file was created:</info> '.str_replace(base_path(), '', $model));
        }

    }
    protected function createController($name)
    {
        $controller                      =   $this->directory.'/Http/Controllers/'.$name.'Controller.php';
        if(file_exists($controller)){
            $this->line('<error>'.$name.' Controller file exist:</error> '.str_replace(base_path(), '', $controller));
        }else{
            $controllerTemplate = str_replace(
                ['{{modelName}}'],
                [$name],
                $this->getStub('Controller')
            );
            $this->laravel['files']->put($controller, $controllerTemplate);
            $this->line('<info>'.$name.' Controller file was created:</info> '.str_replace(base_path(), '', $controller));
        }

    }

    protected function createRequest($name)
    {

        $request                      =   $this->directory.'/Http/Requests/'.$name.'Request.php';
        if(file_exists($request)){
            $this->line('<error>'.$name.' Request file exist:</error> '.str_replace(base_path(), '', $request));
        }else{
            $requestTemplate = str_replace(
                ['{{modelName}}'],
                [$name],
                $this->getStub('Request')
            );
            if(!file_exists($path = $this->directory.'/Requests'))
                mkdir($path, 0777, true);

            $this->laravel['files']->put($request, $requestTemplate);
            $this->line('<info>'.$name.' Request file was created:</info> '.str_replace(base_path(), '', $request));
        }

    }
}