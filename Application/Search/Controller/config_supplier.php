<?php
return array(
    'CATALOG_COMPANY' =>array(
        '111'=>'PowerexPowerSemiconductors',
        '112'=>'microchip',
        '113'=>'MAXIM',
        '114'=>'LINEARTECHNOLOGY',
        '115'=>'COILCRAFT',
        '12'=>'Chiponestop',
        '13'=>'Element14',
        '14'=>'FUTURE',
        '15'=>'Digi-Key',
        '17'=>'Verical',
        '18'=>'Arrow',
        '19'=>'Avnet',
        '20'=>'alliedelec',
        '21'=>'RS',
        '22'=>'Onlinecomponents',
        '23'=>'Rutronik',
        '24'=>'rochester',
        '25'=>'Mouser',
        '27'=> 'tme',
        //'28'=> 'powerandsignal',
        '29'=> 'peigenesis',
        //'WPI',
        //'Master',
        //'TTI',
        //'Heilind',
    ),
    //价格系数 hk:香港，cn:大陆，rate:暂时不理
    'PRICE_MULTI' => array(
            'chip1stop'        => array('hk' => 1,    'cn' => 6.8*1.17*1,  'rate' =>0),
            'element14'        => array('hk' => 0.13*1, 'cn' => 0.86*1.17*1,'rate' =>0),
           'future'           => array(
                         'hk' => 1,   'cn' => 6.8*1.17*1,
                         'hk_E' => 1,   'cn_E' => 6.8*1.17,
                         'rate' =>0),
           'digikey'          => array('hk' => 1,      'cn' => 6.8*1.17,   'rate' =>0),
           'verical'          => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'alliedelec'       => array('hk' => 1,    'cn' => 6.8*1.17*1,  'rate' =>0),
           'rs'         => array('hk' => 0,    'cn' => 1.17,  'rate' =>0),
           'avnet'       => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'arrow'       => array('hk' => 1,    'cn' => 6.75*1.17,  'rate' =>0),
           'online'       => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'rutronik24'       => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'mouser'       => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'rochester'     => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'tti'         => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'company'       => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'tme'         => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           //'powerandsignal'   => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
           'peigenesis'     => array('hk' => 1,    'cn' => 6.8*1.17,  'rate' =>0),
      ),
    'SUPPLIER_DB' => array(
        '11'            => 'company',
        '12'            => 'chip1stop',
        '13'            => 'element14',
        '14'            => 'future',
        '15'            => 'digikey',
        '16'            => 'matches',
        '17'            => 'verical',
        '18'            => 'arrow',
        '19'            => 'avnet',
        '20'            => 'alliedelec',
        '21'            => 'rs',
        '22'            => 'online',
        '23'            => 'rutronik24',
        '24'            => 'rochester',
        '25'            => 'mouser',
        '26'            => 'tti',
        '27'            => 'tme',
        //'28'            => 'powerandsignal',
        '29'            => 'peigenesis',
    ),
    //全部独立供应商（除fchip来源的） 【新增供应商需要修改】
    //某些原厂公用一个k值，需要转换，如111,112 共用11
    'ALLSUPPLIER' => array(
        'rs' => array(
            'flag' => 21,
            'flagName' => 'RS', //RS ONLINE
            'img' => 'RS.jpg',//
            'supplierId' => '32548', //
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'tme' => array(
            'flag' => 27,
            'flagName' => 'TME', //TME
            'img' => 'tme.jpg',//
            'supplierId' => '45208', //
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        /*'powerandsignal' => array(
            'flag' => 28,
            'flagName' => 'powerandsignal', //powerandsignal
            'img' => 'powerandsignal.jpg',//
            'supplierId' => '46915', //待定
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),*/
        'peigenesis' => array(
            'flag' => 29,
            'flagName' => 'peigenesis', //peigenesis
            'img' => 'peigenesis.jpg',//
            'supplierId' => '46916', //待定
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'COILCRAFT' => array(
            'flag' => 115,
            'flagName' => 'COILCRAFT', //现艺
            'img' => 'coilcraft.jpg',//
            'supplierId' => '45198', //
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'MAXIM' => array(
            'flag' => 113,
            'flagName' => 'MAXIM', //美信
            'img' => 'maxim.jpg',//
            'supplierId' => '45124', //待改
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'LINEARTECHNOLOGY' => array(
            'flag' => 114,
            'flagName' => 'LINEAR TECHNOLOGY', //凌力尔特
            'img' => 'linear.jpg',//
            'supplierId' => '45123', //待改
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'PowerexPowerSemiconductors' => array(
            'flag' => 111,
            'flagName' => 'Powerex Power Semiconductors',
            'img' => 'powerex.jpg',
            'supplierId' => '45086',
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'microchip' => array(
            'flag' => 112,
            'flagName' => 'microchip',
            'img' => 'microchip.jpg',
            'supplierId' => '45085',
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'rochester' => array(
            'flag' => 24,
            'flagName' => 'Rochester Electronics',
            'img' => 'rochester.jpg',
            'supplierId' => '45002',
            'cnTime' => '5-11工作日',
            'hkTime' => '4-9工作日',
            ),
        'arrow' => array(
            'flag' => 18,
            'flagName' => 'Arrow（艾睿）',
            'img' => 'arrow-h.jpg',
            'supplierId' => '32543',
            'cnTime' => '5-11工作日',
            'hkTime' => '4-9工作日',
            ),
        'alliedelec' => array(
            'flag' => 20,
            'flagName' => 'Allied Electronics',
            'img' => 'Allied.jpg',
            'supplierId' => '32546',
            'cnTime' => '7-14工作日',
            'hkTime' => '5-12工作日',
            ),
        'future' => array(
            'flag' => 14,
            'flagName' => 'Future',
            'img' => 'future1.gif',
            'supplierId' => '29963',
            'cnTime' => '4-9工作日',
            'hkTime' => '3-7工作日',
            ),
        'mouser' => array(
            'flag' => 25,
            'flagName' => 'Mouser Electronics',
            'img' => 'mouser1.jpg',
            'supplierId' => '30025',
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'digikey' => array(
            'flag' => 15,
            'flagName' => 'Digikey',
            'img' => 'digikey1.jpg',
            'supplierId' => '30024',
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
        'element14' => array(
            'flag' =>13,
            'flagName' => 'Element14（e络盟）',
            'img' => 'element14-h.jpg',
            'supplierId' => '32536',
            'cnTime' => '3-11工作日',
            'hkTime' => '3-9工作日',
            ),
        'chip1stop' => array(
            'flag' =>12,
            'flagName' => 'Chip One Stop',
            'img' => 'Chiponestop.jpg',
            'supplierId' => '32538',
            'cnTime' => '7-10工作日',
            'hkTime' => '5-8工作日',
            ),
        'verical' => array(
            'flag' =>17,
            'flagName' => 'Verical',
            'img' => 'verical-h.jpg',
            'supplierId' => '32537',
            'cnTime' => '8-14工作日',
            'hkTime' => '6-12工作日',
            ),
        'avnet' => array(
            'flag' => 19,
            'flagName' => 'Avnet',
            'img' => 'avnet-h.jpg',
            'supplierId' => '32542',
            'cnTime' => '5-9工作日',
            'hkTime' => '4-7工作日',
            ),
    ),
    //建立spu时，重新定义的supplier_id与供应商名称对应的配置
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
);