<?php

namespace Githen\LaravelCommon\Providers;


use Illuminate\Support\ServiceProvider;

class DingdingProvider extends ServiceProvider
{

    // 请求地址
    private $url = 'https://oapi.dingtalk.com/robot/send?access_token=';

    // 请求token
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
        $this->app->singleton('jiaoyu.common.dingding', function ($app, $params = []){
            // 设置token值
            $this->token = $params['token'] ?? '';
            $this->secret = $params['secret'] ?? '';
            return $this;
        });
    }

    /**
     * 发送消息信息
     * @param string $message 发送消息
     * @param bool $isAll 是否@所有人
     * @param array $mobilds 用手机号@群内人
     * @param array $userIds 用userid@群内人
     * @return array
     */
    public function text(string $message, bool $isAll = false, array $mobilds = [], array $userIds = [])
    {
        $content = [
            'msgtype' => 'text',
            'text' => ['content' => $message],
            'at' => [
                'atMobiles' => $mobilds,
                'atUserIds' => $userIds,
                'isAtAll' => $isAll
            ]
        ];

        return $this->send($content);
    }

    /**
     * 发送链接消息
     * @param string $title 消息标题
     * @param string $text 消息内容，太长只显示部署
     * @param string $url  点击跳转地址
     * @param string $picUrl  图片地址
     * @return array
     */
    public function link(string $title, string $text, string $url, string $picUrl = '')
    {
        $content = [
            'msgtype' => 'link',
            'link' => [
                'text' => $text,
                'title' => $title,
                'picUrl' => $picUrl,
                'messageUrl' => $url,
            ]
        ];

        return $this->send($content);

    }

    /**
     * 发送markdown类型
     * @param string $title
     * @param string $text
     * @param bool $isAll
     * @param array $mobiles
     * @param array $userIds
     * @return array
     */
    public function markdown(string $title, string $text, bool $isAll = false, array $mobiles = [], array $userIds = [])
    {
        $content = [
            'msgtype' => 'markdown',
            'markdown' => [
                'title' => $title,
                'text' => $text,
            ],
            'at' => [
                'atMobiles' => $mobiles,
                'atUserIds' => $userIds,
                'isAtAll' => $isAll
            ],
        ];

        return $this->send($content);
    }

    /**
     * 整体/独立跳转ActionCard
     * @param string $title
     * @param string $text
     * @param array $btns [['title' => xxx, actionURL=> 'http:// *****']...]
     * @param string $btnOrientation 0:按钮竖直排列 1：按钮横向排列
     * @return array
     */
    public function actionCard(string $title, string $text, array $btns, string $btnOrientation = '')
    {
        $content = [
            'msgtype' => 'actionCard',
            'actionCard' => [
                'title' => $title,
                'text' => $text,
                'btnOrientation' => $btnOrientation,
            ]
        ];

        // 对 $btns参数进行检测
        foreach ($btns as $item){
            if (!isset($item['title'])){
                return ['code' => 1, 'message' => '按钮缺少title参数'];
            }
            if (!isset($item['actionURL'])){
                return ['code' => 1, 'message' => '按钮缺少actionURL参数'];
            }
        }

        // 整体跳转
        if (count($btns) == 1){
            $btns = array_shift($btns);
            $content['actionCard']['singleTitle'] = $btns['title'];
            $content['actionCard']['singleURL'] = $btns['actionURL'];
        }

        // 独立跳转
        if (count($btns) > 1){
            $content['actionCard']['btns'] = $btns;
        }

        return $this->send($content);
    }

    public function feedCard(array $links)
    {

        // 对 $links 参数进行检测
        foreach ($links as $item){
            if (!isset($item['title'])){
                return ['code' => 1, 'message' => '缺少title参数'];
            }
            if (!isset($item['messageURL'])){
                return ['code' => 1, 'message' => '缺少messageURL参数'];
            }
            if (!isset($item['picURL'])){
                return ['code' => 1, 'message' => '缺少picURL参数'];
            }
        }

        $content = [
            'msgtype' => 'feedCard',
            'feedCard' => [
                'links' => $links
            ]
        ];

        return $this->send($content);
    }


    public function send($message)
    {
        if (!is_string($message)){
            $message = json_encode($message, JSON_UNESCAPED_UNICODE);
        }

        $url = $this->url.$this->token;
        // 加签
        if ($this->secret){
            $time = (int)round(microtime(true) * 1000);
            $sign = $time . "\n" . $this->secret;
            $sign = hash_hmac('SHA256',$sign, $this->secret,  true);

            $sign = base64_encode( $sign);
            $url .= '&timestamp='.$time.'&sign='.$sign;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array ('Content-Type: application/json;charset=utf-8'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $message);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        // 线下环境不用开启curl证书验证, 未调通情况可尝试添加该代码
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYHOST, 0);
        // curl_setopt ($ch, CURLOPT_SSL_VERIFYPEER, 0);
        $data = curl_exec($ch);
        $data = json_decode($data, true);
        curl_close($ch);
        return ['code' => $data['errcode'], 'message' => $data['errmsg']];
    }
}
