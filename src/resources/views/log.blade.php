<html>
<head>
    <title>查看日志</title>
    <script src="https://www.eol.cn/e_js/index/2022/jquery.min.js" ignoreapd="false"></script>

    <style>
        dt{
            background-color: #ffb07c;
            margin: 5px;
            padding: 5px;
        }
        dl dd{
            height: 30px;
            line-height: 30px;
            width: 100%;
        }

        dl dd:nth-child(even){
            background-color: #e3f2fd;
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
<div>
    <a href="javascript:;" onclick="logShow(true)">全部展开</a>
    <a href="javascript:;" onclick="logShow(false)">全部隐藏</a>
</div>
@foreach($files as $dir => $items)
    <dl>
        <dt onclick="logToggle()">目录：<strong>{{$dir?:'.'}}</strong>  共计: {{count($items)}} 个</dt>
        @foreach($items as $item )
            <dd>
                <div class="name">{{$item->getFilename()}}</div>
                <div class="option">
                    <a href="javascript:;">查看</a>
                    <a href="javascript:;">下载</a>
                    <a href="javascript:;">删除</a>
                </div>

            </dd>
        @endforeach
    </dl>
@endforeach

<script>
    function logShow(isShow) {

        if(isShow){
            $('dd').show();
        }else{
            $('dd').hide();
        }
    }

    $('dt').on('click', function (){

        $(this).closest('dl').find('dd').toggle('slow');
    });

    // 修改长度
    $('dl').each(function (i){
        let tmpWidth = 0;
        $(this).find('dd div.name').each(function (j) {
            tmpWidth = Math.max($(this).outerWidth(true), tmpWidth);
        });
        $(this).find('dd div.name').width((tmpWidth + 20) + 'px');
    });
</script>
</body>
</html>
