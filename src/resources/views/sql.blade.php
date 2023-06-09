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

        .body {
            clear: both;
            overflow: hidden;
        }

        .left {
            float: left;
        }

        .right {
            float: right;
        }

        .info { /*设置整个表单样式*/
            border: 2px dotted #AAAAAA;
            padding: 1px 6px 1px 6px;
            margin: 10px;
            font: 14px Arial;
            display: block;
        }
        form { /*设置整个表单样式*/
            border: 2px dotted #AAAAAA;
            padding: 1px 6px 1px 6px;
            margin: 10px;
            font: 14px Arial;
        }

        input { /* 所有input标记 */
            color: #00008B;
        }

        input.txt { /* 文本框单独设置 */
            border: 1px inset #00008B;
            /*background-color: #ADD8E6;*/
        }

        input.btn { /* 按钮单独设置 */
            color: #00008B;
            /*background-color: #ADD8E6;*/
            border: 1px outset #00008B;
            padding: 1px 2px 1px 2px;
        }

        select { /*设置下拉列表框*/
            width: 80px;
            color: #00008B;
            /*background-color: #ADD8E6;*/
            border: 1px solid #00008B;
        }

        textarea { /*设置多行文本框*/
            color: #00008B;
            /*background-color: #ADD8E6;*/
            border: 1px inset #00008B;
        }

    </style>
</head>
<body>
<div class="menu">
    <div class="left">
        <a target="_blank" href="/{{str_replace('{act?}', 'mysql',$uri)}}">MySQL</a>
    </div>

    <div class="right">
        {{--        <a href="javascript:;" onclick="logShow(true)">全部展开</a>--}}
    </div>

</div>
<div class="body">
    <div class="info">
        @if($host) <p>服务器：{{$host}}</p> @endif
        @if($host) <p>数据库：{{$database}}</p> @endif
        @if($sql) <p>执行SQL：{!! str_replace("\n", "<br>", $sql) !!}</p> @endif

        <p>错误信息：{{session('error','暂无')}}</p>
        <p>执行完成：{{session('success','暂无')}}</p>
    </div>
    <form action="/{{str_replace('{act?}', 'mysql',$uri)}}" method="post" >
        @csrf
        <p>数据库连接：<br>
        <select name="config">
            @foreach($mysql as $config)
                <option value="{{$config}}">{{$config}}</option>
            @endforeach

        </select>
        </p>
        <p>SQL:<br><textarea name="sql" id="sql" cols=80 rows=30 class="txtarea"></textarea></p>
        <p><input type="submit" name="btnSubmit" id="btnSubmit" value="提交" class="btn"></p>

    </form>
</div>

</body>
</html>
