<?php

namespace Githen\LaravelCommon\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;

/**
 * 日志查看
 */
class LogController extends  Controller
{

    /**
     * 统一出入口
     * @param Request $request
     * @param $act
     * @return mixed
     */
    public function act(Request $request, $act)
    {
        return $this->$act($request);
    }


    private function index(Request $request)
    {
        $logFiles = File::allFiles(storage_path('logs/'));

        $logFiles = collect($logFiles)->mapToGroups(function ($item, $key){
            return [$item->getRelativePath() => $item];
        })->sortKeys();

        $curRoute = Route::current();

        dd($curRoute->uri(333));

        return view()->file(__DIR__.'/../../resources/views/log.blade.php', [
            'files' => $logFiles,
            'uri' => $curRoute->uri(),
        ]);
    }
}
