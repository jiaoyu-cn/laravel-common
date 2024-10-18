<html>
<head>
    <title>查看opcache缓存文件</title>
    <script src="https://www.eol.cn/e_js/index/2022/jquery.min.js" ignoreapd="false"></script>

    <style>
        a {
            text-decoration: none;
        }

        /*body {*/
        /*    width: 1024px;*/
        /*}*/

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

        table {
            border-collapse: collapse;
        }
        table tr:hover {
            background-color: #f5f5f5; /* 更改鼠标悬停时的背景色 */
            cursor: pointer; /* 更改鼠标形状 */
        }
        table td,th {
            border-bottom: 1px solid #000;
            padding: 3;
        }
        table td:not(:last-child){
            border-right: 1px solid #000;
        }
    </style>
</head>
<body>
<div class="menu">
    <div class="left">
        <a target="_blank" href="/{{str_replace('{act?}', 'phpinfo#module_zend+opcache',$uri)}}">opcache配置</a>
        <a target="_blank" href="javascript:;"  onclick="opcache('/{{str_replace('{act?}', 'opcache?type=check_file&file=',$uri)}}'+encodeURIComponent(prompt('请输入要检测文件绝对路径')));return false;">检测是否被缓存</a>
        <a target="_blank" href="javascript:;"  onclick="opcache('/{{str_replace('{act?}', 'opcache?type=clear_file&file=',$uri)}}'+encodeURIComponent(prompt('请输入要重置文件绝对路径')));return false;">重置指定文件</a>
        <a target="_blank" href="javascript:;"  onclick="if(confirm('确认重置所有opcache缓存？'))opcache('/{{str_replace('{act?}', 'opcache?type=clear_all',$uri)}}');return false;">重置所有</a>
    </div>

    <div class="right">
        {{--        <a href="javascript:;" onclick="logShow(true)">全部展开</a>--}}
    </div>

</div>
<div class="body">
    <h3>opcache内存中所有缓存的脚本</h3>
    <table>
        <thead>
        <tr>
            <th>操作</th>
            <th>修改时间</th>
            <th>上次使用时间</th>
            <th>命中/次</th>
            <th>内存/KB</th>
            <th>文件</th>
        </tr>
        </thead>
        <tbody>
        @foreach($opcache_status['scripts']??[] as $k => $v)
            <tr>
                <td><a href="javascript:;" onclick="opcache('/{{str_replace('{act?}', 'opcache?type=clear_file&file='.urlencode($v['full_path']),$uri)}}')">清除缓存</a></td>
                <td>{{!empty($v['timestamp'])?date("Y-m-d H:i:s", $v['timestamp']):'-'}}</td>
                <td>{{!empty($v['last_used_timestamp'])?date("Y-m-d H:i:s", $v['last_used_timestamp']):'-'}}</td>
                <td>{{$v['hits']}}</td>
                <td>{{round($v['memory_consumption']/1024, 2)}}</td>
                <td title="{{$k}}" style=""><code>{{$k}}</code></td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
<script>
    function opcache(url){
        $.ajax({
            type: 'GET',
            url: url,
            async: false,
            timeout: 6000,
            success: function (data) {
                alert(data.message);
                if(data.refresh){
                    window.location.reload()
                }
            },
            error: function (XMLHttpRequest, textStatus, errorThrown) {
                console.log(XMLHttpRequest, textStatus, errorThrown)
            }
        });

    }
</script>
</body>
</html>
