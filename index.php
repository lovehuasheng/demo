<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
/*echo phpinfo();die;*/
/*$connection = new Mongo("mongodb://127.0.0.1:27017"); //连接mongodb
var_dump($connection->listDBs());*/


/*$connection = new Mongo("mongodb://192.168.1.66:27017"); //连接mongodb
var_dump($connection->listDBs());die;*/
/*$conn=new Mongo("mongo://ichunt:123456@192.168.1.166:27017/ichunt");
dump($conn);die;*/
/*$db_url = "mongo://ichunt:123456@192.168.1.166:27017/ichunt";
try {   // 捕获异常，防止无法连接 mongodb 导致后面的程序无法正常运行
        $Mongo = new MongoClient('mongo://ichunt:123456@192.168.1.166:27017/ichunt');

        echo 'asdfa';die;
    } catch(Exception $e) {
        echo "dddd";die;
       var_dump($e);die;
    }*/
// 应用入口文件

// 检测PHP环境
if(version_compare(PHP_VERSION,'5.3.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

// 定义应用目录
define('APP_PATH','./Application/');

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单