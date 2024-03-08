<html>
<head>
    <title>查看日志</title>
    <script src="https://www.eol.cn/e_js/index/2022/jquery.min.js" ignoreapd="false"></script>

    <style>
        a {
            text-decoration: none;
        }
        body {
            width: 1024px;
        }
        .menu a {
            border: #0b0d0f 1px solid;
            padding: 1px;
        }
        .body{
            clear: both;
            overflow: hidden;
        }
        .left{
            float: left;
        }
        .right {
            float: right;
        }
        dt{
            background-color: rgba(246, 162, 118, 0.76);
            margin: 5px;
            padding: 5px;
            width: 1008px;
            overflow: hidden;
        }
        dl dd{
            height: 30px;
            line-height: 30px;
            width: 100%;
        }

        dl dd:nth-child(even){
            background-color: #e3f2fd;
        }
        dl dd:hover{
            background-color: #b9d9f6;
        }
        .name {
            font-weight: bold;
            display: inline-block;
        }
        .option {
            display: inline-block;
        }

    </style>
</head>
<body>
<div class="menu">
    <div class="left">
        <a target="_blank" href="/{{str_replace('{act?}', 'ps',$uri)}}?key=artisan">查看进程</a>
        <a target="_blank" href="/{{str_replace('{act?}', 'ls',$uri)}}?key=">查看目录</a>
        <a target="_blank" href="/{{str_replace('{act?}', 'top',$uri)}}?key=">性能分析</a>
        <a target="_blank" href="/{{str_replace('{act?}', 'chown',$uri)}}?key=">修改权限</a>
        <a target="_blank" href="/{{str_replace('{act?}', 'clean',$uri)}}?key=7">清理目录</a>
        <a target="_blank" href="/{{str_replace('{act?}', 'phpinfo',$uri)}}">phpinfo</a>
    </div>

    <div class="right">
        <a href="javascript:;" onclick="logShow(true)">全部展开</a>
        <a href="javascript:;" onclick="logShow(false)">全部隐藏</a>
    </div>

</div>
<div class="body">
@foreach($files as $dir => $items)
    <dl>
        <dt onclick="logToggle()">
            <div class="left">目录：<strong>{{$dir?:'.'}}</strong> </div>
            <div class="right"> {{count($items)}} 个 </div>
        </dt>
        @foreach($items->sortByDesc(function ($v,$k){return $v->getRelativePathname();}) as $item )
            <dd>
                <div class="name">{{$item->getFilename()}}</div>
                <div class="option">
                    <a target="_blank" href="/{{str_replace('{act?}', 'download',$uri)}}?file={{$item->getRelativePathname()}}">查看</a>
                    <a target="_blank" href="/{{str_replace('{act?}', 'download',$uri)}}?file={{$item->getRelativePathname()}}&download=true">下载</a>
                    <a href="/{{str_replace('{act?}', 'download',$uri)}}?file={{$item->getRelativePathname()}}&remove=true">删除</a>
                </div>

            </dd>
        @endforeach
    </dl>
@endforeach
</div>

<script>
    // 所有节点展示、隐藏处理
    function logShow(isShow) {
        if(isShow){
            $('dd').show();
        }else{
            $('dd').hide();
        }
    }

    // dt中节点处理
    $('dt').on('click', function (){
        $(this).closest('dl').find('dd').toggle();
    });

    // 修改长度
    $('dl').each(function (i){
        let tmpWidth = 0;
        $(this).find('dd div.name').each(function (j) {
            tmpWidth = Math.max($(this).outerWidth(true), tmpWidth);
        });
        $(this).find('dd div.name').width((tmpWidth + 20) + 'px');
    });

    logShow(false);

</script>
</body>
</html>
