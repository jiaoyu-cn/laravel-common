<?php

namespace Githen\LaravelCommon\Providers;


use Illuminate\Support\ServiceProvider;

class DingdingProvider extends ServiceProvider
{

    // 请求地址
    private $url = 'https://oapi.dingtalk.com/robot/send?access_token=';

    // 请求token
    public $accessToken = '';

    /**
     * 服务注册
     * @return void
     */
    public function register()
    {
        dd(__CLASS__, __FUNCTION__);

    }

    /**
     * 服务启动
     * @return void
     */
    public function boot()
    {
        dd(__CLASS__, __FUNCTION__);

        // 注册签名方法
        $this->app->singleton('jiaoyu.common.dingding', function (){
            return $this;
        });
    }




    public function send($message)
    {
        if (!is_string($message)){
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->url.$this->accessToken);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }
}
