<?php
namespace Vendor\Xuns;//命名空间
namespace Search\Controller;
use Think\Controller;
use Search\Model\MonModel;


class IndexController extends Controller {

    public function index(){

        $project_num = I('get.k'); //供应商标示 如21 表示 chip1stop
        $project = C("OTHER_DB.{$project_num}"); //获取供应商名称
        $q = I('get.keyword'); //型号关键词
        $_GET['kNums'] = intval($_GET['kNums']);
        if(!$project || !$q) {
            return false;
        }
        //mouser走另外接口、、数据无法获取
        if($project_num == '25'){
            $temp = array();
            $res = $this->getMouser($q);
            if(isset($res['PD']) && !empty($res['PD'])){
                $temp[0]['data'] = $res['PD'];
                $temp[0]['PIUrl'] = C("PIUrl.mouser"); //供应商官网
                $temp[0]['com_name'] = 'mouser'; //供应商官网
                $temp[0]['com_id'] = 25; //供应商官网
                $temp[0]['DT'] = C("DT.mouser") ? C("DT.mouser") : array(); //期货
            }
            return $this->returndata($temp);
            exit;
        }
        if ($project && $q)
        {

                $goodsArr = array();$i = 0;$dtFlag = false;
                $v3 = array();
                if ($project == 'digikey') {
                    $projectMod = 'digikey2';
                    $res = $this->search($projectMod,$q); //从xunsearch中根据供应商&型号获取商品id集
                } else {
                    $res = $this->search($project,$q); //从xunsearch中根据供应商&型号获取商品id集
                }

                $res = $res['docs'];
                //如果为空
                if(empty($res)){
                    
                    if(isset($_GET['callback']) && !empty($_GET['callback'])){
                        return $this->returndata(array());
                    }else{
                        return $this->returndata(array('PD'=>array(),'status'=>1));
                    }
                }
                foreach ($res as $key => $value) { //获取商品id结果集
                    $goodsArr[$key] = floatval($value['goods_id']);
                }

                if ($project_num == '16') {  //撮合数据来源
                    $sqlType = 1; //链接mysql1
                    $field = 'goods_id,goods_name,brand_id,provider_name,company_id,link_man,link_tel,company_name,batch_no,goods_number,min_buynum,last_update';
                    $model = M()->db($sqlType,"DB_CONFIG{$sqlType}");
                    $tmpArr = array();
                    //获取相关信息
                    $ids = implode(',', $goodsArr);
                    $query = "select ".$field." from `lie_goods` where goods_id in ({$ids}) and is_on_sale = 1 and status = 1"; //必须上架（is_on_sale:1）并且审核已通过status = 1
                    $resArr = $model->query($query);
                    foreach ($resArr as $k => $v) {
                        if(empty($v['company_name']) && !empty($v['company_id'])){
                            $m_res = $this->getCompanyName($v['company_id']);
                            if($m_res){
                                $resArr[$k]['company_name'] = $m_res;
                            }
                        }
                        $resArr[$k]['goods_name_temp'] = str_ireplace($q, "<b class='f-red fw'>".strtoupper($q)."</b>",$resArr[$k]['goods_name']);
                        $value = $v['goods_id'];
                        //获取价格梯度
                        $sqlNum = substr($value,-1);//根据goods_id 最后一位确定价格梯度的数据库编号
                        $querySql = "select price from `lie_goods_price_{$sqlNum}` where goods_id = '{$value}'";
                        $re = $model->query($querySql);
                        $re = $re[0];
                        if (!empty($re)) {
                            $priceArr = json_decode(trim($re['price']),true);
                            $resArr[$k]['price'] = $priceArr;
                        } else {
                            $resArr[$k]['price'] = '';
                        }
                    }
                    
                    $finalResArr['PD'] = $resArr;
                    if ($resArr) { //有数据为0，无则为1;
                        $finalResArr['status'] = 0;
                    } else {
                        $finalResArr['status'] = 1;
                    }
                    $v3 = $finalResArr;
                } else {

                    $M= new MonModel();//chip1stop是集合名（表名）
                    $param = array('old_goods_id'=>array('$in'=>$goodsArr));
                    $res_ids = $M->select('sku',$param);
                    if(!empty($res_ids)){
                        $new_ids = array();
                        foreach($res_ids as $k=>$v){
                            $new_ids[] = $v['old_goods_id'];
                        }
                        
                        $param_new = array('goods_id'=>array('$in'=>$new_ids));
                        $res_list = $M->select($project,$param_new);
                    }else{
                        $res_list = array();
                    }
                        
                    if(!empty($res_list)){
                        foreach ($res_list as $key => $resArr) {

                            //$param = array('goods_id'=>$value);
                            //$resArr = $M->fetchRow($project,$param); //根据数据表及条件查询结果

                            if (!$resArr) { //如果结果为空，跳过，（表明xunsearch返回的某goods_id在mongodb里没有相应的数据）
                                continue;
                            }

                            //is_error有设置并且不为空时，跳过 如is_error = 404
                            if (isset($resArr['is_error']) && $resArr['is_error'] !== 0) {
                                continue;
                            }

                            if ($project_num == '15') { //digkey 超过四天不展示
                                if (intval($resArr['time'])+C('DIGKEY_TIME') < time()) {
                                    continue;
                                }
                            }

                            //future,chip1stop,element14 更新时间超过2天的，不显示
                            if ($project_num == '14' || $project_num == '12' || $project_num == '13') {
                                if (intval($resArr['time'])+C('FCE_TIME') < time()) {
                                    continue;
                                }
                            }

                            if (count($resArr['stock']) == 1) { //正常结构应包含两个元素demo:array(1,2);如只有一个元素（一般为0），需要转化，不然页面显示有误
                                $stock = floatval(0);
                                $resArr['stock'] = array($stock,$stock);
                            }
                            //如果有库存参数，则把库存低的过滤掉
                            if(isset($_GET['kNums']) && $_GET['kNums']>$resArr['stock'][1]){
                                continue;
                            }
                            if (!$dtFlag) { //如果数据表里没有期货内容，就用配置里的
                                if($resArr['dt'][0] && $resArr['dt'][1]){
                                    $dtRes = $resArr['dt'];
                                    $dtFlag = true;
                                }
                            }

                            $priceType = 1;
                            if ($project == 'future') { //富昌欧洲仓（goods_sn 末尾含€€E）调用另一个欧洲价格系数
                                $needle = '€€E';
                                if (strpos($resArr['goods_sn'], strval($needle))===false) { //
                                } else {
                                    $priceType = 2;
                                }
                            }

                            $priceArr = $resArr['tiered']; //原始价格集
                            $priceArr = getPriceArrByGoodsID($resArr['goods_id'],$priceArr,$priceType); //获取调整后的价格集
                            $resArr['tiered'] = $priceArr;
                            $pd[$i][] = $resArr;
                            $i++;
                            //用于v3的输出
                            $resArr['goods_name_temp'] = str_ireplace($q, "<b class='f-red'>".strtoupper($q)."</b>",$resArr['goods_name']);
                            if($project_num == 11){
                                $temp_p = preg_replace('# #','',$resArr['brand_name']);
                                //$resArr['goods_name'] = str_ireplace($q, "<b class='fc_font'>".strtoupper($q)."</b>",$resArr['goods_name']);
                                if(isset($v3[$resArr['brand_name']]['data']) && count($v3[$resArr['brand_name']]['data'])>0){
                                    if(isset($resArr['tiered'][0][0]) && $resArr['tiered'][0][0]>0 && isset($resArr['stock'][1]) && $resArr['stock'][1]>0){
                                        array_unshift($v3[$resArr['brand_name']]['data'], $resArr);
                                    }else{
                                        array_push($v3[$resArr['brand_name']]['data'], $resArr);
                                    }
                                }else{
                                    $v3[$resArr['brand_name']]['data'][] = $resArr;
                                }
                                //$v3[$resArr['brand_name']]['data'][] = $resArr;
                                $v3[$resArr['brand_name']]['PIUrl'] = C("PIUrl.{$temp_p}"); //供应商图片
                                $v3[$resArr['brand_name']]['PUrl'] = C("PUrl.{$temp_p}"); //供应商官网
                                $v3[$resArr['brand_name']]['com_name'] = $resArr['brand_name']; //供应商官网
                                $v3[$resArr['brand_name']]['com_id'] = C("SUP11.{$temp_p}"); //供应商官网
                                if ($dtFlag) {
                                    $v3[$resArr['brand_name']]['DT'] = $dtRes; //期货
                                } else {
                                    $v3[$resArr['brand_name']]['DT'] = C("DT.company") ? C("DT.company") : array(); //期货
                                }

                            }else{
                                //$resArr['goods_name'] = str_ireplace($q, "<b class='fc_font'>".strtoupper($q)."</b>",$resArr['goods_name']);
                                if(isset($v3[0]['data']) && count($v3[0]['data'])>0){
                                    if(isset($resArr['tiered'][0][0]) && $resArr['tiered'][0][0]>0 && isset($resArr['stock'][1]) && $resArr['stock'][1]>0){
                                        array_unshift($v3[0]['data'], $resArr);
                                    }else{
                                        array_push($v3[0]['data'], $resArr);
                                    }
                                }else{
                                    $v3[0]['data'][] = $resArr;
                                }
                                //$v3[0]['data'][] = $resArr;
                                $v3[0]['PIUrl'] = C("PIUrl.{$project}"); //供应商图片
                                $v3[0]['PUrl'] = C("PUrl.{$project}"); //供应商官网
                                $v3[0]['com_name'] = $project; //供应商官网
                                $v3[0]['com_id'] = $project_num;
                                if ($dtFlag) {
                                    $v3[0]['DT'] = $dtRes; //期货
                                } else {
                                    $v3[0]['DT'] = C("DT.{$project}") ? C("DT.{$project}") : array(); //期货
                                }
                            }   
                        }
                    }
                    
                    $finalResArr['PIUrl'] = C("PIUrl.{$project}"); //供应商图片
                    $finalResArr['PUrl'] = C("PUrl.{$project}"); //供应商官网
                    $finalResArr['com_name'] = $project; //供应商官网

                    if ($dtFlag) {
                        $dtResult = $dtRes;
                    } else {
                        $dtResult = C("DT.{$project}");
                    }
                    $finalResArr['DT'] = $dtResult; //供应商交期（含香港和大陆）

                    if ($pd) { //有数据为0，无则为1;
                        $finalResArr['status'] = 0;
                    } else {
                        $finalResArr['status'] = 1;
                    }
                    $finalResArr['PD'] = $pd; //商品结果集
                }
        }
        else
        {
            $finalResArr = '';
        }
        
        if(isset($_GET['callback']) && !empty($_GET['callback'])){
            if($project_num == 11){
                sort($v3);
            }
            return $this->returndata($v3);
        }else{
            return $this->returndata($finalResArr);
        }
        
    }
    //输出数据处理
    public function returndata($finalResArr){
        if(isset($_GET['callback']) && !empty($_GET['callback'])){
            echo $_GET['callback'].'('.json_encode($finalResArr).')';
        }else{
            echo json_encode($finalResArr);
        }
    }
    public function search($project = 'ichunt', $q = '', $limit = 100, $fuzzy=false){
        Vendor('XunS.XS');
        error_reporting(0); //注释XS引起的头部报错
        $docs = array();
        $hot  = array();
        try {
            $xs = new \XS ($project);
            $search = $xs->search;
            $search->setCharset ( 'UTF-8' );
            if (empty ( $q )) {
                // just show hot query
                $hot = $search->getHotQuery($limit);
            } else {
                // 开启模糊搜索
                if($fuzzy){
                    $search->setFuzzy (true);
                }
                // 开启同义词
                $search->setAutoSynonyms ($this->conf['synonyms']);
                // set query
                $search->setQuery ( $q );
                $search->setLimit($limit, $this->conf['offset']);
                // get the result
                $search_begin = microtime ( true );
                //$docs = $search->setFuzzy()->search ();
                $docs = $search->search ();
                $search_cost = microtime ( true ) - $search_begin;
                // get other result
                $count = $search->getLastCount ();
                $total = $search->getDbTotal ();
                // $words = $search->getRelatedQuery( $q , 18);
            }

        } catch ( XSException $e ) {
            $error = strval ( $e );
        }

        return array('search' => $search, 'docs' => $docs, 'hot' => $hot,'record_total' => $count);
    }
    
