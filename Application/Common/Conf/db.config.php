<?php
return array(
            /* 数据库配置 */
                 'DB_TYPE'               => 'mongo', // 数据库类型
                 'DB_HOST'               => '192.168.1.237',// 服务器地址
                 'DB_NAME'               => 'ichunt', // 数据库名
                 'DB_USER'               => '', // 用户名
                 'DB_PWD'                => '',  // 密码
                 'DB_PORT'               => '27017', // 端口
                 'DB_PREFIX'             => '', // 数据库表前缀
                 'DB_CONFIG1' => 'mysql://root:123456@192.168.1.232:3306/matches#utf8',
                 'DB_CONFIG2' => 'mysql://root:123456@192.168.1.232:3306/mouser#utf8',
                 //所有数据库的链接
                'MYSQL_POWER'=>'mysql://spu:spu@192.168.1.235:3306',
                'URL_CASE_INSENSITIVE'  =>  true, 
                'REDIS_HOST'=>'192.168.1.235',
                'REDIS_PASSWORD'=>'icDb29mLy2s',
                //es服务ip
                'ES_IP_PORT'=>'172.18.137.29:9211',
    //rabbitmq链接信息
    'RABBITMQ_CONFIG'=>array(
        "host" => "192.168.1.232",
        "port" => "5672",
        "user_name" => "guest",
        "password" => "guest",
    ),
    );