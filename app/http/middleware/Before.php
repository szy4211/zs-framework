<?php
/**
 * @description: 中间件事例
 *
 * @date 2019-06-19
 * author zornshuai@foxmail.com
 */

namespace App\Http\Middleware;


use Framework\Request;

class Before
{
    public function handler(Request $request, \Closure $next)
    {
        return $next($request);
    }
}