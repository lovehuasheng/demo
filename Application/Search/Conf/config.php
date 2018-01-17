<?php
return array(
	//'配置项'=>'配置值'
        'MODULE_ALLOW_LIST'     =>  array('Home','Search','Common'), // 配置你原来的分组列表
        //'配置项'=>'配置值'
        'LOAD_EXT_CONFIG' => 'db.config',
        'DIGKEY_TIME'=> 60*60*24*40,//digkey 超过四天不展示
        'FCE_TIME'=> 60*60*24*20,    //future,chip1stop,element14 更新时间超过2天的，不显示

        'LIANYING_SUPERLIER'=>array(
            1=>'future',
            2=>'powerandsignal',
            3=>'rochester',
            4=>'tme',
            5=>'verical',
            6=>'element14',
            7=>'digikey',
            8=>'chip1stop',
            10=>'arrow',
            12=>'alliedelec',
            13=>'avnet',
            14=>'mouser',
            15=>'company',
            16=>'liexin_lianying',
            18=>'liexin_ti'
        ),
        'MOUSER_API'=>'http://footstone.ichunt.net/webapi/handle_mouser' ,
        'API_KEY' =>'LX@ichunt.com82560956-0755',
);