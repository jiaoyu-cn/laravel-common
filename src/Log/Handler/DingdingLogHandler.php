<?php

namespace Githen\LaravelCommon\Log\Handler;

use Monolog\Handler\AbstractProcessingHandler;
use Monolog\Logger;

class DingdingLogHandler extends AbstractProcessingHandler
{

    // 请求token
    public $accessToken = '';

    protected  $levels = [
        'DEBUG' => '中',
        'INFO' => '低',
        'NOTICE' => '低',
        'WARNING' => '中',
        'ERROR' => '高',
        'CRITICAL' => '低',
        'ALERT' => '低',
        'EMERGENCY' => '低',
    ];

    /**
     * 构造函数
     * @param $level
     * @param bool $bubble
     */
    public function __construct($level = Logger::DEBUG, bool $bubble = true, $access_token = '')
    {
        // access_token
        $this->accessToken = $access_token;

        parent::__construct($level, $bubble);
    }

    /**
     * 接收异常信息
     * @param $record
     * @return void
     */
    public function write($record):void
    {
        // 组装消息结构
        $content = [
            'markdown' => [
                'title' => '服务告警',
                'text' => '',
            ],
            'msgtype' => 'markdown',
            'at' => ['isAtAll' => true]
        ];

        $content['markdown']['text'] = '#### 【'.($this->levels[$record['level_name']]??'未知').'】服务告警 - '.config('app.env').PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '------'.PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '**项目名称:** '.config('settings.name').PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '**域名:** '.config('app.url').PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '**告警等级:** '.$record['level_name'].PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '**告警信息:** '.$record['message'].PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '**告警时间:** '.$record['datetime']->format('Y-m-d H:i:s').PHP_EOL.PHP_EOL;
        $content['markdown']['text'] .= '**操作者ID:** '.($record['context']['userId'] ?? '未登录').PHP_EOL.PHP_EOL;

        if (!empty($record['context']['exception'])){
            $exception = $record['context']['exception'];
            $content['markdown']['text'] .= '------'.PHP_EOL.PHP_EOL;
            $content['markdown']['text'] .= '**报错详情:** '.PHP_EOL.PHP_EOL;

            // 获取最近的异常
            $trace = $exception->getTrace();
            $trace = array_shift($trace);
            if (!empty($trace['args'][4])){
                $content['markdown']['text'] .= ' > **请求地址：** '.$trace['args'][4]['request']->fullurl().PHP_EOL.PHP_EOL;
            }
            $content['markdown']['text'] .= ' > **告警信息：** '.$exception->getMessage().PHP_EOL.PHP_EOL;
            $content['markdown']['text'] .= ' > **告警文件：** '.str_replace(base_path(''), '', $exception->getFile()).'('.$exception->getLine().')'.PHP_EOL.PHP_EOL;
        }
        dd($content['markdown']['text']);

//        $this->send($content);
    }




}