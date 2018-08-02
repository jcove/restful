<?php
/**
 * Author: XiaoFei Zhai
 * Date: 2018/7/5
 * Time: 10:55
 */

namespace Jcove\Restful;


use Illuminate\Contracts\Support\Arrayable;

class Result implements Arrayable
{
    private $code;
    private $msg;
    private $data;

    /**
     * Result constructor.
     * @param $code
     * @param $msg
     * @param $data
     */
    public function __construct($code, $msg, $data=[])
    {
        $this->code = $code;
        $this->msg = $msg;
        $this->data = $data;
    }


    /**
     * @return mixed
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param mixed $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return mixed
     */
    public function getMsg()
    {
        return $this->msg;
    }

    /**
     * @param mixed $msg
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }


    /**
     *
     */
    public function toArray(){
        return [
            'code'      =>  $this->code,
            'msg'       =>  $this->msg,
            'data'      =>  $this->data
        ];
    }

}