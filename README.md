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

### 执行SQL

添加路由
```php
Route::match(['get', 'post'], 'sql/{act?}', '\\Githen\\LaravelCommon\\App\\Controllers\\SqlController@act')->name('sql.act');
```
1. 可在路由配置中添加以下配置，完成SQL操作的控制器注入，并可通过访问` http://host/sql/` 来执行。
2. 可查看当前配置的数据库连通情况（暂只支持 `mysql`,`mongo`,`redis`），通过访问` http://host/sql/check` 来查看

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

Route::get('log/{act?}', '\\Githen\\LaravelCommon\\App\\Controllers\\LogController@act')->name('log.act');
```
### SSL证书到期检测

注：以定时任务每天进行触发，通知规则为离过期还有7天以内时，每天发送钉钉通知；若7-30天时则每周一发送通知。

调用方法
```php
// $param['dir'] => '/...'  需要检测的证书的绝对地址
// $param['token'] => ***  为钉钉群机器人的access_token,不填走默认配置：logging.channels.dingding.with.token
// $param['secret'] => ***  为钉钉群机器人的加签,不填则走关键词或IP
// $isDing => true/false  快到期时是否发送钉钉群通知，true发送，false不发送
app('jiaoyu.common.process')->ssl(['dir' => $dir, 'token' => $token, 'secret' => $secret], $isDing);
```
### PHPWord生成docx文档

调整了PHPWord中`TemplateProcessor`生成图的一些样式问题，[官方文档](https://phpoffice.github.io/PHPWord/index.html)测试代码如下：
```php
use Githen\LaravelCommon\Extend\PHPWord\TemplateProcessor;

// $docFile 为模板docx文件
$templateProcessor = new TemplateProcessor($docFile);
//替换单个变量
$templateProcessor->setValue('name', 'aaa');
// 批量替换变量
$templateProcessor->setValues([
    // 汇总数据
    'name' => '周报', // 名称
    'dateLoop' => date('Y-m-d',strtotime('-7 days')).'~'.date('Y-m-d'), // 巡检周期
]);

// 图表测试数据
$categories = array('网站', '微信', '微博', '百家号', '头条');
// 文章总数
$series1 = array(10, 30, 20, 50, 40);
// 风险数
$series2 = array(1, 13, 12, 5, 34);

// 柱状图
$chart = new Chart('column',$categories, $series1,null,'文章总数'); // 创建时需要给默认数据
$chart->getStyle()
    ->setWidth(Converter::inchToEmu(6)) // 设置图宽
    ->setHeight(Converter::inchToEmu(3)) // 设置图高
    ->setShowAxisLabels(true) // 显示坐标标签
    ->setColors(['00B0F0','92D050']) // 设置颜色，循环展示
    ->setShowLegend(true) // 显示图例
    ->setLegendPosition('b') // 图例位置 
    ->setShowGridY(true) // 展示Y轴参考线
    ->setDataLabelOptions(['showCatName' => false]); // 柱状图不展示名称
$chart->addSeries($categories, $series2, '风险文章数'); // 添加第二组数据
$templateProcessor->setChart('articleChartBar', $chart); // 将图添加到文档中

// 饼图，只支持一组数据
$chart1 = new Chart('pie',$categories, $series1,null,'文章总数');
$chart1->getStyle()
    ->setWidth(Converter::inchToEmu(6))
    ->setHeight(Converter::inchToEmu(3))
    ->setShowAxisLabels(true)
    ->setColors(['00B0F0','92D050','ff0000','00ff00','0000ff'])
    ->setShowLegend(true)
    ->setLegendPosition('b')
    ->setShowGridY(true)
    ->setDataLabelOptions(['showCatName' => false]);
$templateProcessor->setChart('articleChartPie', $chart1);


// 表格渲染
$testData = [
    ['tableType' => '网站', 'point' => '12','articleT' => 10],
    ['tableType' => '网站', 'point' => '12','articleT' => 10],
    ['tableType' => '网站', 'point' => '12','articleT' => 10],
    ['tableType' => '网站', 'point' => '12','articleT' => 10],
    ['tableType' => '', 'point' => '总计','articleT' => 10],
];
$templateProcessor->cloneRowAndSetValues('tableType', $testData);

// 区块渲染，PHPWord中块渲染只支持文本
$chineseNumbers = ['','一', '二', '三', '四', '五', '六', '七', '八', '九'];
$testData =[
    '一类' => ['一类1' => ['risk' => 130, 'suspect' => 20], '一类2' => ['risk' => 30, 'suspect' => 20]],
    '二类' => ['二类1' => ['risk' => 230, 'suspect' => 20], '二类2' => ['risk' => 40, 'suspect' => 20]],
    '三类' => ['三类1' => ['risk' => 330, 'suspect' => 20], '三类2' => ['risk' => 30, 'suspect' => 20]],
];
// 不存在图，只有纯文本，未处理数据，只关注参数
$templateProcessor->cloneBlock('riskDetail',count($testData), true, false, $testData);


//存在图则需要先生成重复的块代码，再执行替换
$templateProcessor->cloneBlock('riskDetail',count($testData), true, true);
$detailData = [];
$maxText = [];

$i = 1;
$max = 0;
foreach ($testData as $name => $detail){
    $detailText = [];
    foreach ($detail as $type => $tmp){
        $detailText[] = $type."风险{$tmp['risk']}个，包括疑似风险{$tmp['suspect']}个";
        if ($max <= $tmp['risk']){
            $max = $tmp['risk'];
            if($max == $tmp) $maxText[] = $type;
            else $maxText = [$type];
        }
    }

    $templateProcessor->setValue('riskIndex#'.$i, $chineseNumbers[$i]);
    $templateProcessor->setValue('riskName#'.$i, $name);
    $templateProcessor->setValue('riskCount#'.$i, count($detail));
    $templateProcessor->setValue('riskMax#'.$i, implode("、",$maxText));
    $templateProcessor->setValue('riskText#'.$i, implode(";",$detailText));
    $templateProcessor->setChart('riskChart#'.$i, $chart);

    $i++;
}

//  保存文件
$templateProcessor->saveAs( storage_path('sample.docx'));


```

### 清除Opcache缓存
1. 添加清理路由
```php
//路由名称及协议不可变
// 路由名称只可以为opcache.clear或 api.opcache.clear
Route::get('opcache/clear', '\\Githen\\LaravelCommon\\App\\Controllers\\OpcacheController@clear')->name('opcache.clear');
```
2.注册命令

```php
// laravel6
// 在app/Console/Kernel.php的$commands中添加引用
use Githen\LaravelCommon\Commands\OpcacheClear;
protected $commands = [
    OpcacheClear::class,
];

// laravel11
// 在bootstrap/app.php文件的withCommands中添加引用
use Githen\LaravelCommon\Commands\OpcacheClear;

 ->withCommands([
        OpcacheClear::class,
    ])

```
3. 项目根目录添加`opcache.sh`脚本，以执行清理操作

```shell
#!/bin/bash
cd "$(dirname "$0")"

nohup bash -c "sleep 10; php artisan jiaoyu:opcache-clear" >> storage/logs/opcache.log 2>&1 &
echo $! > storage/logs/opcache.pid
disown
```

3. 部署时执行
在composer.json中添加执行脚本,Host为本项目的名称，`http://127.0.0.1` 域名不可替换,协议根据实际情况调整
```json
    "scripts": {
        "post-autoload-dump": [
            "Illuminate\\Foundation\\ComposerScripts::postAutoloadDump",
            "@php artisan package:discover --ansi",
            "@bash opcache.sh"
        ]
    }
```
