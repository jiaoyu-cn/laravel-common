<?php

namespace Githen\LaravelCommon\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * 日志查看
 */
class OpcacheController extends Controller
{

    /**
     * 清除opcache缓存
     * @param Request $request
     * @param $act
     * @return mixed
     */
    public function clear(Request $request)
    {
        //检测ip，只允许本地访问
        $date = date("Y-m-d H:i:s");
        if ($request->ip() != '127.0.0.1') {
            return response($date.'只有本地才可以访问');
        }

        if (!function_exists('opcache_get_configuration')) {
            return response($date."opcache扩展未安装");
        }
        if (opcache_reset()) {
            return response($date."成功重置内存中opcache所有缓存");
        }
        return response($date."重置内存中opcache所有缓存失败");
    }
}
