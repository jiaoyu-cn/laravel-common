<?php

namespace Githen\LaravelCommon\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    private function check(Request $request)
    {
        if ($key = $request->input('key')){
            // 服务器版本号
            $serverVersion = '';

            // 获取配置
            if(! $config = config('database.'.$key, [])){
                return response()->json(['code' => 1, 'message' => '配置不存在']);
            }

            // mysql
            if (isset($config['driver']) && $config['driver'] == 'mysql'){
                try {
                    $database = DB::connection(str_replace('connections.', '', $key))->getPdo();
                    $serverVersion =  $database->getAttribute(\PDO::ATTR_SERVER_VERSION);
                }catch (\Exception $e){
                    return response()->json(['code' => 1, 'message' => $e->getMessage()]);
                }
            }

            // mongo
            if (isset($config['driver']) && $config['driver'] == 'mongodb'){
                try {
                    $database = (new \Jenssegers\Mongodb\Connection($config))->getMongoDB()->command(['serverStatus' => true]);
                    $database->rewind();
                    $serverVersion = $database->current()->bsonSerialize()->version;
                }catch (\Exception $e){
                    return response()->json(['code' => 1, 'message' => $e->getMessage()]);
                }
            }

            // redis
            if (Str::startsWith($key, 'redis.')){
                try {
                    $redis = Redis::connection(str_replace('redis.', '', $key));
                    $server = $redis->command('info', ['server']);
                    $serverVersion = $server['redis_version'];
                }catch (\Exception $e){
                    return response()->json(['code' => 1, 'message' => $e->getMessage()]);
                }
            }
            return response()->json(['code' => 0, 'message' => $serverVersion]);

        }


        // 检测数据
        $check = [];
        $databaseConfig = config('database.connections',[]);

        // mysql , mongodb
        foreach ($databaseConfig as $key => $item){
            if (empty($item['driver']) || ! in_array($item['driver'], ['mysql', 'mongodb'])) continue;

            $check['connections.'.$key] = [
                'driver' => $item['driver'],
                'host' => $item['host'],
                'port' => $item['port'],
                'database' => $item['database'],
                'charset' => $item['charset']?? '',
            ];
        }

        // redis配置
        $redisConfig = config('database.redis',[]);
        foreach ($redisConfig as $key => $item){
            if (in_array($key, ['client', 'options'])) continue;
            $check['redis.'.$key] = [
                'driver' => 'redis',
                'host' => $item['host'],
                'port' => $item['port'],
                'database' => $item['database'],
            ];
        }

        return view()->file(__DIR__.'/../../resources/views/sql-check.blade.php', [
            'data' => $check,
            'uri' => Route::current()->uri(),
        ]);


        dd($check);



    }

}
