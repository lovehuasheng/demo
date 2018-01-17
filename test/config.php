<?php
return array(
	//'配置项'=>'配置值'
        'MODULE_ALLOW_LIST'     =>  array('Home','Search','Common'), // 配置你原来的分组列表
        //'配置项'=>'配置值'
        'LOAD_EXT_CONFIG' => 'db.config',
        'DIGKEY_TIME'=> 60*60*24*4,//digkey 超过四天不展示
        'FCE_TIME'=> 60*60*24*2,    //future,chip1stop,element14 更新时间超过2天的，不显示
);