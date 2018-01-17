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
                        $resArr[$k]['goods_name_temp'] = str_ireplace($q, "<b class='f-blue'>".strtoupper($q)."</b>",$resArr[$k]['goods_name']);
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
                    $param = array('goods_id'=>array('$in'=>$goodsArr));
                    $res_list = $M->select($project,$param);
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
                            $resArr['goods_name_temp'] = str_ireplace($q, "<b class='f-blue'>".strtoupper($q)."</b>",$resArr['goods_name']);
                            if($project_num == 11){
                                //$resArr['goods_name'] = str_ireplace($q, "<b class='fc_font'>".strtoupper($q)."</b>",$resArr['goods_name']);
                                $v3[$resArr['brand_name']]['data'][] = $resArr;
                                $v3[$resArr['brand_name']]['PIUrl'] = C("PIUrl.{$resArr['brand_name']}"); //供应商图片
                                $v3[$resArr['brand_name']]['PUrl'] = C("PUrl.{$resArr['brand_name']}"); //供应商官网
                                $v3[$resArr['brand_name']]['com_name'] = $resArr['brand_name']; //供应商官网
                                $v3[$resArr['brand_name']]['com_id'] = C("SUP11.{$resArr['brand_name']}"); //供应商官网
                                if ($dtFlag) {
                                    $v3[$resArr['brand_name']]['DT'] = $dtRes; //期货
                                } else {
                                    $v3[$resArr['brand_name']]['DT'] = C("DT.company") ? C("DT.company") : array(); //期货
                                }

                            }else{
                                //$resArr['goods_name'] = str_ireplace($q, "<b class='fc_font'>".strtoupper($q)."</b>",$resArr['goods_name']);
                                $v3[0]['data'][] = $resArr;
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
        $keyword = urlencode(strtoupper($k)); //获取型号
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
        if(is_array($array) && count($array)) {
            $trueArray = $array['SearchByKeywordResult']['Parts']['MouserPart'];
            $newArray = [];
            if(is_array($trueArray) && count($trueArray)){ //有结果
                $newArray['status'] = 0;
                $pd = array();
                if(isset($trueArray['Availability']) && is_string($trueArray['Availability'])){ //只有一个结果
                    $pdTemp['goods_name'] = $trueArray['ManufacturerPartNumber'] ? $trueArray['ManufacturerPartNumber'] : '' ; //型号
                    $pdTemp['goods_name_temp'] = str_ireplace($keyword, "<b class='f-blue'>".$keyword."</b>",$pdTemp['goods_name']);
                    $pdTemp['brand_name'] = $trueArray['Manufacturer'] ? $trueArray['Manufacturer'] : '' ; //制造商或品牌
                    $pdTemp['desc'] = $trueArray['Description'] ? $trueArray['Description'] : '' ; //描述
                    $pdTemp['goods_sn'] = $trueArray['MouserPartNumber'] ? $trueArray['MouserPartNumber'] : '' ; //商品唯一标识
                    $pdTemp['pn'] = 'mouser' ; //供应商
                    $pdTemp['docurl'] = $trueArray['DataSheetUrl'] ? $trueArray['DataSheetUrl'] : '' ; //描述
                    $pdTemp['url'] = $trueArray['ProductDetailUrl'] ? $trueArray['ProductDetailUrl'] : '' ; //来自网站url
                    $pdTemp['img'] = $trueArray['ImagePath'] ? $trueArray['ImagePath'] : '' ; //图片路径
                    $pdTemp['cat'] = $trueArray['Category'] ? $trueArray['Category'] : '' ; //分类
                    //记录商品名称
                    if(!empty($pdTemp['goods_sn'])){
                        $goods_sn_arr[] = $pdTemp['goods_sn'];
                    }
                    $pdTemp['increment'] = $trueArray['Mult'];//递增量
                    $temp = $trueArray['Availability'];
                    $temp = explode(" ",$temp);
                    if(strval($temp[1]) == '有庫存'){
                        $pdTemp['stock'][1] = $temp[0]; //库存
                    }else{
                        $pdTemp['stock'][1] = 0; //库存
                    }

                    $pdTemp['stock'][0] = $trueArray['Min']; //Moq

                    //价格梯度
                    $tieredArr = array();
                    $price_arr = array();
                    if(isset($trueArray['PriceBreaks']['Pricebreaks']) && count($trueArray['PriceBreaks']['Pricebreaks'])){
                        $priceArr = $trueArray['PriceBreaks']['Pricebreaks'];
                        $xishu = C('PRICE_MULTI.mouser');
                        if(isset($priceArr['Currency']) && is_string($priceArr['Currency'])){ //只有一个阶梯价
                            $tieredArrTemp[0] = $priceArr['Quantity'];
                            $usdPrice = $priceArr['Price'];
                            if(strstr($usdPrice,'$')){
                                $usdPrice = ltrim($usdPrice,'$');
                                $usdPrice = str_replace( ',', '', $usdPrice );
                                $usdPrice = floatval($usdPrice);
                            }
                            $tieredArrTemp[2] = number_format(trim($xishu['cn']) * $usdPrice,4,'.','');
                            $tieredArrTemp[3] = number_format(trim($xishu['hk']) * $usdPrice,4,'.','');
                            $price_arr[0]['purchases'] = $priceArr['Quantity'];
                            $price_arr[0]['price'] = $usdPrice;
                            $tieredArr[] = $tieredArrTemp;
                        }else{ //多个阶梯价
                            foreach ($priceArr as $k => $v) {
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
                            } //foreach end
                        }
                    } //if end
                    $pdTemp['tiered'] = $tieredArr;
                    $pdTemp['hhs_price'] = $price_arr;
                    $goods_list[$pdTemp['goods_sn']] = $pdTemp;
//                    $newArray['PD'][] = $pdTemp;
//                    unset($pdTemp);

                }else{
                    foreach ($trueArray as $key => $value) {

                        $pd[$key]['goods_name'] = $value['ManufacturerPartNumber'] ? $value['ManufacturerPartNumber'] : '' ; //型号
                        $pd[$key]['goods_name_temp'] = str_ireplace($keyword, "<b class='f-blue'>".$keyword."</b>",$pd[$key]['goods_name']);
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
                        $pd[$key]['increment'] = $value['Mult'];
                        //价格梯度
                        $tieredArr = array();
                        $price_arr = array();
                        if(isset($value['PriceBreaks']['Pricebreaks']) && count($value['PriceBreaks']['Pricebreaks'])){
                            $xishu = C('PRICE_MULTI.mouser');
                            $priceArr = $value['PriceBreaks']['Pricebreaks'];
                            if(isset($priceArr['Currency']) && is_string($priceArr['Currency'])){ //只有一个阶梯价
                                $tieredArrTemp[0] = $priceArr['Quantity'];
                                $usdPrice = $priceArr['Price'];
                                if(strstr($usdPrice,'$')){
                                    $usdPrice = ltrim($usdPrice,'$');
                                    $usdPrice = str_replace( ',', '', $usdPrice );
                                    $usdPrice = floatval($usdPrice);
                                }
                                $tieredArrTemp[2] = number_format(trim($xishu['cn']) * $usdPrice,4,'.','');
                                $tieredArrTemp[3] = number_format(trim($xishu['hk']) * $usdPrice,4,'.','');
                                $price_arr[0]['purchases'] = $priceArr['Quantity'];
                                $price_arr[0]['price'] = $usdPrice;
                                $tieredArr[] = $tieredArrTemp;
                            }else{ //多个阶梯价
                                foreach ($priceArr as $k => $v) {
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
                                } //foreach end
                            }
                        } //if end
                        $pd[$key]['tiered'] = $tieredArr;
                        $pd[$key]['hhs_price'] = $price_arr;
                        $goods_list[$pd[$key]['goods_sn']] = $pd[$key];
                    } //foreach end

//                    $newArray['PD'] = $pd;
//                    unset($pd);
                    
                }


            } else { //无结果
                $newArray['status'] = 1;
            }
        } else {
            $newArray['status'] = 1;
        }
        $res = $this->updateMonAndMysql($goods_list,$goods_sn_arr);
        if($res){
            $newArray['PD'] = $res;
        }else{
            $newArray['PD'] = array();
        }
        
        return $newArray;
    }
    //处理本地数据，以goods_name在mongo里面查，如果存在则判断其更新时间，超过24小时则更新其价格，不存在则先在mysql插入数据，并拿到goods_id，再插入mongodb
    public function updateMonAndMysql($goods_list,$goods_sn_arr){
        if(empty($goods_list) || empty($goods_sn_arr)){
            return false;
        }
        $mon= new MonModel();
        $res = $mon->select('mouser',array('goods_sn'=>array('$in'=>$goods_sn_arr)),array('goods_sn','time','goods_id'),array()); //根据数据表及条件查询结果
        $update_data = array();
        $insert_data = array();//将要插入的数据
        if(!empty($res)){
            foreach($res as $k=>$v){
                if(isset($goods_list[$v['goods_sn']]) && !empty($goods_list[$v['goods_sn']])){
                    $temp = time()-$v['time']-86400;
                    if($temp>0){
                        $arr = array();
                        $arr['stock'] = $goods_list[$v['goods_sn']]['stock'];
                        $price_temp = array();
                        if(!empty($goods_list[$v['goods_sn']]['hhs_price'])){
                            foreach($goods_list[$v['goods_sn']]['hhs_price'] as $kp=>$vp){
                                $price_temp[$kp][0] = $vp['purchases'];
                                $price_temp[$kp][1] = $vp['price'];
                            }
                        }
                        $arr['tiered'] = $price_temp;
                        $arr['time'] = time();
                        $re = $mon->update('mouser',$arr,array('goods_id'=>$v['goods_id']));
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
            $insert_res = $this->insertMonAndMysql($insert_data);
        }
        if($insert_res){
            $re_data = array_merge($insert_res,$update_data);
        }else{
            $re_data = $update_data;
        }
        return $re_data;
        
    }
    //如果不存在mouser的数据，则往数据库与mongodb插入数据
    public function insertMonAndMysql($data){
        if(empty($data)){
            return false;
        }
        $res_data = array();
        $model = M()->db(2,"DB_CONFIG2");
        foreach ($data as $k=>$v){
            $arr = array();
            $sql = 'select cat_id from lie_category where cat_name='.$v['cat'].' limit 1';
            //$sql = 'select cat_id from ic_category limit 1';
//            $sql = 'show tables';
            $cat_res = $model->query($sql);
            $b_id = 0;
            $sql = 'select brand_id from lie_brand where brand_name='.$v['brand_name'].' limit 1';
            $brand_res = $model->query($sql);
            if(empty($brand_res)){
                $letter = substr($v['brand_name'], 0,1);
                $b_arr = array();
                $b_arr['brand_name'] = $v['brand_name'];
                $b_arr['first_letter'] = $letter;
                $b_res = $model->table('lie_brand')->add($b_arr);
                $b_id = $b_res;
            }else{
                $b_id = $brand_res[0]['brand_id'];
            }
            if($cat_res){
                $arr['cat_id'] = $cat_res[0]['cat_id'];
                $arr['goods_sn'] = $v['goods_sn'];
                $arr['goods_name'] = $v['goods_name'];
                $arr['brand_id'] = $b_id;
                $arr['provider_name'] = $v['brand_name'];
                $arr['pdf_url'] = $v['docurl'];
                $arr['goods_number'] = $v['stock'][1];
                $arr['min_buynum'] = $v['stock'][0];
                $arr['goods_brief'] = $v['desc'];
                $arr['goods_thumb'] = $v['img'];
                $arr['goods_img'] = $v['img'];
                $arr['add_time'] = time();
                $arr['last_update'] = time();
                $arr['site_url'] = $v['url'];
                $arr['increment'] = $v['increment'];
                $arr['MOQ'] = $v['stock'][0];
            }else{
                $arr['goods_name_style'] = $v['cat'];
                $arr['goods_sn'] = $v['goods_sn'];
                $arr['goods_name'] = $v['goods_name'];
                $arr['brand_id'] = $b_id;
                $arr['provider_name'] = $v['brand_name'];
                $arr['pdf_url'] = $v['docurl'];
                $arr['goods_number'] = $v['stock'][1];
                $arr['min_buynum'] = $v['stock'][0];
                $arr['goods_brief'] = $v['desc'];
                $arr['goods_thumb'] = $v['img'];
                $arr['goods_img'] = $v['img'];
                $arr['add_time'] = time();
                $arr['last_update'] = time();
                $arr['site_url'] = $v['url'];
                $arr['increment'] = $v['increment'];
                $arr['MOQ'] = $v['stock'][0];
            }
            $goods_id = $model->table('lie_goods')->add($arr);
            $goods_id = ceil($goods_id);
            if($goods_id){
                $data[$k]['goods_id'] = $goods_id;
                $arr_price = array();
                //获取价格梯度
                $sqlNum = substr($goods_id,-1);//根据goods_id 最后一位确定价格梯度的数据库编号
                $price_arr = array();
                $price_arr['goods_id'] = $goods_id;
                $price_arr['price'] = json_encode($v['hhs_price']);
                $table = 'lie_goods_price_'.$sqlNum;
                //$sql = 'insert into lie_goods_price_'.$sqlNum.' values('.$goods_id.',"'.json_encode($v['hhs_price']).'")';
                $r = $model->table($table)->add($price_arr);
                $M= new MonModel();
                $arr = array();
                $arr['goods_id'] = $goods_id;
                $arr['goods_sn'] = $v['goods_sn'];
                $arr['goods_name'] = $v['goods_name'];
                $arr['brand_name'] = $v['brand_name'];
                $arr['dt'] = array();
                $arr['desc'] = $v['desc'];
                $arr['docurl'] = $v['docurl'];
                $arr['pn'] = $v['pn'];
                $start = $v['stock'][0] ? intval($v['stock'][0]) : 0;
                $stock = $v['stock'][1] ? intval($v['stock'][1]) : 0;
                $arr['stock'] = array($start,$stock);
                $price_temp = array();
                if(!empty($v['hhs_price'])){
                    foreach($v['hhs_price'] as $kp=>$vp){
                        $price_temp[$kp][0] = $vp['purchases'];
                        $price_temp[$kp][1] = $vp['price'];
                    }
                }
                $arr['tiered'] = $price_temp;
                $arr['increment'] = $v['increment']? intval($v['increment']) : 0;
                $arr['time'] = time();
                $arr['url'] = $v['url'];
                $arr['isapi'] = 1;
                $res = $M->insert('mouser',$arr);
                unset($data[$k]['hhs_price']);
                $res_data[] = $data[$k];
            }else{
                return false;
            }
        }
        return $res_data;
    }

        public function test(){
        $M= new MonModel();
        $res = $M->select('mouser',array('goods_id'=>25003155660),array(),array()); //根据数据表及条件查询结果

        dump($res);exit;

        //dump($res);exit;
        $arr = array();
        $arr['goods_id'] = '333';
        $arr['goods_sn'] = 'hellol88888';
        $arr['goods_name'] = '333goods_name';
        $arr['brand_name'] = '333brand_name';
        $arr['dt'] = array();
        $arr['desc'] = '333desc';
        $arr['docurl'] = '333docurl';
        $arr['pn'] = '333pn';
        $arr['stock'] = array(0=>123,1=>123);
        $arr['hhs_price'] = array(0=>array('purchases'=>30,'price'=>0.99),1=>array('purchases'=>160,'price'=>90));
        $arr['increment'] = '333';
        $arr['time'] = '3311113';
        $arr['url'] = '333url';
        $arr['isapi'] = '333';
        $arr['img'] = '333url';
        $arr['cat'] = 'kkkk';
        $temp = array();
        $temp['hellol88888'] = $arr;
        $arr = array();
        $arr['goods_id'] = '333444';
        $arr['goods_sn'] = 'hellol99999';
        $arr['goods_name'] = '333goods_name';
        $arr['brand_name'] = '333brand_name';
        $arr['dt'] = array();
        $arr['desc'] = '333desc';
        $arr['docurl'] = '333333ffff';
        $arr['pn'] = '333pn';
        $arr['stock'] = array(0=>888,1=>888);
        $arr['hhs_price'] = array(0=>array('purchases'=>88,'price'=>0.8888),1=>array('purchases'=>160,'price'=>90));
        $arr['increment'] = '333';
        $arr['time'] = '3311113';
        $arr['url'] = '333url';
        $arr['isapi'] = '333';
        $arr['img'] = '333url';
        $arr['cat'] = 'jjjj';
        $temp['hellol99999'] = $arr;
//        $arr = array();
//        $arr['stock'] = array(0=>90,1=>90);
//        $arr['tiered'] = array(0=>array(0=>30,1=>0.99),1=>array(0=>160,1=>90));
        $res = $this->updateMonAndMysql($temp,array('hellol88888','hellol99999'));
        //$res = $M->insert('mouser',$arr);
        //$res = $M->update('mouser',$arr,array('goods_id'=>$res[0]['goods_id']));
        dump($res);
    }
    //删除数据
    
    public function ddd(){
        $M= new MonModel();
        $res = $M->select('mouser',array('time'=>array('$ne'=>0)),array('goods_id'),array()); //根据数据表及条件查询结果
        echo count($res);exit;

        dump($res);
    }
    
}