<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Search\Controller;
require 'vendor/autoload.php';
require 'qqbot/CoolQ.class.php';
use Elasticsearch\ClientBuilder;
use Think\Controller;
use Search\Model\MonModel;
/**
 * Description of AutoRunController
 *
 * @author Administrator
 */
class AutoRunController extends Controller {
    
    public function _initialize()
        {
            
            if(!defined('JSON_PRESERVE_ZERO_FRACTION'))
            {
                define('JSON_PRESERVE_ZERO_FRACTION', 1024);
            }
            //Vendor('vendor.autoload');
            $hosts = [
                //'172.18.137.29:9211',         // IP + Port
                C('ES_IP_PORT'),         // IP + Port
            ];
            $this->client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                                ->setHosts($hosts)      // Set the hosts
                                ->build();
        }
    
    public function run_sku(){
        $redis = new \Think\Cache\Driver\Redis();
        $i = 0;
        while (($res = $redis->lpop('update_list_sku')) && $i<1000){
            $this->updateSku($res);
            $i++;
            usleep(10);
        }
        echo 'ok';
    }
    public function run_spu(){
        $redis = new \Think\Cache\Driver\Redis();
        $i = 0;
        while (($res = $redis->lpop('update_list_spu')) && $i<1000){
            $this->updateSpu($res);
            $i++;
            usleep(10);
        }
        echo 'ok';
    }
    //sku数据更新
    public function updateSku($ids){
        //$ids = $_POST['ids'];
        //file_put_contents('./update_ids.txt', $ids.PHP_EOL,FILE_APPEND);
        if(empty($ids)){
            $this->return_date(1,'id为空',1);
        }
        $arr = explode(',',$ids );
        $con = C('MYSQL_POWER').'/liexin_sku_';
        $table = 'sku_';
        foreach ($arr as $v){
            $a = substr($v, -2,1);
            $b = substr($v, -1);
            $m = M($table.$b, 'lie_', $con.$a);
            $datas = $m->field('*')->where(array('goods_id'=>$v+0))->limit('0,1')->select();
            if(empty($datas)){
                $this->return_date(1,'数据信息有误-'.$ids,1);
            }
            $arr0 = array(0=>'ziying',2=>'zhuanmai');//自营与专卖
            $arr1 = C('SUPERLIER_ALL');//联营
//            if($datas[0]['goods_type']!=1){
//                $res = $this->souOther($datas,$arr0[$datas[0]['goods_type']]);
//            }else{
                $res = $this->souOther($datas,$arr1[$datas[0]['supplier_id']]);
//            }
        }
        //$this->return_date(0,'',1);
    }
    //sku数据更新
    public function updateSpu($ids){

        //$ids = $_POST['ids'];
        if(empty($ids)){
            $this->return_date(1,'id为空',1);
        }
        $arr = explode(',',$ids );
        $con = C('MYSQL_POWER').'/liexin_spu';
        $table = 'spu_';
        foreach ($arr as $v){
            $b = substr($v, -1);
            $m = M($table.$b, 'lie_', $con);
            $datas = $m->field('*')->where(array('spu_id'=>$v))->limit('0,1')->select();
            if(empty($datas)){
                $this->return_date(1,'id为空',1);
            }
            $res = $this->souSpuOther($datas,'lie_spu');
        }
        //$this->return_date(0,'',1);
    }
    
    public function return_date($code=0,$msg='',$data=array()){
        if(empty($data)){
            $code = 1;
        }
        $arr = array(
            'error_code'=>$code,
            'error_msg'=>$msg,
            'data'=>$data,
        );
        echo json_encode($arr);exit;
    }
    
