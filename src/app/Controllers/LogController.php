<?php

namespace Githen\App\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

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
        dd(__CLASS__, __FUNCTION__);

    }
}
