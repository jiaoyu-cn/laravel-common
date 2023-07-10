<html>
<head>
    <title>数据库连接测试</title>
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
            width: fit-content; /* 或者使用 width: max-content; */
            float: left;

        }

        .info .success {
            color: #0B7500;
        }
        .info .error {
            color: #9D1E15;
        }
        .info p {
            display: block;
            white-space: normal; /* 允许文字换行 */
            word-wrap: break-word; /* 超出宽度部分自动换行 */
            max-width: 250px; /* 设置最大宽度 */
        }

    </style>
</head>
<body>
<div class="menu">
    <div class="left">
        <a target="_blank" href="/{{str_replace('{act?}', 'mysql',$uri)}}">MySQL</a>
        <a target="_blank" href="/{{str_replace('{act?}', 'check',$uri)}}">连接测试</a>
    </div>

    <div class="right">
        {{--        <a href="javascript:;" onclick="logShow(true)">全部展开</a>--}}
    </div>

</div>
<div class="body">
    @foreach($data as $key => $item)
    <div class="info" id="{{str_replace('.', '--', $key)}}">

        <p>配置KEY: {{str_replace(['connections.'], '', $key)}}</p>
        @if(isset($item['host'])) <p>服务器：{{$item['host']}}</p> @endif
        @if(isset($item['driver'])) <p>驱动类型：{{$item['driver']}}</p> @endif
        @if(isset($item['host'])) <p>服务器：{{$item['host']}}</p> @endif
        @if(isset($item['port'])) <p>端口：{{$item['port']}}</p> @endif
        @if(isset($item['database'])) <p>数据库：{{$item['database']}}</p> @endif
        @if(!empty($item['charset'])) <p>字符集：{{$item['charset']}}</p> @endif
        <p class="status">连接状态：未知</p>
        <script>
            $(function () {
                $.ajax({
                    type: 'GET',
                    url: "/{{str_replace('{act?}', 'check',$uri)}}?key={{$key}}",
                    async: false,
                    timeout: 6000,
                    success: function (data) {
                        if(data.code == 0){
                            $("#{{str_replace('.', '--', $key)}} p.status").addClass('success').html('连接状态：成功');
                            $("#{{str_replace('.', '--', $key)}} p.status").after('<p class="success">服务器版本：'+data.message+'</p>');
                            return;
                        }

                        $("#{{str_replace('.', '--', $key)}} p.status").addClass('error').html('连接状态：失败');
                        $("#{{str_replace('.', '--', $key)}} p.status").after('<p class="error">失败原因：'+data.message+'</p>');

                    },
                    error: function (XMLHttpRequest, textStatus, errorThrown) {
                        $("#{{str_replace('.', '--', $key)}} p.status").addClass('error').html('连接状态：失败');
                        $("#{{str_replace('.', '--', $key)}} p.status").after('<p class="error">失败原因：'+errorThrown+'</p>');
                    }
                });

            });

        </script>
    </div>
    @endforeach

</div>

</body>
</html>