    //处理spu的数据信息
    public function souSpuOther($datas,$es_index){
        if(count($datas)<=0){
            return array();
        }
        $num = count($datas);
        $params = array('body'=>array());
        $con = C('MYSQL_POWER').'/liexin_spu';
        $brand = M('brand', 'lie_', $con);
        foreach ($datas as $k=>$v){
            $brand_data = $brand->field('brand_name')->where(array('brand_id'=>$v['brand_id']+0))->find();
            $arr = array(
                "spu_id"=>$v['spu_id']+0,
                "spu_name"=>$v['spu_name'],
                "brand_id"=>$v['brand_id']+0,
                "brand_name"=>$brand_data['brand_name'],
                "status"=>$v['status']+0,
                "class_id1"=>$v['class_id1']+0,
                "class_id2"=>$v['class_id2']+0,
                "class_id3"=>$v['class_id3']+0,
                "sale_time"=>$v['sale_time']+0,
                "create_time"=>$v['create_time']+0,
                "update_time"=>$v['update_time']+0,
                "sort"=>1,
            );
            $params['body'][] = [
                'index' => [
                    '_index' => $es_index,
                    '_type' => 'spu',
                    '_id' => $v['spu_id']+0
                ]
            ];

            $params['body'][] = $arr;
        }
        unset($responses);
        $responses = $this->client->bulk($params);
        $this->es_log($responses,2);
        return $responses;
    }
    
    //根据sku查询spu等信息
    public function souOther($datas,$es_index){//dump($datas);exit;
        if(count($datas)<=0){
            return array();
        }
        $num = count($datas);
        $params = array('body'=>array());
        foreach ($datas as $k=>$v){
            $sqlNum = substr($v['spu_id'],-1);
            $con = C('MYSQL_POWER').'/liexin_spu';
            $table = 'spu_';
            $m = M($table.$sqlNum, 'lie_', $con);
            $where = array('spu_id'=>$v['spu_id']);
            $spu = $m->field('*')->where($where)->find();
            $brand = M('brand', 'lie_', $con);
            $brand_data = $brand->field('brand_name')->where(array('brand_id'=>$spu['brand_id']))->find();
            $brand = M('supplier', 'lie_', $con);
            $supplier_data = $brand->field('supplier_name')->where(array('supplier_id'=>$v['supplier_id']))->find();
            $arr = array(
                "goods_id"=>$v['goods_id']+0,
                "old_goods_id"=>$v['old_goods_id']+0,
                "goods_name"=>(!empty($v['goods_name']))?$v['goods_name']:$spu['spu_name'],
                "brand_id"=>$spu['brand_id'],  
                "brand_name"=>$brand_data['brand_name'],
                "supplier_name"=>$supplier_data['supplier_name'],
                "class_id1"=>$spu['class_id1']+0,
                "class_id2"=>$spu['class_id2']+0,
                "class_id3"=>$spu['class_id3']+0,
                "spu_id"=>$v['spu_id']+0,
                "sale_time"=>$v['sale_time']+0,
                "create_time"=>$v['create_time']+0,
                "update_time"=>$v['update_time']+0,
                "single_price"=>$v['single_price']+0,
                'stock'=>$v['stock']+0, 
                "goods_status"=>$v['goods_status']+0,
                "sort"=>1,      
                "encoded"=>$v['encoded'],
                "encap"=>$spu['encap'], 
                "delivery_place"=>1
            );//dump($arr);exit;
            $params['body'][] = [
                'index' => [
                    '_index' => $es_index,
                    '_type' => 'goods',
                    '_id' => $v['goods_id']+0
                ]
            ];

            $params['body'][] = $arr;
        }
        unset($responses);
        $responses = $this->client->bulk($params);//dump($responses);
        $this->es_log($responses,1);
        return $responses;
        
    }
    
    //记录日志
    public function es_log($data,$flag){
        if($data['errors'] != false){
            if($flag=1){
                file_put_contents('./update_sku_data_log.txt', date('Y-m-d H:i:s').json_encode($data).PHP_EOL,FILE_APPEND);
            }else{
                file_put_contents('./update_spu_data_log.txt', date('Y-m-d H:i:s').json_encode($data).PHP_EOL,FILE_APPEND);
            }
        }
    }
    
