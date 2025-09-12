<?php

namespace Githen\LaravelCommon\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/**
 * 日志查看
 */
class LogController extends Controller
{

    /**
     * 统一出入口
     * @param Request $request
     * @param $act
     * @return mixed
     */
    public function act(Request $request, $act = 'index')
    {
        return $this->$act($request);
    }

    /**
     * 日志列表
     * @param Request $request
     * @return \Illuminate\Contracts\View\View
     */
    private function index(Request $request)
    {
        $logFiles = File::allFiles(storage_path('logs/'));

        $logFiles = collect($logFiles)->mapToGroups(function ($item, $key) {
            return [$item->getRelativePath() => $item];
        })->sortKeys();

        $curRoute = Route::current();

        return view()->file(__DIR__ . '/../../resources/views/log.blade.php', [
            'files' => $logFiles,
            'uri' => $curRoute->uri(),
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
        $file = storage_path('logs/' . $request->input('file', 'xxx***'));
        if (!is_file($file)) {
            return response('文件不存在', 404);
        }

        // 删除日志
        if ($request->input('remove') == 'true') {
            if (!unlink($file)) {
                return response('文件删除失败', 404);
            }
            return redirect()->route(Route::current()->getName(), ['act' => 'index']);
        }

        // 下载日志
        if ($request->input('download') == 'true') {
            return response()->download($file);
        }

        // 查看日志
        $readFile = function () use ($file) {
            $handle = fopen($file, 'r');
            while (feof($handle) === false) {
                echo fread($handle, 1024 * 1024);;
                ob_flush();
                flush();
            }
            fclose($handle);
        };

        return response()->stream($readFile, 200, [
            "Content-Type" => "text/plain; charset=UTF-8",
        ]);
    }

    /**
     * 查看进程
     * @param Request $request
     * @return string
     */
    private function ps(Request $request)
    {
        $key = $request->input('key', 'artisan');
        if (empty($key)) {
            return response("输入错误", 500);
        }

        $cmd = 'ps aux | grep -v grep | grep -e START -e ' . $key;
        exec($cmd, $out);
        array_unshift($out, '执行命令：' . $cmd . "<br>");

        return '<pre>' . implode('<br>', $out);
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
        array_unshift($out, '执行命令：' . $cmd . "<br>");

        return '<pre>' . implode('<br>', $out);
    }

    /**
     * 性能分析
     * @param Request $request
     * @return string
     */
    private function top(Request $request)
    {
        $key = $request->input('key', '');
        $systemInfo = php_uname('s');
        if (strpos($systemInfo, 'Darwin') !== false) {
            $cmd = 'top -l 1 ' . $key;
        } else {
            $cmd = 'top -bcn 1 -w 600' . $key;
        }
        exec($cmd, $out);
        array_unshift($out, '执行命令：' . $cmd . "<br>");

        return '<pre>' . implode('<br>', $out);
    }

    /**
     * 查看磁盘
     * @param Request $request
     * @return string
     */
    private function df(Request $request)
    {
        $cmd = 'df -h ';

        exec($cmd, $out);
        array_unshift($out, '执行命令：' . $cmd . "<br>");

        return '<pre>' . implode('<br>', $out);
    }

    /**
     * 修改文件/目录权限
     * @param Request $request
     * @return string
     */
    private function chmod(Request $request)
    {
        if (!$key = $request->input('key', '')) {
            return response('请输入要修改的权限目录', 500);
        }
        $dir = storage_path($key);

        if (!is_file($dir) && !is_dir($dir)) {
            return response('文件/目录不存在', 500);
        }

        $cmd = 'chmod -R 0770 ' . $dir;
        exec($cmd, $out);
        array_unshift($out, '执行命令：' . $cmd . "<br>");

        return '<pre>' . implode('<br>', $out);
    }

    /**
     * 清理日志文件
     * @param Request $request
     * @return string
     */
    private function clean(Request $request)
    {
        $key = (int)$request->input('key', 7);

        if ($key <= 3) {
            return response('日志保留天数最小为3天', 500);
        }

        // 清理文件及目录
        $cmd = 'find ' . storage_path('logs/') . ' -type f -ctime +' . $key . ' | grep -e "\.log" -e "\.sql"';
        exec($cmd, $out);

        if (count($out) == 0) {
            return redirect()->route(Route::current()->getName(), ['act' => 'index']);
        }

        // 执行文件
        foreach ($out as $file) {
            @unlink($file);
        }

        array_unshift($out, '执行命令：' . $cmd . "<br>", '删除文件如下：<br>');

        return response('<pre>' . implode('<br>', $out));
    }

    /**
     * 查看phpinfo
     * @return void
     */
    public function phpinfo()
    {
        phpinfo();
    }


    /**
     * opcache操作
     * @return \Illuminate\Contracts\View\View
     */
    public function opcache(Request $request)
    {
        if (!function_exists('opcache_get_configuration')) {
            return "opcache扩展未安装";
        }
        // 清除单个文件
        if ($request->input('type') == 'clear_file') {
            if ($file = $request->input('file')) {
                if (opcache_invalidate($file, true)) {
                    return response()->json([
                        'message' => '成功废除脚本缓存',
                        'refresh' => false
                    ]);
                }
                return response()->json([
                    'message' => '废除脚本缓存失败',
                    'refresh' => false
                ]);
            }
        }

        // 检测单个脚本
        if ($request->input('type') == 'check_file') {
            if ($file = $request->input('file')) {
                if (opcache_is_script_cached($file)) {
                    return response()->json([
                        'message' => '脚本已被缓存',
                        'refresh' => false
                    ]);
                }
                return response()->json([
                    'message' => '脚本未被缓存',
                    'refresh' => false
                ]);
            }
        }

        // 重置所有缓存
        if ($request->input('type') == 'clear_all') {
            if (opcache_reset()) {
                return response()->json([
                    'message' => '成功重置内存中所有缓存',
                    'refresh' => true
                ]);
            }
            return response()->json([
                'message' => '重置内存中所有缓存失败',
                'refresh' => false
            ]);
        }

        $curRoute = Route::current();
        return view()->file(__DIR__ . '/../../resources/views/opcache.blade.php', [
            'opcache_status' => opcache_get_status(),
            'uri' => $curRoute->uri(),
        ]);
    }

    public function check(Request $request)
    {
        $data = [];
        $file = 'app/data/schedule_check.txt';
        if (!Storage::exists($file)) {
            Storage::put($file, time(), 'public');
            $data = [
                'is_ok' => true,
                'checked_at' => date('Y-m-d H:i:s'),
                'last_run_at' => date('Y-m-d H:i:s'),
            ];
        } else {
            $lastModified = Storage::get($file);
            $diff = time() - $lastModified;
            $isOk = $diff > 360 ? false : true;

            $data = [
                'is_ok' => $isOk,
                'checked_at' => date('Y-m-d H:i:s'),
                'last_run_at' => date('Y-m-d H:i:s', $lastModified),
            ];
        }
        return response()->json(['code' => '0000', 'message' => 'ok', 'data' => $data]);
    }
}
