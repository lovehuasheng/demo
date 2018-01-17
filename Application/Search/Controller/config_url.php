<?php
return array(
    //路由
    /* URL设置 */
    'URL_CASE_INSENSITIVE'   => true, // 默true 表示URL不区分大小写 false则表示区分大小写
    'URL_MODEL'              => 2, // URL访问模式,可选参数0、1、2、3,代表以下四种模式： 0 (普通模式); 1 (PATHINFO 模式); 2 (REWRITE  模式); 3 (兼容模式)  默认为PATHINFO 模式

    'URL_ROUTER_ON'          => true, // 是否开启URL路由
    'URL_ROUTE_RULES'        => array(//动态路由
        '/^public\/verify\/id\/(\d+)/' => 'Home/Public/verify?id=:1',
        '/^public\/(\w+)/' => 'Home/Public/:1',
        //商品详情页
        '/^goods_(\w+)/'    => 'Home/Details/details?goods_id=:1',//商品详情页
        //分类列表
        'class/list/:id\d'    => 'Home/Classify/classify_list/class_id/:1',
        //品牌详情
        '/^brand\/list\/(\d+)/'     => 'Home/Brand/brand_list?brand_id=:1',
        '/^class\/list\/(\d+)/'    => 'Home/Classify/classify_list?class_id=:1',
//        '/^ic\/(\w+)/'       => 'Home/Ic/ic?letter=:1',
      //  '/^ic\/([A-Za-z0-9]{1,2})(?:_(\d+))?$/' => 'Home/Ic/ic?letter=:1&p=:2',
    ),
    'URL_MAP_RULES'          => array(//静态路由
        'help' => 'Home/Help/index',
        'api' => 'Home/Api/index',
        'index' => 'Home/Index/index',
        'lianying' => 'Home/Joint/index',
        'zhuanmai' => 'Home/Joint/shop',

        //登录注册
        'reg'               => 'Home/Personal/register',
        'reg/success'       => 'Home/Personal/register_tip',
        'login'             => 'Home/Personal/login',
        'login/qq'          => 'Home/Personal/qq',
        'login/bind'        => 'Home/Personal/login_bind',
        'login/bindsuccess' => 'Home/Personal/login_bind_tip',
        'forget'            => 'Home/Personal/find',

        //会员中心
        'user/order'        => 'Home/User/index', //订单中心首页
        'user/shoporder'    => 'Home/User/indent_store',//订单商城
        'user/orderdetail'  => 'Home/User/indent_store_details',//订单明细
        'user/orderinvoice' => 'Home/User/indent_invoice',//订单发票
        'user/account'      => 'Home/User/account_index',//基本信息
        'user/reciveaddress'=> 'Home/User/account_shipping_address',//收货地址
        'user/sendaddress'  => 'Home/User/account_send_address',//发货地址
        'user/invoice'      => 'Home/User/account_invoice',//用户发票
        'user/guide'        => 'Home/User/indent_help_two',//引导页
        'user/emailbind'    => 'Home/User/account_index_mail',//邮件绑定
        'user/saleadd'       => 'Home/User/sale_add',//库存批量上传
        'user/salemanage'       => 'Home/User/goods',//库存批量上传
        'user/coupon'  => 'Home/User/user_coupon',   //优惠劵
		's' => 'Home/Search/index',//搜索路径

        'user/saleadd'       => 'Home/User/sale_add',//库存批量上传
        'user/salemanage'       => 'Home/User/goods',//库存批量上传

        //购物流程
         'joincart'    => 'Home/Car/index',//购物车
         'order/confirm'    => 'Home/Car/order_form',//结算页面
         'pay/online'   => 'Home/Car/checkstand',// 收银台
          'order/success'   => 'Home/Car/order_success',// 订单成功
           'pay/success'   => 'Home/Car/pay_success',// 支付成功
            'pay/fail'   => 'Home/Car/pay_fail',// 支付失败

        //分类地图
        'class/map'     => 'Home/Classify/classify_map',
        //品牌地图
        'brand/map'     => 'Home/Brand/brand_map',
	'user/msg'        => 'Home/User/news', //消息中心

        '404'               =>  'Home/Error/error404',//404
        //分类列表搜索sku接口
        'sku/list'      => 'Home/classify/sku_list',
        '404'               =>  'Home/Error/error404',//404
   'chain'     => 'Home/Service/customs',
      //历史数据
     'forex'     => 'Home/Forex/index',

           //关于我们
         'about'               =>  'Home/About/index',//首页
          'about/public'               =>  'Home/About/public_relationship',//公共关系
            'about/contact'               =>  'Home/About/contact',//联系我们
             'about/jobs'               =>  'Home/About/jobs',//人才招聘
              'about/company'               =>  'Home/About/company',//公司动态
              'about/detail'               =>  'Home/About/detail',//公司动态详情

              //猎单宝 3.0
               'ldb'               =>  'Home/Ldb/index',//猎单宝首页
                 'ldb/banner'               =>  'Home/Ldb/banner',//猎单宝首页
                 //资讯
                                'news'               =>  'Home/News/index',//资讯列表
                                  'news/detail'               =>  'Home/News/news_detail',//资讯详情

        //频道页
        'chip1stop'=>'home/title/index',
        'chip1stop/hd'=>'home/title/title',
    ),
);