    //mouser的数据来源
    public function getMouser($k){
        //输出内容编码
        @header('Content-type: text/html; charset="utf-8');
        error_reporting(E_ALL^E_NOTICE^E_WARNING);
        $parent_id = '0a11fa6f-ddcb-4ddf-9947-e42b2f3b4723';
        $wsdl = 'http://www.mouser.com/service/searchapi.asmx?WSDL';
        //$keyword = urlencode(strtoupper($k)); //获取型号
        $keyword = strtoupper(urldecode($k)); //获取型号
        $titleArr = array( //需要屏蔽的型号
            'ANT-2.4-CW-RH-SMA',
            );
        if(in_array($keyword,$titleArr)){
            $newArray['status'] = 1;
            $res = json_encode($newArray);
            unset($newArray);
            echo $res;exit;
        }

        try {
            $client = new \SoapClient($wsdl, array(
                'soap_version' => 'SOAP_1_2',
                'trace' => true,
                "exceptions" => 0 ,
                'encoding' => 'UTF-8',
                'user_agent' => 'PHPSoap',
            ));
            $header = new \SoapHeader('http://api.mouser.com/service', 'MouserHeader', array(
                'AccountInfo' => array('PartnerID' => $parent_id))
            );
            $client->__setSoapHeaders($header);

            $params = array('keyword' => $keyword, 'records'=> 200, 'startingRecord'=> 50, 'searchOptions' => 'None');

            $data = $client->SearchByKeyword($params);
        } catch (Exception $e) {
            //print $e->getMessage();
        exit();
        }
        $array = json_decode(json_encode($data),TRUE);
        $goods_sn_arr = array();//记录数据的型号
        $goods_list = array();//记录数据列表
        $hhs_arr = array();//临时数组
        if(is_array($array) && count($array)) {
            $trueArray = $array['SearchByKeywordResult']['Parts']['MouserPart'];
            $newArray = [];
            if(is_array($trueArray) && count($trueArray)){ //有结果
                $newArray['status'] = 0;
                $pd = array();
                $temp_data = array();//循环使用
                if(isset($trueArray['Availability']) && is_string($trueArray['Availability'])){ //只有一个结果
                    $temp_data[0] = $trueArray;//为了下面的循环做的操作
                }else{
                    $temp_data = $trueArray;
                }
                foreach ($temp_data as $key => $value) {
                    $hhs_arr[$value['MouserPartNumber']] = $this->handleMouserData($value);
                    $pd[$key]['goods_name'] = $value['ManufacturerPartNumber'] ? $value['ManufacturerPartNumber'] : '' ; //型号
                    $pd[$key]['goods_name_temp'] = str_ireplace($keyword, "<b class='f-red'>".$keyword."</b>",$pd[$key]['goods_name']);
                    $pd[$key]['brand_name'] = $value['Manufacturer'] ? $value['Manufacturer'] : '' ; //制造商或品牌
                    $pd[$key]['desc'] = $value['Description'] ? $value['Description'] : '' ; //描述
                    $pd[$key]['goods_sn'] = $value['MouserPartNumber'] ? $value['MouserPartNumber'] : '' ; //商品唯一标识
                    $pd[$key]['pn'] = 'mouser' ; //供应商
                    $pd[$key]['docurl'] = $value['DataSheetUrl'] ? $value['DataSheetUrl'] : '' ; //pdf
                    $pd[$key]['url'] = $value['ProductDetailUrl'] ? $value['ProductDetailUrl'] : '' ; //来自网站url
                    $pd[$key]['img'] = $value['ImagePath'] ? $value['ImagePath'] : '' ; //图片路径
                    $pd[$key]['cat'] = $value['Category'] ? $value['Category'] : '' ; //分类
                    //记录商品名称
                    if(!empty($pd[$key]['goods_sn'])){
                        $goods_sn_arr[] = $pd[$key]['goods_sn'];
                    }
                    $temp = $value['Availability'];
                    $temp = explode(" ",$temp);
                    if(strval($temp[1]) == '有庫存'){
                        $pd[$key]['stock'][1] = $temp[0]; //库存
                    }else{
                        $pd[$key]['stock'][1] = 0; //库存
                    }
                    $pd[$key]['stock'][0] = $value['Min']; //Moq
                    if(isset($_GET['kNums']) && intval($_GET['kNums'])>intval($pd[$key]['stock'][1])){
                        continue;
                    }
                    $pd[$key]['increment'] = $value['Mult'];
                    //价格梯度
                    $tieredArr = array();
                    $price_arr = array();
                    if(isset($value['PriceBreaks']['Pricebreaks']) && count($value['PriceBreaks']['Pricebreaks'])){
                        $xishu = C('PRICE_MULTI.mouser');
                        $priceArr = $value['PriceBreaks']['Pricebreaks'];
                        $temp_price_data = array();
                        if(isset($priceArr['Currency']) && is_string($priceArr['Currency'])){ //只有一个阶梯价
                            $temp_price_data[0] = $priceArr;
                        }else{ //多个阶梯价
                            $temp_price_data = $priceArr;
                        }
                        foreach ($temp_price_data as $k => $v) {
                            $tieredArr[$k][0] = $v['Quantity'];//价格梯度数量
                            $usdPrice = $v['Price'];
                            if(strstr($usdPrice,'$')){
                                $usdPrice = ltrim($usdPrice,'$');
                                $usdPrice = str_replace( ',', '', $usdPrice );
                                $usdPrice = floatval($usdPrice);
                            }
                            $tieredArr[$k][2] = number_format(trim($xishu['cn']) * $usdPrice,4,'.','');//价格梯度人民币
                            $tieredArr[$k][3] = number_format(trim($xishu['hk']) * $usdPrice,4,'.','');//价格梯度美元
                            $price_arr[$k]['purchases'] = $v['Quantity'];//价格梯度数量
                            $price_arr[$k]['price'] = $usdPrice;
                        }
                    } //if end
                    $pd[$key]['tiered'] = $tieredArr;
                    $pd[$key]['hhs_price'] = $price_arr;
                    $goods_list[$pd[$key]['goods_sn']] = $pd[$key];
                }
            } else { //无结果
                $newArray['status'] = 1;
            }
        } else {
            $newArray['status'] = 1;
        }
        $res = $this->updateMonAndMysql($goods_list,$goods_sn_arr,$hhs_arr);
        if($res){
            $newArray['PD'] = $res;
        }else{
            $newArray['PD'] = array();
        }
        
        return $newArray;
    }
    //处理数据传给基石系统接口
    public function handleMouserData($data){
        if(empty($data)){
            return array();
        }
        $arr = array();
        $arr['goods_sn'] = $data['MouserPartNumber'] ? $data['MouserPartNumber'] : '' ; //商品唯一标识
        $arr['goods_name'] = $data['ManufacturerPartNumber'] ? $data['ManufacturerPartNumber'] : '' ; //型号
        $arr['brand_name'] = $data['Manufacturer'] ? $data['Manufacturer'] : '' ; //制造商或品牌
        $arr['spu_brief'] = $data['Description'] ? $data['Description'] : '' ; //描述
        $arr['images'] = $data['ImagePath'] ? $data['ImagePath'] : '' ; //商品图片
        $arr['pdf'] = $data['DataSheetUrl'] ? $data['DataSheetUrl'] : '' ; //pdf
        $arr['url'] = $data['ProductDetailUrl'] ? $data['ProductDetailUrl'] : '' ; //来自网站url
        $arr['cat'] = $data['Category'] ? $data['Category'] : '' ; //分类
        $temp = $data['Availability'];
        $temp = explode(" ",$temp);
        if(strval($temp[1]) == '有庫存'){
            $arr['stock'] = $temp[0]; //库存
        }else{
            $arr['stock'] = 0; //库存
        }
        $arr['moq'] = $data['Min']; //Moq
        $arr['mpq'] = $data['Mult'];
        //价格梯度
        $price_arr = array();
        if(isset($data['PriceBreaks']['Pricebreaks']) && count($data['PriceBreaks']['Pricebreaks'])){
            $xishu = C('PRICE_MULTI.mouser');
            $priceArr = $data['PriceBreaks']['Pricebreaks'];
            $temp_price_data = array();
            if(isset($priceArr['Currency']) && is_string($priceArr['Currency'])){ //只有一个阶梯价
                $temp_price_data[0] = $priceArr;
            }else{ //多个阶梯价
                $temp_price_data = $priceArr;
            }
            foreach ($temp_price_data as $k => $v) {
                $usdPrice = $v['Price'];
                if(strstr($usdPrice,'$')){
                    $usdPrice = ltrim($usdPrice,'$');
                    $usdPrice = str_replace( ',', '', $usdPrice );
                    $usdPrice = floatval($usdPrice);
                }
                $price_arr[$k]['purchases'] = $v['Quantity'];//价格梯度数量
                $price_arr[$k]['price_cn'] = 0;
                $price_arr[$k]['price_us'] = $usdPrice;
            }
        } //if end
        $arr['ladder_price'] = json_encode($price_arr);
        return $arr;
    }
    //处理本地数据，以goods_name在mongo里面查，如果存在则判断其更新时间，超过24小时则更新其价格，不存在则先在mysql插入数据，并拿到goods_id，再插入mongodb
    public function updateMonAndMysql($goods_list,$goods_sn_arr,$temp_data){
        if(empty($goods_list) || empty($goods_sn_arr)){
            return false;
        }
        $mon= new MonModel();
        $redis = new \Think\Cache\Driver\Redis();  
        $res = $mon->select('mouser',array('goods_sn'=>array('$in'=>$goods_sn_arr)),array('goods_sn','time','goods_id','brand_name','tiered'),array()); //根据数据表及条件查询结果
        $update_data = array();
        $insert_data = array();//将要插入的数据
        if(!empty($res)){
            foreach($res as $k=>$v){
                
                if(isset($goods_list[$v['goods_sn']]) && !empty($goods_list[$v['goods_sn']])){
                    $temp = time()-$v['time']-86400;
                    //if($temp>0 || isset($v['tiered']['Quantity'])){
                    if(true){
                        $sku_arr = $mon->fetchRow('sku',array('old_goods_id'=>floatval($v['goods_id'])));
                        $redis_data = $redis->hGet('sku',$sku_arr['goods_id']);
                        $redis_arr = json_decode($redis_data,true);
                        $arr = array();
                        $n = $goods_list[$v['goods_sn']]['stock'][0] ? intval($goods_list[$v['goods_sn']]['stock'][0]) :0;
                        $n1 = $goods_list[$v['goods_sn']]['stock'][1] ? intval($goods_list[$v['goods_sn']]['stock'][1]) :0;
                        $arr['stock'] = array($n,$n1);
                        $redis_arr['moq'] = $n;
                        $redis_arr['stock'] = $n1;
                        $price_temp = array();
                        $redis_arr['ladder_price'] = array();
                        if(!empty($goods_list[$v['goods_sn']]['hhs_price'])){
                            foreach($goods_list[$v['goods_sn']]['hhs_price'] as $kp=>$vp){
                                $price_temp[$kp][0] = $vp['purchases'];
                                $price_temp[$kp][1] = $vp['price'];
                                $redis_arr['ladder_price'][$kp]['purchases'] = $vp['purchases'];
                                $redis_arr['ladder_price'][$kp]['price_cn'] = 0;
                                $redis_arr['ladder_price'][$kp]['price_us'] = $vp['price'];
                            }
                        }
                        $arr['tiered'] = $price_temp;
                        $arr['brand_name'] = $goods_list[$v['goods_sn']]['brand_name'];
                        $arr['time'] = time();
                        $redis_arr['update_time'] = time();
                        $re = $mon->update('mouser',$arr,array('goods_id'=>$v['goods_id']));
                        $redis->hSet('sku',$sku_arr['goods_id'], json_encode($redis_arr));
                    }
                    $goods_list[$v['goods_sn']]['goods_id'] = $v['goods_id'];
                    unset($goods_list[$v['goods_sn']]['hhs_price']);
                    $update_data[] = $goods_list[$v['goods_sn']];
                    unset($goods_list[$v['goods_sn']]);
                }
            }
            
        }
        $insert_data = $goods_list;
        $insert_res = array();
        if(!empty($insert_data)){
            $insert_res = $this->insertMonAndMysql($insert_data,$temp_data);
        }
        if($insert_res){
            $re_data = array_merge($insert_res,$update_data);
        }else{
            $re_data = $update_data;
        }
        return $re_data;
        
    }
    //调用基石系统接口，并处理相关
    public function insertMonAndMysql($insert_data,$temp_data){return array();
        if(empty($insert_data) || empty($temp_data)){
            return array();
        }
        $arr = array();
        foreach ($insert_data as $k=>$v){
            $arr[] = $temp_data[$k];
        }
        $sign = MD5(http_build_query($arr).C('API_KEY'));
        $post['data'] = $arr;
        $post['sign'] = $sign;
        $res = http_post(C('MOUSER_API'),http_build_query($post));
        return array();
        /*$data = json_decode($res,true);
        $arr_re = array();
        if($data['errcode']==0){
            if(count($data['data']['sku'])>0){
                foreach($data['data']['sku'] as $k=>$v){
                    $insert_data[$v]['goods_id'] = $k; 
                    $arr_re[] = $insert_data[$v];
                }
            }
        }
        return $arr_re;*/
    }

