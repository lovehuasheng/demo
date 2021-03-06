<?php
return array(
    //价格保留位数
    'PRICE_FLOAT_NUM' => 4,
	//交期
	'DT'     => array(
			  'chip1stop'        => array('香港：4-7','国内：5-9'),
			  'element14'        => array('香港：4-7','国内：5-9'),
			  'future'           => array('香港：4-7','国内：5-9'),
			  'digikey'          => array('香港：4-7','国内：5-9'),
			  'verical'          => array('香港：4-7','国内：5-9'),
			  'alliedelec'       => array('香港：4-7','国内：5-9'),
			  'rs'				 => array('香港：4-7','国内：5-9'),
			  'avnet'		     => array('香港：4-7','国内：5-9'),
			  'online'		     => array('香港：4-7','国内：5-9'),
			  'arrow'		     => array('香港：4-7','国内：5-9'),
			  'rutronik24'		 => array('香港：4-7','国内：5-9'),
			  'mouser'			 => array('香港：4-7','国内：5-9'),
			  'rochester'		=> array('香港：4-7','国内：5-9'),
			  'tti'				=> array('香港：4-7','国内：5-9'),
			  'company'			=> array('香港：4-7','国内：5-9'),
			  'tme'				=> array('香港：4-7','国内：5-9'),
			  'powerandsignal'	=> array('香港：4-7','国内：5-9'),
			  'peigenesis'		=> array('香港：4-7','国内：5-9'),
			),

    //价格系数 hk:香港，cn:大陆，rate:暂时不理
    'PRICE_MULTI' => array(
              'chip1stop'        => array('hk' => 1,	  'cn' => 6.8*1.17*1,  'rate' =>0),
              'element14'        => array('hk' => 0.13*1, 'cn' => 0.86*1.17*1,'rate' =>0),
			  'future'           => array(
										  'hk' => 1,   'cn' => 6.8*1.17*1,
										  'hk_E' => 1,   'cn_E' => 6.8*1.17,
										  'rate' =>0),
			  'digikey'          => array('hk' => 1,      'cn' => 6.8*1.17,   'rate' =>0),
			  'verical'          => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'alliedelec'       => array('hk' => 1,	  'cn' => 6.8*1.17*1,  'rate' =>0),
			  'rs'				 => array('hk' => 0,	  'cn' => 1.17,  'rate' =>0),
			  'avnet'			 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'arrow'			 => array('hk' => 1,	  'cn' => 6.75*1.17,  'rate' =>0),
			  'online'			 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'rutronik24'       => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'mouser'			 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'rochester'		 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'tti'				 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'company'			 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'tme'				 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'powerandsignal'	 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
			  'peigenesis'		 => array('hk' => 1,	  'cn' => 6.8*1.17,  'rate' =>0),
	 ),

    //供应商标示
    'OTHER_DB' => array(
			'11'			=> 'company',
			'12'			=> 'chip1stop',
            '13'			=> 'element14',
			'14'			=> 'future',
			'15'			=> 'digikey',
			'16'			=> 'matches',
			'17'			=> 'verical',
			'18'			=> 'arrow',
			'19'			=> 'avnet',
			'20'			=> 'alliedelec',
			'21'			=> 'rs',
			'22'			=> 'online',
			'23'			=> 'rutronik24',
			'24'			=> 'rochester',
			'25'			=> 'mouser',
			'26'			=> 'tti',
			'27'			=> 'tme',
			'28'			=> 'powerandsignal',
			'29'			=> 'peigenesis',
			
	),
    //供应商logo
    'PIUrl' => array(
        'rs' => '/Public/Home/images/RS.jpg',
        'tme' => '/Public/Home/images/tme.jpg',
        'peigenesis' => '/Public/Home/images/peigenesis.jpg',
        'COILCRAFT' => '/Public/Home/images/coilcraft.jpg',
        'MAXIM' => '/Public/Home/images/maxim.jpg',
        'LINEARTECHNOLOGY' => '/Public/Home/images/linear.jpg',
        'PowerexPowerSemiconductors' => '/Public/Home/images/powerex.jpg',
        'microchip' => '/Public/Home/images/microchip.jpg',
        'rochester' => '/Public/Home/images/rochester.jpg',
        'arrow' => '/Public/Home/images/arrow-h.jpg',
        'alliedelec' => '/Public/Home/images/Allied.jpg',
        'future' => '/Public/Home/images/future1.gif',
        'mouser' => '/Public/Home/images/mouser1.jpg',
        'digikey' => '/Public/Home/images/digikey1.jpg',
        'element14' => '/Public/Home/images/element14-h.jpg',
        'chip1stop' => '/Public/Home/images/Chiponestop.jpg',
        'verical' => '/Public/Home/images/verical-h.jpg',
        'avnet' => '/Public/Home/images/avnet-h.jpg',
        'powerandsignal' => '/Public/Home/images/powerandsignal.jpg',
        'tti' => '/Public/Home/images/TTI.jpg',
        ),
    //11供应商id对应策略信息
    'SUP11' => array(
        
    ),

    //供应商官网网址
    'PUrl' => array(
            'chip1stop'     =>  'http://www.chip1stop.com/web/CHN/zh',
            'element14'     =>  'http://hk.element14.com',
			'future'		=>  'http://www.futureelectronics.com/en',
			'digikey'		=>  'http://www.digikey.cn',
			'verical'		=>  'http://www.verical.com',
			'rs'			=>  'http://china.rs-online.com/',
			'avnet'			=>  'http://www.avnet.com/',
			'rutronik24'	=>  'http://www.rutronik24.com/',
			'arrow'			=>  'http://www.arrow.com/',
			'mouser'		=>  'http://www.mouser.com/',
			'rochester'		=>  'https://www.rocelec.com/',
			'tti'			=>  'https://www.ttiinc.com/',
			'tme'			=>  'http://www.tme.eu/zh/',
			'peigenesis'	=>  'http://www.peigenesis.cn/cn',
    ),

);