    //提供接口给自动回复机器人调用
    /*
     * @param $q 内容
     * return array 相关总条数  第一个品牌  前十个库存相加
     */
     public function ans($q){
         $index_name_all = C('SUPERLIER_ALL');//根据实际情况进行填补
         unset($index_name_all['4']);
        unset($index_name_all['2']);
         preg_match('/([^\x{4e00}-\x{9fa5}]{3,50})/u', $q,$match);
         $res = array();
         if(empty($match) || empty($match[1])){
             $res['title'] = "哎哟，{$q}是个稀缺资源，让猎单宝帮你排忧解难！";
             
             $res['content'] = "波哥为猎单宝带“盐”";
             $res['url'] = "http://www.ichunt.com/v3/ldb?ADTAG=qq.qun.auto";
             return $res;
         }
         $q = trim($match[1]);
         $info = $this->ans_data($index_name_all,$q,0,0);
         if(!empty($info)){
             $url = 'http://www.ichunt.com/s/?k='. urlencode($q);
             $res['title'] = "我找到{$info['total']}个{$q}货源，来帮我看看吧！";
             $info['stock'] = $info['stock']>0 ? $info['stock']:'--';
             $res['content'] = "品牌：{$info['brand_name']}\r\n库存数量：{$info['stock']}+";
             $res['url'] = $url;
             
         }else{
             $res['title'] = "哎哟，{$q}是个稀缺资源，让猎单宝帮你排忧解难！";
             $res['content'] = "波哥为猎单宝带“盐”";
             $res['url'] = "http://www.ichunt.com/v3/ldb?ADTAG=qq.qun.auto";
         }
         return $res;
     }
    //根据供应商名称获取，关键词、分页获取从es数据信息
    public function ans_data($project,$q,$stock,$brand_id=0){
        $params = array();
        $params['index'] = $project;//索引

        $params['type'] = 'goods';  //类型
        $params['body']['_source'] = array('goods_id',"old_goods_id",'brand_name','stock'); //返回字段
        $params['body']['query']['bool']['must'][] = array('term'=>array('goods_name'=> strtoupper($q))); //根据关键词查找
        $params['body']['query']['bool']['must'][] = array('term'=>array('goods_status'=>1)); //根据关键词查找
        if($brand_id>0){
            $params['body']['query']['bool']['must'][] = array('term'=>array('brand_id'=> $brand_id+0)); //根据品牌id查找
        }
        if($stock>0){
            $params['body']['query']['bool']['must'][] = array('range'=>array('stock'=>array('gte'=>$stock+0)));//根据库存查找
        }
      
        //页码，第一页为2页，接下来以每页5条展示
        $pagesize = 10;
        $pagecur = !empty($_REQUEST['p'])?$_REQUEST['p']:1;
        $first = $pagesize*($pagecur-1);
        if($pagecur>1){
            $pagesize = 5;
            $first = $pagesize*($pagecur-1)-2-1;
        }
        $params['body']['from'] = $first;
        $params['body']['size'] = $pagesize;  
        //dump($params);
        $es_start = microtime(true);
        $results = $this->client->search($params);
        $res = array();
        $temp = $results['hits']['hits'];
        if($results['hits']['total']>0){
            $res['total'] = strval($results['hits']['total']);
            $res['stock'] = 0;
            //$redis = new \Think\Cache\Driver\Redis();
            foreach($temp as $k=>$v){
                //$redis_data = $redis->hGet('sku',$v['_source']['goods_id']);
                if(!isset($res['brand_name']) || empty($res['brand_name'])){
                    $res['brand_name'] = $v['_source']['brand_name'];
                }
                $res['stock'] += $v['_source']['stock'];
            }
        }
        return $res;
    }
    
