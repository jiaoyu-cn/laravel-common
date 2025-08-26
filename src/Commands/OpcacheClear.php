<?php

namespace Githen\LaravelCommon\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;

class OpcacheClear extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jiaoyu:opcache-clear';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '清除opcache缓存';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $url = route('opcache.clear');
        }catch (\Exception $exception){
            $this->warn($exception->getMessage());
            return 0;
        }

        // 解析路由协议
        $urlParse = parse_url($url);
        // $url = $urlParse['scheme']."://127.0.0.1".$urlParse['path'];
        $url = "http://127.0.0.1".$urlParse['path'];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Host: '.$urlParse['host'],
        ]);

        $head = curl_exec($ch);
        //请求失败
        if (curl_errno($ch)){
            $this->warn("请求失败:".curl_error($ch).' -- '.$url.'('.$urlParse["host"].')');
            curl_close($ch);
            return 0;
        }
        curl_close($ch);
        $this->info($head);
        return 0;
    }
}
