<?php
/**
 * @description: TODO
 *
 * @date 2019-06-13
 */

namespace App\Controllers;

use App\Models\Mongodb\User;
use Framework\Exception\HttpCode;
use Framework\Model\MongoDb\MongoDb;
use Framework\Model\Mysql\DB;
use Framework\Model\Mysql\Db2;
use Framework\Response;

class HomeController
{
    public function home()
    {
//        echo '<pre>';

        //  mysql 测试
//        $area = Area::where('parentid', '653127')
//            ->get()
//            ->toArray();
        //  mongodb 测试
//        $mongodb = new MongoDb();
//        $mongodb->table('users');
//        dd($mongodb->count());

//        $arr = [];
//        for($i = time() * 100000; $i < time() * 100000 + 100000; $i++) {
//            $arr[] = [
//                'userid'   => 'admin' . $i,
//                'password' => '123456',
//            ];
//        }
//
//        $mongodb->insert($arr);
        $area = [];
//        $area = $mongodb->where(['userid' => 'admin156078590403306'])->select();
//        $area = $mongodb->limit(10)->select();

//        $area = Area::where('parentid', '653127')
//            ->get()
//            ->toArray();
//        $conf['dsn'] = 'mysql:host=127.0.0.1;dbname=test';
//        $conf['username'] = 'root';
//        $conf['password'] = '123456';
//        $conf['charset'] = 'utf8';
//        $conf['user'] = 'root';
//        $db = new DB();
//        $db->__setup($conf);
//        $area = $db->fetchAll('select * from t_area where parentid=653127');

//        $validate       = new Validate([
//            'a' => 'date',
//            'b' => ['must', 'url'],
//        ], ['b.must' => '哈哈哈'], ['b' => '测试']);
//        $res            = $validate->batch()->check(Request::instance()->param());
//        $included_files = get_included_files();
//        foreach ($included_files as $filename) {
//            echo "$filename\n";
//        }
//        dd(count($included_files));
//        var_dump($mongodb->whereOr([
//            ['userid'=> 'admin1'],
//            ['userid'=> 'admin3']
//        ])->order('userid')->field(['userid'])->select());
//        var_dump($mongodb->count());

//        echo '</pre>';
//        return view('home.home')->with('data', $area);
//        return View::json($area);
//        return View::make('home.home')->with('data', $area);

//        return Response\Json::create($area);
//        return Response\Json::create([]);


//        $area = (new User())->mongodb();
//        $area =(new User)->table('test');
//        $area = \Framework\Model\Mongodb\DB::table('users')
//            ->where(['userid' => 'admin156078590403306'])
//            ->select();

        $area = User::table('users')
            ->where(['userid' => 'admin156078590403306'])
            ->select();

        return self::returnJson($area, '', 200);
    }

    public function pdo2()
    {
        $conf['dsn'] = 'mysql:host=127.0.0.1;dbname=test';
        $conf['username'] = 'root';
        $conf['password'] = '123456';
        $conf['charset'] = 'utf8';
        $conf['user'] = 'root';
        $db = DB::instance();
        $db->__setup($conf);
        $area = $db->fetchAll('select * from `t_area` where `parentid`=653127');

        return self::returnJson($area, '', 200);
    }

    public function pdo()
    {
        $db = Db2::getInstance('127.0.0.1', 'root', '123456', 'test', 'utf8');

        $area = $db->query('select * from `t_area` where `parentid`=653127');
//        $db->destruct();
        return self::returnJson($area, '', 200);
    }

    static protected function returnJson($data, string $msg, int $http_code)
    {
        $msg = !empty($msg) ? $msg : HttpCode::getMessage($http_code);

        $json_array = [
            'status_code' => $http_code,
            'msg'         => $msg,
            'data'        => $data,
        ];

        return Response::create($json_array, 'json', HttpCode::OK);
    }
}