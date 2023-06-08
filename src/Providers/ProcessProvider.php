<?php

namespace Githen\LaravelCommon\Providers;


use Illuminate\Support\ServiceProvider;

class ProcessProvider extends ServiceProvider
{

    // 钉钉请求token
    public $token = '';
    public $secret = '';

    /**
     * 服务注册
     * @return void
     */
    public function register()
    {
    }

    /**
     * 服务启动
     * @return void
     */
    public function boot()
    {
        // 注册签名方法
        $this->app->singleton('jiaoyu.common.process', function (){
            return $this;
        });
    }

    /**
     * 监听进程
     * @param array $params
     * @param bool $isLogConfig
     * @return array|void
     */
    public function listen(array $params, bool $isLogConfig = false)
    {
        if (! $params){
            return $this->message(1, '参数配置错误');
        }

        // 钉钉配置
        if ($isLogConfig){
            $this->token = config('logging.channels.dingding.with.token');
            $this->secret = config('logging.channels.dingding.with.secret');
        }else{
            $this->token = $params['token'] ?? '';
            $this->secret = $params['secret'] ?? '';

        }

        if (! $this->token){
            return $this->message(1, 'token参数缺失');
        }
        if (empty($params['process'])){
            return $this->message(1, 'process参数缺失');
        }

        // 查询进程
        foreach ($params['process'] as $key => $name){
            if (! $key ) continue;

            $this->check($key,$name);
        }

    }

    /**
     * 检测进程
     * @param $key
     * @param $name
     * @return array
     */
    private function check($key, $name)
    {
        $tmpKey = explode(',', $key);
        $tmpKey = "grep '". implode("' | grep '", $tmpKey) ."'";

        exec('ps aux | grep -v grep | '.$tmpKey, $output);

        // 存活
        if (count($output) > 0){
            return $this->message(0, $name .' 进程存活');
        }

        $text = '#### 【高】服务宕机 - '.config('app.env').PHP_EOL.PHP_EOL;
        $text .= '------'.PHP_EOL.PHP_EOL;
        $text .= '**项目名称:** '.config('settings.name').PHP_EOL.PHP_EOL;
        $text .= '**域名:** '.config('app.url').PHP_EOL.PHP_EOL;
        $text .= '**告警信息:** '.$name.' 已宕机'.PHP_EOL.PHP_EOL;
        $text .= '**关键词:**  '.$key.PHP_EOL.PHP_EOL;
        $text .= '**告警时间:** '.date('Y-m-d H:i:s').PHP_EOL.PHP_EOL;

        // 发送到钉钉
        return app('jiaoyu.common.dingding', ['token' => $this->token, 'secret' => $this->secret])->markdown('进程宕机', $text, true);
    }

    /**
     * 格式化输出
     * @param $code
     * @param $message
     * @return array
     */
    private function message($code, $message)
    {
        return ['code' => $code, 'message' => $message];
    }

}