    public function getCompanyName($id){
        $arr = explode('matches', C("DB_CONFIG1"));
        $con = 'mysql://huntdbuser:mLssy2@@!!@$#yy@172.18.137.21:3306/hunt2016#utf8';
        $m = M('user_company', 'lie_', $con);
        //$sql = "select * from lie_user_company limit 3";
        $res = $m->field('com_name')->where(array('com_id'=>$id))->find();
        return $res['com_name'];
    }

    //删除数据
    public function test(){     exit;
        $arr = array('goods_sn'=>'585-ALD1103PBL');
        $M= new MonModel();
        $res = $M->select('mouser',$arr,array(),array()); //根据数据表及条件查询结果
        dump($res);
        $data['stock'] = array('1','2');
        $re = $M->update('mouser',$data,$arr);dump($re);exit;
        //$res = $M->select('mouser',$arr,array('goods_id','goods_name','brand_name','time'),array()); //根据数据表及条件查询结果
        //dump($res);
    }
    public function ddd(){
        $M= new MonModel();
        $res = $M->select('mouser',array('time'=>array('$ne'=>0)),array('goods_id'),array()); //根据数据表及条件查询结果
        $arr = array();
        $str = '';
        $model = M()->db(2,"DB_CONFIG2");
        $n = 0;echo count($res);exit;
        foreach($res as $k=>$v){
            $arr[] = $v['goods_id'];
            $str .= ','.$v['goods_id'];
//            $price_arr = array();
//            $price_arr['goods_id'] = $v['goods_id'];
//            $sqlNum = substr($v['goods_id'],-1);
//            $table = 'lie_goods_price_'.$sqlNum;
//            $r = $model->table($table)->where($price_arr)->delete();
//            
//            $n += $r;
        }
//        echo $n;
//        exit;
        
//        $where = array();
//        $where['goods_id'] = array('in', trim($str));
//        $goods_id = $model->table('lie_goods')->where($where)->delete();
//        echo $goods_id;exit;
        
        $res = $M->delete('mouser',array('goods_id'=>array('$in'=>$arr))); //根据数据表及条件查询结果
        echo $res;exit;

        dump($res);
    }
    
}