    //对外接口，自动回复群友
    public function ans_qq(){
        /**
        * 动态交互 设置
        * 如果 $url 不为空，视为使用动态交互功能
        */
       $url = ''; //服务器地址
       $port = 9999; //监听端口
       if($port!=80 && $url) $url .= ':'.$port;

       /**
        * 校验数据设置
        * 如果 $key 不为空，则视为使用校验数据功能
        */
       $key = ''; //校验数据所需要的密钥
       $effectTime = 30; //数据有效期

       $format = 'JSON'; //数据格式，如果使用 Key=Value 格式，请设置为 KV

       
       $CQ = new \CoolQ($url,$key,$effectTime,$format);
       
       $array = $CQ->receive(); //接收插件推送的数据
        if(!$array) exit; //没传入数据，终止运行

        switch($array['type']) {
            case 1:
                //收到私聊信息
                $qq = $array['qq'];
                $msg = $array['msg'];
                $CQ->sendPrivateMsg($qq, "收到一条消息:$msg");
                break;

            case 2:
                //收到群聊天信息
                $group = $array['group'];
                $msg = trim(preg_replace('/\[.+?\]/', '', $array['msg']));//[CQ:at,qq=1730914938] ads
                if(empty($msg) || strpos($msg,'加入本群') !== false)exit;
                $res = $this->ans($msg);
                $msg_share = $CQ->cqShare($res['url'],$res['title'],$res['content'],'http://www.ichunt.com/v3/dist/res/home/images/logo.png');
                $CQ->sendGroupMsg($group, $msg_share);

                /*if($msg == '你好') {
                    $CQ->sendGroupMsg($group, "你好\r\n我是小娜");
                    $CQ->sendGroupMsg($group,'你是我的闺蜜Siri吗');
                    $CQ->sendGroupMsg($group,$CQ->cqAt($array['qq']));
                }
                //$CQ->sendGroupMsg($group,"本次消息结构体：\r\n".print_r($array,true));
                //$CQ->sendGroupMsg($group,"本次消息(仅文本)：\r\n$msg");
                if($msg == '更新群成员信息'){
                    $array = $CQ->getGroupMemberList($array['group']);
                }*/
                break;

            case 4:
                //收到讨论组信息
                $group = $array['group'];
                $msg = $array['msg'];
                $CQ->sendDiscussMsg($group, "FromHttpSocket:$msg");
                break;

            case 11:
                //有群成员上传文件
                $group = $array['group'];
                $file = $array['fileInfo'];
                $msg = $CQ->cqAt($array['qq']).'上传了文件';
                $msg .= "\r\n";
                $msg .= '文件名：'.$file['name'];
                $CQ->sendGroupMsg($group, $msg);
                break;

            case 103:
                //群成员增加
                $group = $array['group'];
                $qq = $array['beingOperateQQ'];
                $groupInfo = $CQ->getGroupInfo($group);
                $groupName = (!$groupInfo['status']) ? $groupInfo['result']['gName'] : '本群';
                $msg = '欢迎'.$CQ->cqAt($qq).'加入'.$groupName;
                $CQ->sendGroupMsg($group, $msg);
                break;
        }
        unset($CQ);//释放连接
    }
    /*
     * rabbitmq 消费队列，主要记录搜索日志
     */
    public function consumSearchLog(){
        $con = C('RABBITMQ_CONFIG');
        $con['queue_name'] = "hhs_search_log";
        $con['route_key'] = "hhs_search_log";
        $con['vhost'] = "";
        $con['exchangeType'] = AMQP_EX_TYPE_DIRECT;
        $con['flags'] = AMQP_DURABLE;
        $con['exchangename'] = 'CREDITHC_CS';
        vendor('Mq.RabbitMQ');
        try{
            $MQ = \RabbitMQ :: init();
            $MQ -> config($con);
            $MQ -> connect();
            $n = 1;
            $log_info = array();
            while ($n<1000){
                $ret = $MQ -> getMsg();
                if(!$ret) break;
                $log_info[] = json_decode($ret,true);
                $n++;
            }
            $this->dullLog($log_info);
        }catch(Exception $e){
            var_dump($e);
        }
    }
    
    /*
     * 搜索日志信息数据处理，读取rabbitmq 批量上传数据，每次至多1000条
     */
    public function dullLog($datas,$es_index='search_log1'){
        if(count($datas)<=0){
            return array();
        }
        $params = array('body'=>array());
        foreach ($datas as $k=>$v){
            $arr = array(
                "keyword"=>$v['keyword'],
                "create_time"=>$v['time_info']+0,
                "brown_info"=>$v['brown_info'],
                "uid"=>$brand_data['uid']+0,
                "ip"=> $v['ip'],
                "flag"=>$v['flag']+0
            );
            $params['body'][] = [
                'index' => [
                    '_index' => $es_index,
                    '_type' => 'log',
                ]
            ];

            $params['body'][] = $arr;
        }
        unset($responses);
        $responses = $this->client->bulk($params);
        return $responses;
    }
}
