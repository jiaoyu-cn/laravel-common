# laravel-common
基于laravel的通用组件

[![image](https://img.shields.io/github/stars/jiaoyu-cn/laravel-tencent-vodlaravel-common)](https://github.com/jiaoyu-cn/laravel-common/stargazers)
[![image](https://img.shields.io/github/forks/jiaoyu-cn/laravel-common)](https://github.com/jiaoyu-cn/laravel-common/network/members)
[![image](https://img.shields.io/github/issues/jiaoyu-cn/laravel-common)](https://github.com/jiaoyu-cn/laravel-common/issues)

## 安装

```shell
composer require githen/laravel-common:~v1.0.0
```

## 功能说明

### 服务状态检测

调用方法
```php
// token => ***  为钉钉群机器人的access_token
// secret => ***  为钉钉群机器人的加签,不填则走关键词或IP
// 以上两个参数可复用`logging.channels.dingding.with`中配置，可直接在`listen`方法中第二个参数为`true`即可。

app('jiaoyu.common.process')->listen([
'process' => [
    'nginx' => 'Nginx服务', // key 为匹配关键词  name 为显示名称
    'artisan,shixun' => '多层过滤' // key中,为多层过滤 a,b  => grep a | grep b
    ],
 'token' => '***',
 'secret' => '***' 
]);
```

可以在任务调度中直接使用
```php
 $schedule->call(function (){
    app('jiaoyu.common.process')->listen(['process' => ['nginx' => 'nginx']], true);
 })->everyFiveMinutes()->runInBackground();
```

### 钉钉WebHook消息发送

钉钉手册地址：[官方手册](https://open.dingtalk.com/document/orgapp/custom-robot-access)

调用方法
```php
// token => ***  为钉钉群机器人的access_token
// secret => ***  为钉钉群机器人的加签,不填则走关键词或IP
// ->***()  ***为目前支持的方法，可参考接下文档
app('jiaoyu.common.dingding', ['token' => '*****','secret' => '****'])->***();
```

#### 发送消息信息

> text(string $message, bool $isAll = false, array $mobilds = [], array $userIds = [])

| 名称      | 必填 | 类型     | 备注                       |
|---------|----|:-------|:-------------------------|
| message | 是  | string | 发送的消息内容                  |
| isAll   | 否  | bool   | 是否@群内所有人                 |
| mobilds | 否  | array  | 通过手机号@群内成员，不在群内成功将被过滤    |
| userIds | 滞  | array  | 通过userid@群内成员，不在群内成功将被过滤 |


#### 发送链接消息

> link(string $title, string $text, string $url, string $picUrl = '')

| 名称     | 必填 | 类型 | 备注             |
|--------|----|--|----------------|
| title  | 是  | string | 消息标题           |
| text   | 是  | string | 消息内容,太长只显示部分内容 |
| url    | 是  | string | 点击跳转地址         |
| picUrl | 是  | string | 图片地址           |

#### 发送markdown类型

> markdown(string $title, string $text, bool $isAll = false, array $mobiles = [], array $userIds = [])

| 名称      | 必填 | 类型     | 备注             |
|---------|----|--------|----------------|
| title   | 是  | string | 消息标题           |
| text    | 是  | string | 消息内容 |
| isAll   | 否  | bool   | 是否@群内所有人                 |
| mobilds | 否  | array  | 通过手机号@群内成员，不在群内成功将被过滤    |
| userIds | 滞  | array  | 通过userid@群内成员，不在群内成功将被过滤 |

#### 整体/独立跳转ActionCard

> actionCard(string $title, string $text, array $btns, string $btnOrientation = '')

| 名称     | 必填 | 类型     | 备注                                                           |
|--------|----|--------|--------------------------------------------------------------|
| title  | 是  | string | 消息标题                                                         |
| text   | 是  | string | 消息内容,太长只显示部分内容                                               |
| btns   | 是  | array  | 点击按钮，结构 [['title' => xxx, actionURL=> 'http:// *****']...]   |
| btnOrientation | 否  | string | 按钮排序，0:按钮竖直排列 1：按钮横向排列                                       |


#### FeedCard类型消息

> feedCard(array $links)
> 
> $links的结构如下：

```php
[
    ['title' => '我是标题2'， 'messageURL' => 'https://链接地址2', 'picURL' => '图片的地址2'],
    ['title' => '我是标题1'， 'messageURL' => 'https://链接地址1', 'picURL' => '图片的地址1'],
    ...
]
```

| 名称               | 必填 | 类型     | 备注            |
|------------------|----|--------|---------------|
| links.title      | 是  | string | 单条信息文本。       |
| links.messageURL | 是  | string | 点击单条信息到跳转链接   |
| links.picURL     | 是  | string  | 单条信息后面图片的URL  |


### 日志发送到钉钉

在`config/logging.php`中添加新的配置项，以新建基于钉钉的日志通道（目前本地环境是关闭发送消息）。

可通过修改本地`config`中的`app.evn`来发送本地报警信息。

```php
        'dingding' => [
            'driver' => 'monolog',
            'level' => 'debug',
            'with' => ['token' => '钉钉机器人的access_token','secret' => '不填则走关键词或IP'],
            'handler' => \Githen\LaravelCommon\Log\Handler\DingdingLogHandler::class
        ],
```

若需要此配置生效，需要在`stack.channels`中添加`dingding`关键词


### 日志信息查看

此扩展中已完成了基本的日志及进程等功能，需要在`routes/web.php`中添加路由来访问此控制器。

```php
// log/{act} 中 log关键字可自定义，{act}不可修改
// 路由名可随意修改

Route::get('log/{act}', '\\Githen\\LaravelCommon\\App\\Controllers\\LogController@act')->name('log.act');
```
