<?php

namespace Githen\LaravelCommon\Commands;

use Illuminate\Console\Command;

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
        if (opcache_reset()) {
            $this->info('成功重置所有opcache缓存');
            return 0;
        }
        $this->warn('重置所有opcache缓存失败');
        return 0;
    }
}
