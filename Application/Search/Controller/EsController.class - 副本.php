<?php
namespace Search\Controller;//WEB_ROOT
require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;
use Think\Controller;
    class EsController extends Controller
    {
        public function _initialize()
        {
            if(!defined('JSON_PRESERVE_ZERO_FRACTION'))
            {
                define('JSON_PRESERVE_ZERO_FRACTION', 1024);
            }
            //Vendor('vendor.autoload');
            $hosts = [
                '192.168.1.237:9200',         // IP + Port
                //'192.168.1.2',              // Just IP
                //'mydomain.server.com:9201', // Domain + Port
                //'mydomain2.server.com',     // Just Domain
                //'https://localhost',        // SSL to localhost
                //'https://192.168.1.3:9200'  // SSL to IP + Port
            ];
            $this->client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                                ->setHosts($hosts)      // Set the hosts
                                ->build();
        }
    public function test(){
        $con = 'mysql://root:123456@192.168.1.232:3306/liexin';
        $m = M('goods_merge', 'lie_', $con);
        $id = rand(1, 5856712);
        $res = $m->where(array('id'=>$id))->field('goods_name')->find();
        $len = strlen($res['goods_name']);
        $start = rand(0, $len-4);
        $str = substr($res['goods_name'], $start,3);
        
        $arr = array();
        $arr['spu_name/condition'] = strtoupper($str);
        $this->testSpu($arr);
        
    }
    //sku查询
    public function testSku($arr){
//        $_POST['supplier_id'] = 0;
//        $_POST['spu_id/condition'] = '123,234';
//        $_POST['sale_time/order'] = 'desc';
//        $_POST['p'] = 1;
//        $_POST['goods_name/condition'] = 'kkkk,111111';
        $supplier_id = $_POST['supplier_id'];
        $arr  = $_POST;
        $arr['p']  = $arr['p']? $arr['p']:1;
        $index_name = array(7=>'digikey',8=>'chip1stop');//供应商id对应供应商名称（即ES索引名称）
        $params = array();
        $lianying_index_name = array(1=>'future',2=>'powerandsignal',3=>'rochester',4=>'tme',5=>'verical',6=>'element14',7=>'digikey',8=>'chip1stop',10=>'arrow',12=>'alliedelec',13=>'avnet',14=>'mouser');//根据实际情况进行填补
        if(isset($index_name[$supplier_id]) && !empty($index_name[$supplier_id])){
            $params['index'] = $index_name[$supplier_id];
            $params['type'] = 'goods';
        }else{
            $params['index'] = $index_name;
            $params['type'] = 'goods';
        }
        //条件字段
        $where_field = array('spu_id','goods_type','goods_name','company_name','goods_status','encoded');
        $sort_field = array('create_time' ,'sale_time','update_time','goods_status','single_price');
        $int_field = array('create_time','sale_time','update_time','goods_status','class_id3','class_id2','class_id1','single_price','stock','goods_id','old_goods_id','spu_id','sort','delivery_place');
        if(count($arr)>0){
            foreach($arr as $k=>$v){
                //返回参数设置
                $params['body']['_source'] = array('goods_id',"old_goods_id");
                //查询条件 
                $k_temp = explode('/',$k);
                if(isset($k_temp[1]) && $k_temp[1] == 'condition'){
                    $term_v = explode(',', $v);
                    //转换类型
                    if(in_array($k_temp[0], $int_field)){
                        foreach ($term_v as $k=>$v){
                            $term_v[$k] = $v+0;
                        }
                    }else{
                        foreach ($term_v as $k=>$v){
                            $term_v[$k] = strtoupper($v);
                        }
                    }
                    $params['body']['query']['bool']['must'][] = array('terms'=>array($k_temp[0]=>$term_v));
                    
                }
                //排序参数
                if(isset($k_temp[1]) && $k_temp[1] == 'order'){
                    $params['body']['sort'][$k_temp[0]] = array('order'=>$v);
                }
                
                //范围查询
                if(isset($k_temp[1]) && $k_temp[1] == 'range'){
                    $v_temp = explode(',', $v);
                    $params['body']['query']['bool']['must'][] = array('range'=>array($k_temp[0]=>array('gte'=>$v_temp[0]+0,'lte'=>$v_temp[1]+0)));
                }
            }
            //如果没有排序，则开启默认排序
            if(!isset($params['body']['sort'])){
                $params['body']['sort']['update_time'] = array('order'=>'desc');//默认
            }
            $length = $arr['offset']>0? intval($arr['offset']):20;
            $start = ($arr['p']-1)*$length;
            $params['body']['from'] = $start;
            $params['body']['size'] = $length;
        }

        $results = $this->client->search($params);
        file_put_contents('./request_sku_time.txt', $results['took'].'  ',FILE_APPEND);
        dump($results);exit;
        
    }
    public function testSpu($arr){
        
//        $_POST['spu_name/condition'] = '15';
//        $_POST['sale_time/order'] = 'desc';
        //$arr  = $_POST;
        $arr['p']  = $arr['p']? $arr['p']:1;
        $params = array();
        $params['index'] = 'test1';
        $params['type'] = 'spu';

        //条件字段
        $where_field = array('spu_name','brand_id','status','class_id3','sale_time');
        $sort_field = array('create_time','sale_time','update_time','spu_name','status');
        $int_field = array('create_time','sale_time','update_time','status','class_id3','class_id2','class_id1','brand_id');
        if(count($arr)>0){
            foreach($arr as $k=>$v){
                //返回参数设置
                $params['body']['_source'] = array('spu_id');
                //查询条件 
                $k_temp = explode('/',$k);
                if(isset($k_temp[1]) && $k_temp[1] == 'condition'){
                    $term_v = explode(',', $v);
                    if($k_temp[0]!='spu_name' && $k_temp[0]!='brand_name'){
                        foreach ($term_v as $k=>$v){
                            $term_v[$k] = $v+0;
                        }
                    }else{
                        foreach ($term_v as $k=>$v){
                            $term_v[$k] = strtoupper($v);
                        }
                    }
                    $params['body']['query']['bool']['must'][] = array('terms'=>array($k_temp[0]=>$term_v));
                }
                //排序参数
                if(isset($k_temp[1]) && $k_temp[1] == 'order'){
                    $params['body']['sort'][$k_temp[0]] = array('order'=>$v);
                }
                
                //范围查询
                if(isset($k_temp[1]) && $k_temp[1] == 'range'){
                    $v_temp = explode(',', $v);
                    $params['body']['query']['bool']['must'][] = array('range'=>array($k_temp[0]=>array('gte'=>$v_temp[0]+0,'lte'=>$v_temp[1]+0)));
                }
            }
            //如果没有排序，则开启默认排序
            if(!isset($params['body']['sort'])){
                $params['body']['sort']['update_time'] = array('order'=>'desc');//默认
            }
            $length = $arr['offset']>0? intval($arr['offset']):20;
            $start = ($arr['p']-1)*$length;
            $params['body']['from'] = $start;
            $params['body']['size'] = $length;
        }

        $results = $this->client->search($params);
        file_put_contents('./request_spu_time.txt', $results['took'].'  ',FILE_APPEND);
        dump($results);exit;
        $temp = $results['hits']['hits'];
        $res = array();
        if($results['hits']['total']>0){
            $res['total'] = $results['hits']['total'];
            foreach($temp as $k=>$v){
                $res['spu_id'][] = strval($v['_source']['spu_id']);
            }
        }
        $this->return_date(0,'',$res);   
        
    }
    //sku全量增量数据同步脚本
    public function es_sku(){
        set_time_limit(0);
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_sku_';
        $table = 'sku_';
        $content = file_get_contents('./sku_ids.txt');
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }
        //按库查
        for($i = 0;$i<10;$i++){
            //按表查
            for($j = 0;$j<10;$j++){
                $m = M($table.$j, 'lie_', $con.$i);
                //自营、联营、专卖查
                for($p=0;$p<3;$p++){
                    if($p!=1){
                        $index_name = array(0=>'ziying',2=>'zhuanmai');
                        //取出上一次增量的id,把增量部分添加到es
                        if(!isset($last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]]) || $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]]<=0){
                            $from_time = 0;
                        }else{
                            $from_time = $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]];
                        }
                        $where = array('update_time'=>array('EGT',$from_time),'goods_type'=>$p);
                        $count = $m->where($where)->count();
                        if($count>0){
                            $total_page = ceil($count/1000);echo $total_page,'jjj';
                            for($n=0;$n<$total_page;$n++){
                                $start = $n*1000;
                                $datas = $m->field('*')->where($where)->order('update_time asc')->limit($start.',1000')->select();
                                $this->souOther($datas,$index_name[$p]);
                                $temp_arr = end($datas);echo 'liexin_sku_'.$i.'_'.$table.$j.$v.'--'.$n.'===';
                                //$where['update_time'] = array('gt',$temp_arr['update_time']);
                            }
                            $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]] = $temp_arr['update_time'];
                        }
                    }else{
                        $index_name = array(1=>'future',2=>'powerandsignal',3=>'rochester',4=>'tme',5=>'verical',6=>'element14',7=>'digikey',8=>'chip1stop',10=>'arrow',12=>'alliedelec',13=>'avnet',14=>'mouser',15=>'company11');//根据实际情况进行填补
                        foreach($index_name as $k=>$v){
                            //取出上一次增量的id,把增量部分添加到es
                            if(!isset($last_time['liexin_sku_'.$i.'_'.$table.$j.$v]) || $last_time['liexin_sku_'.$i.'_'.$table.$j.$v]<=0){
                                $from_time = 0;
                            }else{
                                $from_time = $last_time['liexin_sku_'.$i.'_'.$table.$j.$v];
                            }
                            $where = array('update_time'=>array('EGT',$from_time),'goods_type'=>$p,'supplier_id'=>intval($k));
                            if($k==15){
                                $where['supplier_id'] = array('in',array(15,16,17,18,19));
                            }
                            $count = $m->where($where)->count();
                            if($count>0){
                                $total_page = ceil($count/1000);echo $total_page,'jjj';
                                for($n=0;$n<$total_page;$n++){
                                    $start = $n*1000;
                                    $datas = $m->field('*')->where($where)->order('update_time asc')->limit($start.',1000')->select();
                                    $this->souOther($datas,$v);
                                    $temp_arr = end($datas);echo 'liexin_sku_'.$i.'_'.$table.$j.$v.'--'.$n.'===';
                                    //$where['update_time'] = array('gt',$temp_arr['update_time']);
                                }
                                $last_time['liexin_sku_'.$i.'_'.$table.$j.$v] = $temp_arr['update_time'];
                            }
                        }  
                    }
                }
            }
        }
        file_put_contents('./sku_ids.txt', json_encode($last_time));dump($last_time);
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
            $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_spu';
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
                "goods_name"=>$spu['spu_name'],
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
        $responses = $this->client->bulk($params);
        unset($responses);
    }
    //spu全量增量数据同步脚本
    public function es_spu(){
        set_time_limit(0);
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_spu';
        $table = 'spu_';
        $content = file_get_contents('./spu_ids.txt');
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }

        //按表查
        for($j = 0;$j<10;$j++){
            $m = M($table.$j, 'lie_', $con);
            //取出上一次增量的id,把增量部分添加到es
            if(!isset($last_time[$table.$j]) || $last_time[$table.$j]<=0){
                $from_time = 0;
            }else{
                $from_time = $last_time[$table.$j];
            }
            $where = array('update_time'=>array('EGT',$from_time));
            $count = $m->where($where)->count();
            if($count>0){
                $total_page = ceil($count/1000);echo $total_page,'jjj';
                for($n=0;$n<$total_page;$n++){
                    $start = $n*1000;
                    $datas = $m->field('*')->where($where)->order('update_time asc')->limit($start.',1000')->select();
                    $this->souSpuOther($datas,'lie_spu');
                    $temp_arr = end($datas);echo $table.$j.'--'.$n.'===';
                    //$where['spu_id'] = array('gt',$temp_arr['spu_id']);
                }
                $last_time[$table.$j] = $temp_arr['update_time'];
            }

        }

        file_put_contents('./spu_ids.txt', json_encode($last_time));dump($last_time);
    }
    //处理spu的数据信息
    public function souSpuOther($datas,$es_index){
        if(count($datas)<=0){
            return array();
        }
        $num = count($datas);
        $params = array('body'=>array());
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_spu';
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
        $responses = $this->client->bulk($params);
        unset($responses);
    }
    //sku数据更新
    public function updateSku(){
        $ids = $_POST('ids');
        $arr = explode(',',$ids );
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_sku_';
        $table = 'sku_';
        foreach ($arr as $v){
            $a = substr($v, -2,1);
            $b = substr($v, -1);
            $m = M($table.$b, 'lie_', $con.$a);
            $datas = $m->field('*')->where(array('goods_id'=>$v))->limit('0,1')->select();
            $arr0 = array(0=>'ziying',2=>'zhuanmai');//自营与专卖
            $arr1 = array(0=>'mouser');//联营
            $lianying_index_name = array(1=>'future',2=>'powerandsignal',3=>'rochester',4=>'tme',5=>'verical',6=>'element14',7=>'digikey',8=>'chip1stop',10=>'arrow',12=>'alliedelec',13=>'avnet',14=>'mouser',15=>'company11',16=>'company11',17=>'company11',18=>'company11',19=>'company11');//根据实际情况进行填补
            if($datas[0]['goods_type']!=1){
                $this->souOther($datas,$arr0[$datas[0]['goods_type']]);
            }else{
                $this->souOther($datas,$arr1[$datas[0]['supplier_id']]);
            }
        }
    }
    //sku数据更新
    public function updateSpu(){
        $ids = $_POST('ids');
        $arr = explode(',',$ids );
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_spu';
        $table = 'spu_';
        foreach ($arr as $v){
            $b = substr($v, -1);
            $m = M($table.$b, 'lie_', $con);
            $datas = $m->field('*')->where(array('spu_id'=>$v))->limit('0,1')->select();
            $this->souSpuOther($datas,'lie_spu');
        }
    }
    //sku查询
    public function searchSku(){
//        $_POST['supplier_id'] = 0;
//        $_POST['spu_id/condition'] = '123,234';
//        $_POST['sale_time/order'] = 'desc';
//        $_POST['p'] = 1;
//        $_POST['goods_name/condition'] = 'kkkk,111111';
        $supplier_id = $_POST['supplier_id'];
        $arr  = $_POST;
        $arr['p']  = $arr['p']? $arr['p']:1;
        //$index_name = array(7=>'digikey',8=>'chip1stop');//供应商id对应供应商名称（即ES索引名称）
        $params = array();
        $index_name = array(1=>'future',2=>'powerandsignal',3=>'rochester',4=>'tme',5=>'verical',6=>'element14',7=>'digikey',8=>'chip1stop',10=>'arrow',12=>'alliedelec',13=>'avnet',14=>'mouser',15=>'company11',16=>'company11',17=>'company11',18=>'company11',19=>'company11');//根据实际情况进行填补
        if(isset($index_name[$supplier_id]) && !empty($index_name[$supplier_id])){
            $params['index'] = $index_name[$supplier_id];
            $params['type'] = 'goods';
        }else{
            $params['index'] = $index_name;
            $params['type'] = 'goods';
        }
        //条件字段
        //$where_field = array('spu_id','goods_type','goods_name','company_name','goods_status','encoded');
        //$sort_field = array('create_time' ,'sale_time','update_time','goods_status','single_price');
        $upper_field = array('goods_name','brand_name');
        $int_field = array('create_time','sale_time','update_time','goods_status','class_id3','class_id2','class_id1','single_price','stock','goods_id','old_goods_id','spu_id','sort','delivery_place');
        if(count($arr)>0){
            foreach($arr as $k=>$v){
                //返回参数设置
                $params['body']['_source'] = array('goods_id',"old_goods_id");
                //查询条件 
                $k_temp = explode('/',$k);
                if(isset($k_temp[1]) && $k_temp[1] == 'condition'){
                    $term_v = explode(',', $v);
                    //转换类型
                    if(in_array($k_temp[0], $int_field)){
                        foreach ($term_v as $k=>$v){
                            $term_v[$k] = $v+0;
                        }
                    }else{
                        if(in_array($k_temp[0], $upper_field)){
                            foreach ($term_v as $k=>$v){
                                $term_v[$k] = strtoupper($v);
                            }
                        }
                    }
                    $params['body']['query']['bool']['must'][] = array('terms'=>array($k_temp[0]=>$term_v));
                    
                }
                //排序参数
                if(isset($k_temp[1]) && $k_temp[1] == 'order'){
                    $params['body']['sort'][$k_temp[0]] = array('order'=>$v);
                }
                
                //范围查询
                if(isset($k_temp[1]) && $k_temp[1] == 'range'){
                    $v_temp = explode(',', $v);
                    $params['body']['query']['bool']['must'][] = array('range'=>array($k_temp[0]=>array('gte'=>$v_temp[0]+0,'lte'=>$v_temp[1]+0)));
                }
            }
            //如果没有排序，则开启默认排序
            if(!isset($params['body']['sort'])){
                $params['body']['sort']['update_time'] = array('order'=>'desc');//默认
            }
            $length = $arr['offset']>0? intval($arr['offset']):20;
            $start = ($arr['p']-1)*$length;
            $params['body']['from'] = $start;
            $params['body']['size'] = $length;
        }

        $results = $this->client->search($params);
        $temp = $results['hits']['hits'];
        $res = array();
        if($results['hits']['total']>0){
            $res['total'] = $results['hits']['total'];
            foreach($temp as $k=>$v){
                $res['goods_id'][] = strval($v['_source']['goods_id']);
                //$res['old_goods_id'][] = strval($v['_source']['old_goods_id']);
            }
        }
        $this->return_date(0,'',$res);
    }
    //spu查询
    public function searchSpu(){
        
//        $_POST['spu_name/condition'] = '15';
//        $_POST['sale_time/order'] = 'desc';
        $arr  = $_POST;
        $arr['p']  = $arr['p']? $arr['p']:1;
        $params = array();
        $params['index'] = 'lie_spu';
        $params['type'] = 'spu';

        //条件字段
        $where_field = array('spu_name','brand_id','status','class_id3','sale_time');
        $sort_field = array('create_time','sale_time','update_time','spu_name','status');
        $upper_field = array('spu_name','brand_name');
        $int_field = array('create_time','sale_time','update_time','status','class_id3','class_id2','class_id1','brand_id');
        if(count($arr)>0){
            foreach($arr as $k=>$v){
                //返回参数设置
                $params['body']['_source'] = array('spu_id');
                //查询条件 
                $k_temp = explode('/',$k);
                if(isset($k_temp[1]) && $k_temp[1] == 'condition'){
                    $term_v = explode(',', $v);
                    if($k_temp[0]!='spu_name' && $k_temp[0]!='brand_name'){
                        foreach ($term_v as $k=>$v){
                            $term_v[$k] = $v+0;
                        }
                    }else{
                        if(in_array($k_temp[0], $upper_field)){
                            foreach ($term_v as $k=>$v){
                                $term_v[$k] = strtoupper($v);
                            }
                        }
                    }
                    $params['body']['query']['bool']['must'][] = array('terms'=>array($k_temp[0]=>$term_v));
                }
                //排序参数
                if(isset($k_temp[1]) && $k_temp[1] == 'order'){
                    $params['body']['sort'][$k_temp[0]] = array('order'=>$v);
                }
                
                //范围查询
                if(isset($k_temp[1]) && $k_temp[1] == 'range'){
                    $v_temp = explode(',', $v);
                    $params['body']['query']['bool']['must'][] = array('range'=>array($k_temp[0]=>array('gte'=>$v_temp[0]+0,'lte'=>$v_temp[1]+0)));
                }
            }
            //如果没有排序，则开启默认排序
            if(!isset($params['body']['sort'])){
                $params['body']['sort']['update_time'] = array('order'=>'desc');//默认
            }
            $length = $arr['offset']>0? intval($arr['offset']):20;
            $start = ($arr['p']-1)*$length;
            $params['body']['from'] = $start;
            $params['body']['size'] = $length;
        }

        $results = $this->client->search($params);//dump($results);exit;
        $temp = $results['hits']['hits'];
        $res = array();
        if($results['hits']['total']>0){
            $res['total'] = $results['hits']['total'];
            foreach($temp as $k=>$v){
                $res['spu_id'][] = strval($v['_source']['spu_id']);
            }
        }
        $this->return_date(0,'',$res);   
        
    }
    public function return_date($code=0,$msg='',$data=array()){
        if(empty($data)){
            $code = 1;
        }
        $arr = array(
            'error_code'=>$code,
            'error_msg'=>$msg,
            'data'=>$data,
        );//dump($arr);exit;
        //header('content-type:application/json;charset=utf8'); 
        echo json_encode($arr);exit;
    }
        public function create_index()
        {
            $json = '{
    "settings": {
        "analysis": {
            "tokenizer": {
                "trigrams_filter": {
                    "type":     "ngram",
                    "min_gram": 1,
                    "max_gram": 50,
					"token_chars": ["letter","digit","punctuation","symbol","whitespace"]
                }
            },
            "analyzer": {
                "trigrams": {
                    "type":      "custom",
                    "tokenizer": "trigrams_filter",
                    "filter":   [
                        "lowercase"
                    ]
                }
            }
        }
    },
    "mappings": {
		"goods": { 
		  "_all":       { "enabled": false  }, 
		  "properties": { 
			"goods_id":       { "type": "long"  }, 
			"old_goods_id":     { "type": "long"  }, 
			"goods_name":      { "type": "string" ,"analyzer": "trigrams"},
			"brand_name":    { "type": "string" ,"analyzer": "trigrams" }, 
			"supplier_name":     { "type": "string","index": "not_analyzed" }, 
			"class_id1":      { "type": "integer" },
			"class_id2":     { "type": "integer"  }, 
			"class_id3":     { "type": "integer"  }, 
			"spu_id":       { "type": "string","index": "not_analyzed" },
			"sale_time":    { "type": "integer"  }, 
			"create_time":     { "type": "integer"  }, 
			"update_time":      { "type": "integer" },
			"single_price":    { "type": "double"  }, 
			"stock":    { "type": "integer"  }, 
			"goods_status":     { "type": "byte"  }, 
			"sort":          { "type": "integer" },
			"encoded":       { "type": "string" ,"index": "not_analyzed" }, 
			"encap":         { "type": "string" ,"index": "not_analyzed" }, 
			"delivery_place":      { "type": "byte" }
		  }
		}
	}
}';
            $set = '{
        "analysis": {
            "tokenizer": {
                "trigrams_filter": {
                    "type":     "ngram",
                    "min_gram": 1,
                    "max_gram": 50,
					"token_chars": ["letter","digit","punctuation","symbol","whitespace"]
                }
            },
            "analyzer": {
                "trigrams": {
                    "type":      "custom",
                    "tokenizer": "trigrams_filter",
                    "filter":   [
                        "lowercase"
                    ]
                }
            }
        }
    }';
            $map = '{
		"goods": { 
		  "_all":       { "enabled": false  }, 
		  "properties": { 
			"goods_id":       { "type": "long"  }, 
			"old_goods_id":     { "type": "long"  }, 
			"goods_name":      { "type": "string" ,"analyzer": "trigrams"},
			"brand_name":    { "type": "string" ,"analyzer": "trigrams" }, 
			"supplier_name":     { "type": "string","index": "not_analyzed" }, 
			"class_id1":      { "type": "integer" },
			"class_id2":     { "type": "integer"  }, 
			"class_id3":     { "type": "integer"  }, 
			"spu_id":       { "type": "string","index": "not_analyzed" },
			"sale_time":    { "type": "integer"  }, 
			"create_time":     { "type": "integer"  }, 
			"update_time":      { "type": "integer" },
			"single_price":    { "type": "double"  }, 
			"stock":    { "type": "integer"  }, 
			"goods_status":     { "type": "byte"  }, 
			"sort":          { "type": "integer" },
			"encoded":       { "type": "string" ,"index": "not_analyzed" }, 
			"encap":         { "type": "string" ,"index": "not_analyzed" }, 
			"delivery_place":      { "type": "byte" }
		  }
		}
	}';
            $params = [
    'index' => 'hhs',
    'settings' => $set,
    'mappings' => $map,

];

