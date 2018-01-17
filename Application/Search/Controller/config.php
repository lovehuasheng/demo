<?php
return array(
	//'配置项'=>'配置值'
        'MODULE_ALLOW_LIST'     =>  array('Home','Search','Common'), // 配置你原来的分组列表
        //'配置项'=>'配置值'
        'LOAD_EXT_CONFIG' => 'db.config',
        'DIGKEY_TIME'=> 60*60*24*4,//digkey 超过四天不展示
        'FCE_TIME'=> 60*60*24*2,    //future,chip1stop,element14 更新时间超过2天的，不显示
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
            18=>'liexin_ti',
            19=>'peigenesis',
            20=>'powell',
            21=>'rs',
            22=>'liexin_sell',
        ),
        'SUPERLIER_ALL'=>array(
            1=>'future',
            2=>'powerandsignal',
            3=>'rochester',
            4=>'tme',
            5=>'verical',
            6=>'element14',
            7=>'digikey',
            8=>'chip1stop',
            9=>'aipco',
            10=>'arrow',
            11=>'bisco',
            12=>'alliedelec',
            13=>'avnet',
            14=>'mouser',
            15=>'company',
            16=>'liexin_lianying',
            17=>'zhuanmai',
            18=>'liexin_ti',
            19=>'peigenesis',
            20=>'powell',
            21=>'rs',
            22=>'liexin_sell',
            
            
            100=>'ziying',
      
        ),
        //mouser增量接口
        'MOUSER_API'=>'http://footstone.liexin.net/webapi/handle_mouser' ,
        //redis没有读到时，请求接口
        'GET_GOODS_DETAILS_URL'=>'http://footstone.liexin.net/webapi/goods_details',
        'API_KEY' =>'LX@ichunt.com82560956-0755',
        'HHS_SUPPLIER_AD'=>30,
);
