<?php

namespace Githen\LaravelCommon\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * SQL执行
 */
class SqlController extends  Controller
{
    /**
     * 统一出入口
     * @param Request $request
     * @param $act
     * @return mixed
     */
    public function act(Request $request, $act='mysql')
    {
        return $this->$act($request);
    }

    /**
     * 日志列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    private function mysql(Request $request)
    {
        if ($request->input('btnSubmit')){
            if (!$request->input('config') || !$request->input('sql')){
                // 参数缺失
                $request->session()->flash('error', '未获取到参数信息：config|sql');
            }elseif (! config('database.connections.'.$request->input('config'))){
                // 获取mysql配置
                $request->session()->flash('error', '配置信息不存在：'.$request->input('config'));
            }else{
                $result = DB::connection($request->input('config'))->statement($request->input('sql'));
                $request->session()->flash('success', $result);
            }
        }

        // 获取sql配置
        $databaseConfig = config('database.connections',[]);
        $databaseConfig = collect($databaseConfig)->where('driver', '=','mysql')->keys();

        return view()->file(__DIR__.'/../../resources/views/sql.blade.php', [
            'host' => config('database.connections.'.$request->input('config', '').'.host', ''),
            'database' => config('database.connections.'.$request->input('config'. '').'.database', ''),
            'sql' => $request->input('sql', ''),
            'mysql' => $databaseConfig,
            'uri' => Route::current()->uri(),
        ]);
    }

}
