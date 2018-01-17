<?php

    /**
      * 获取价格数组
      * @param goods_id string                  商品id
      * @param priceArr array                   原来的价格梯度
      * @param priceType string                 价格类型 1：默认 2：欧洲系数
      * @param price_float_num number           保留小数位数，默认4位
      * @return resArr array                    调整后的价格梯度
    */
    function getPriceArrByGoodsID($goods_id,$priceArr,$priceType=1,$price_float_num = 4) {
        $pn = getPnByGoodsID($goods_id);
        if (!$pn) {
            return false;
        }
        switch (strval($priceType)) {
          case '1':
            $hkType = 'hk';
            $cnType = 'cn';
            break;
          case '2':
            $hkType = 'hk_E';
            $cnType = 'cn_E';
            break;
          default:
            # code...
            break;
        }
        $price_config = C('PRICE_MULTI');//价格系数
        foreach($priceArr as $k => &$v) {
            if(isset($_GET['callback']) && !empty($_GET['callback'])){
                $cn = $v[1] * $price_config[$pn][$cnType];//人民币价格
                $cn = number_format($cn, $price_float_num, '.', '');
                array_push($v,$cn);
                
                $hk = $v[1] * $price_config[$pn][$hkType];//美金价格
                $hk = number_format($hk, $price_float_num, '.', ''); //浮动保留位数
                array_push($v,$hk);
            }else{
                $hk = $v[1] * $price_config[$pn][$hkType];//美金价格
                $hk = number_format($hk, $price_float_num, '.', ''); //浮动保留位数
                array_push($v,$hk);

                $cn = $v[1] * $price_config[$pn][$cnType];//人民币价格
                $cn = number_format($cn, $price_float_num, '.', '');
                array_push($v,$cn);
            }
        }
        return $priceArr;
    }
    
    /**
      * 获取价格数组
      * @param code string                  序号(供应商名称)
      * @param priceArr array                   原来的价格梯度
      * @param priceType string                 价格类型 1：默认 2：欧洲系数
      * @param price_float_num number           保留小数位数，默认4位
      * @return resArr array                    调整后的价格梯度
    */
    function getPriceArrByCode($code,$priceArr,$priceType=1,$price_float_num = 4) {
        $pn = $code;
        if (!$pn || empty($priceArr)) {
            return array();
        }
        switch (strval($priceType)) {
          case '1':
            $hkType = 'hk';
            $cnType = 'cn';
            break;
          case '2':
            $hkType = 'hk_E';
            $cnType = 'cn_E';
            break;
          default:
            # code...
            break;
        }
        $price_config = C('PRICE_MULTI');//价格系数
        $res = array();
        foreach($priceArr as $k => $v) {
            $temp = array();
            if(isset($price_config[$pn][$cnType]) && !empty($price_config[$pn][$cnType])){
                $temp[0] = $v['purchases'];
                $temp[1] = $v['price_us'];
                $cn = $v['price_us'] * $price_config[$pn][$cnType];//人民币价格
                $cn = number_format($cn, $price_float_num, '.', '');
                $temp[2] = $cn;

                $hk = $v['price_us'] * $price_config[$pn][$hkType];//美金价格
                $hk = number_format($hk, $price_float_num, '.', ''); //浮动保留位数
                $temp[3] = $hk;
            }else{
                $temp[0] = $v['purchases'];
                $temp[1] = $v['price_us'];
                $temp[2] = $v['price_cn'];
                $temp[3] = $v['price_us'];
            }
            $res[] = $temp;
        }
        return $res;
    }

    /**
      * 获取供应商名称
      * @param goods_id string                  商品id
      * @return pn array                    供应商名称
    */
    function getPnByGoodsID($goods_id) {
        $OTHER_DB = substr($goods_id, 0, 2); //商品id前两位判断所属供应商如21：chip1stop
        $pn = C("OTHER_DB.{$OTHER_DB}"); //供应商名称
        return $pn ? $pn : '';
    }


    /**
     * 连接型号名称，用于迅搜索引
     * @param  string $goods_name       型号名
     * @return 处理后的名称
     */
    function concat_ic($goods_name = '')
    {
        $temp = str_replace(array('#','.','/','-','=','_','+','(',')','!','"','$','%','&','\'','*',',',':',';','<','\|',' '),'',$goods_name);
        return $goods_name.'€€'.$temp;
    }

  /**
   * 传递数据以易于阅读的样式格式化后输出
   * @param  array $data       传递数组
   * @return 处理后的数组
   */
  function p($data){
      // 定义样式
      $str='<pre style="display: block;padding: 9.5px;margin: 44px 0 0 0;font-size: 13px;line-height: 1.42857;color: #333;word-break: break-all;word-wrap: break-word;background-color: #F5F5F5;border: 1px solid #CCC;border-radius: 4px;">';
      // 如果是boolean或者null直接显示文字；否则print
      if (is_bool($data)) {
          $show_data=$data ? 'true' : 'false';
      }elseif (is_null($data)) {
          $show_data='null';
      }else{
          $show_data=print_r($data,true);
      }
      $str.=$show_data;
      $str.='</pre>';
      echo $str;
  }
  
  /**
     * 提交POST数据
     * 如果使用Curl 你需要改一改你的php.ini文件的设置，找到php_curl.dll去掉前面的";"就行了
     *
     * @param   string   服务接口地址
     * @param   mixed    提交数据
     * @param   array    http头信息
     * @param   boolean  是否SSL传输
     * @return  mixed    返回结果
     **/
    function http_post($gateway_url, $req_data,$time_out=1, $optional_headers = null, $isSSL = false) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $gateway_url);                //配置网关地址
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT,$time_out);//超时时间
        curl_setopt($ch, CURLOPT_POST, 1);                          //设置post提交
        curl_setopt($ch, CURLOPT_POSTFIELDS, $req_data);            //post传输数据
        if ($optional_headers !== null){
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optional_headers);
        }
        else{
            curl_setopt($ch, CURLOPT_HEADER, 0);                        //过滤HTTP头
        }
        if($isSSL){
            curl_setopt($ch, CURLOPT_SSLVERSION, CURL_SSLVERSION_TLSv1); //SSL版本
        }
        $data = curl_exec($ch);
        curl_close($ch);
        return $data;
    }