$response = $this->client->index($params);
print_r($response);
        }
        public function add_document()
        {
            $params = array();
            $params['body'] = array(
                          
                "spu_id"=>2223372036854775847,
                "spu_name"=>'jdjfj$%^',   
                "brand_id"=>32767,   
                "status"=>1,     
                "class_id1"=>1,  
                "class_id2"=>121,  
                "class_id3"=>32767,  
                "sale_time"=>time(),  
                "create_time"=>time(),
                "update_time"=>time(),
                "sort"=>time()      
            );
//"spu_id":2223372036854775807, 
//"spu_name":"jdjfj$%^",			
//"brand_id":32767,
//"status":1,			
//"class_id1":1,
//"class_id2":121,
//"class_id3":32767,
//"sale_time":1234567890,
//"create_time":1234567890,
//"update_time":1234567890,
//"sort":1234567890
//            $params['body'] = array(
//                          
//                "sku_id"=>123,      
//                "old_goods_id"=>123, 
//                "goods_name"=>123,   
//                "brand_name"=>123,   
//                "supplier_name"=>123,
//                "class_id1"=>123,    
//                "class_id2"=>123,    
//                "class_id3"=>123,    
//                "spu_id"=>123,       
//                "sale_time"=>123,    
//                "create_time"=>123,  
//                "update_time"=>123,  
//                "single_price"=>123, 
//                "goods_status"=>123, 
//                "sort"=>123,         
//                "encoded"=>123, 
//                "encap"=>123,        
//                "delivery_place"=>0
//
//
//            );
            $params['index'] = 'lie_spu';
            $params['type'] = 'spu';
            $params['id'] = '2223372036854775847';
            $ret = $this->client->index($params);dump($ret);
        }
        public function delete_index()
        {
            $deleteParams['index'] = 'my_index';
            $this->client->indices()->delete($deleteParams);
        }
        public function delete_document()
        {
            $deleteParams = array();
            $deleteParams['index'] = 'my_index';
            $deleteParams['type'] = 'my_index';
            $deleteParams['id'] = 'AU4Kmmj-WOmOrmyOj2qf';
            $retDelete = $this->client->delete($deleteParams);
        }
        public function update_document()
        {
            $updateParams = array();
            $updateParams['index'] = 'ziying';
            $updateParams['type'] = 'goods';
            $updateParams['id'] = '2223372036854775847';
            $updateParams['body']['doc']['goods_name']  = '111111';
           $response = $this->client->update($updateParams);
             
        }
        //模糊匹配
        public function match()
        {
            //模糊匹配
//            $json = '{
//               "query" : {
//                   "match" : {
//                       "goods_name" : "stm"
//                   }
//               }
//           }';
//           term是代表完全匹配，即不进行分词器分析，文档中必须包含整个搜索的词汇
//            $json = '{
//               "query" : {
//                   "term" : {
//                       "goods_name" : "stm"
//                   }
//               }
//           }';
            //当你需要寻找邻近的几个单词时，你会使用match_phrase查询：分页 //测试高亮是否正常  
//            $json = '{
//               "query" : {
//                   "match_phrase" : {
//                       "goods_name" : "stm"
//                   }
//               },
//               "from": 10,
//               "size": 2,
//               "highlight": {   
//    "pre_tags": [  
//      "<b>"  
//    ],  
//    "post_tags": [  
//      "</b>"  
//    ],  
//    "fragment_size": 100,  
//    "number_of_fragments": 2,  
//    "require_field_match": true,  
//    "fields": {  
//      "goods_name": {}  
//    }  
//  }
//              
//           }';
           
            $json = '{"query":{"regexp":{"exact_value":".*Quick Fo\\*x.*"}}}';
            //bool多条件匹配
//            $json  = '{
//               "query" : {
//        "bool" : {
//            "must": [
//                {
//                    "match" : { "goods_name" : "stm" }
//                },
//                {
//                    "match" : { "id" : "12" }
//                }
//            ]
//        }
//    }
//                   }';
//            多匹配再过滤
//            $json = '{"query" : {
//        "bool" : {
//            "filter" : {
//                "term" : { "goods_name" : "stmf" }
//            },
//            "must" : {
//                "match" : { "id" : "12" }
//            }
//        }
//    }}';jADDG 123-45*%&*!6

           $params = [
               'index' => 'test1',
               'type' => 'my_type',
               'body' => ['query'=>['match'=>[
                   'text'=>"&*!"
               ]]]
           ];

           $results = $this->client->search($params);
           dump($results);
        }
        //多个类型匹配
        public function find_types()
        {
            $json = '{
               "query" : {
                   "match" : {
                       "id" : "12"
                   }
               }
           }';

           $params = [
               'index' => 'liexin',
               'type' => 'goods,gs',
               'body' => $json
           ];

           $results = $this->client->search($params);
           dump($results);
        }
        public function get_document()
        {
           $params = [
    'index' => 'my_index',
    'type' => 'my_type',
//    'body' => [
//        'query' => [
//            'match' => [
//                'testField' => 'abc'
//            ]
//        ]
//    ]
];

$response = $this->client->search($params);
dump($response);
        }
        //spu全量增量数据同步脚本
    public function es_test1(){
        set_time_limit(0);
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_spu';
        $table = 'spu_';
        $content = file_get_contents('./spu_ids1.txt');
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }

        //按表查
        for($j = 0;$j<10;$j++){
            $m = M($table.$j, 'lie_', $con);
            //取出上一次增量的id,把增量部分添加到es
            if(!isset($last_time[$table.$j]) || $last_time[$table.$j]<=0){
                $from_time = 0;
            }else{
                $from_time = $last_time[$table.$j];
            }
            $where = array('update_time'=>array('EGT',$from_time));
            $count = $m->where($where)->count();
            if($count>0){
                $total_page = ceil($count/1000);echo $total_page,'jjj';
                for($n=0;$n<$total_page;$n++){
                    $start = $n*1000;
                    $datas = $m->field('*')->where($where)->order('update_time asc')->limit($start.',1000')->select();
                    $this->souSpuTest1($datas,'test1');
                    $temp_arr = end($datas);echo $table.$j.'--'.$n.'===';
                    //$where['spu_id'] = array('gt',$temp_arr['spu_id']);
                }
                $last_time[$table.$j] = $temp_arr['update_time'];
            }

        }

        file_put_contents('./spu_ids1.txt', json_encode($last_time));dump($last_time);
    }
    //处理spu的数据信息
    public function souSpuTest1($datas,$es_index){
        if(count($datas)<=0){
            return array();
        }
        $num = count($datas);
        $params = array('body'=>array());
        $con = 'mysql://spu:spu@192.168.1.235:3306/liexin_spu';
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
        $responses = $this->client->bulk($params);
        unset($responses);
    }
    }
    ?>
