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

    /**
     * 日志文件处理
     * @param Request $request
     * @return void
     */
    private function download(Request $request)
    {
        // 检测文件是否存在
        $file = 'logs/'.$request->input('file', 'xxx***');
        if (! Storage::exists($file)){
            return response('文件不存在', 404);
        }

        // 删除日志
        if ($request->input('remove') == 'true'){
            if(! Storage::delete($file)){
                return response('文件删除失败', 404);
            }
            return redirect()->route(Route::current()->getName(), ['act' => 'index']);
        }

        // 下载日志
        if ($request->input('download') == 'true'){
            return response()->download(Storage::path($file));
        }

        // 查看日志
        echo '<pre>';
        $handle = fopen(Storage::path($file), 'r');
        while (feof($handle) === false) {
            echo fgets($handle);
        }
        fclose($handle);
    }

    /**
     * 查看进程
     * @param Request $request
     * @return string
     */
    private function ps(Request $request)
    {
        $key = $request->input('key', 'artisan');
        if (empty($key)){
            return response("输入错误", 500);
        }

        $cmd = 'ps aux | grep -v grep | grep -e START -e ' . $key;
        exec($cmd, $out);
        array_unshift($out, '执行命令：'.$cmd."<br>");

        return '<pre>'.implode('<br>', $out);
    }

    /**
     * 查看目录
     * @param Request $request
     * @return string
     */
    private function ls(Request $request)
    {
        $key = $request->input('key', '');
        $cmd = 'ls -alh ' . storage_path($key);
        exec($cmd, $out);
        array_unshift($out, '执行命令：'.$cmd."<br>");

        return '<pre>'.implode('<br>', $out);
    }

    /**
     * 修改文件/目录权限
     * @param Request $request
     * @return string
     */
    private function chown(Request $request)
    {
        if(! $key = $request->input('key', '')){
            return response('请输入要修改的权限目录', 500);
        }
        $dir = storage_path($key);

        if (!is_file($dir) && !is_dir($dir)){
            return response('文件/目录不存在', 500);
        }

        $cmd = 'chown -R 0770 ' . $dir;
        exec($cmd, $out);
        array_unshift($out, '执行命令：'.$cmd."<br>");

        return '<pre>'.implode('<br>', $out);
    }

    /**
     * 清理日志文件
     * @param Request $request
     * @return string
     */
    private function clean(Request $request)
    {
        $key = (int)$request->input('key', 7);

        if ($key <= 3){
            return response('日志保留天数最小为3天', 500);
        }

        // 清理文件及目录
        $cmd = 'find '.storage_path('logs') .' -type f -ctime +'.$key.' | grep -e "\.log" -e "\.sql"';
        exec($cmd, $out);

        if (count($out) == 0){
            return redirect()->route(Route::current()->getName(), ['act' => 'index']);
        }

        // 执行文件
        foreach ($out as $file){
            @unlink($file);
        }

        array_unshift($out, '执行命令：'.$cmd."<br>", '删除文件如下：<br>');

        return '<pre>'.implode('<br>', $out);
    }

    /**
     * 查看phpinfo
     * @return void
     */
    public function phpinfo()
    {
        phpinfo();
    }
}
