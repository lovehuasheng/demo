<?php
namespace Search\Controller;//WEB_ROOT
require 'vendor/autoload.php';

use Elasticsearch\ClientBuilder;
use Think\Controller;
use Search\Model\MonModel;
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
                //'172.18.137.29:9211',         // IP + Port
                C('ES_IP_PORT'),         // IP + Port
            ];
            $this->client = ClientBuilder::create()           // Instantiate a new ClientBuilder
                                ->setHosts($hosts)      // Set the hosts
                                ->build();
        }
    //sku全量增量数据同步脚本
    public function es_sku_bak(){
        set_time_limit(0);
        $con = C('MYSQL_POWER').'/liexin_sku_';
        $table = 'sku_';
        //$content = file_get_contents('./sku_ids.txt');
        $content = false;
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }
        $redis = new \Think\Cache\Driver\Redis(); 
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
                        //$v_update_time = $redis->hGet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]);
                        $v_update_time = 0;
                        if($v_update_time>0){
                            $from_time = $v_update_time;
                        }else{
                            if(!isset($last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]]) || $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]]<=0){
                                $from_time = 1;
                            }else{
                                $from_time = $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]];
                            }
                        }
                        $where = array('update_time'=>array('EGT',$from_time),'goods_type'=>$p);
                        $count = $m->where($where)->count();
                        if($count>0){
                            $total_page = ceil($count/1000);echo $total_page,'jjj';
                            for($n=0;$n<$total_page;$n++){
                                $start = $n*1000;
                                $datas = $m->field('*')->where($where)->order('update_time asc')->limit($start.',1000')->select();
                                $this->souOther($datas,$index_name[$p]);
                                $temp_arr = end($datas);echo 'liexin_sku_'.$i.'_'.$table.$j.$index_name[$p].'--'.$n.'===';
                                //$where['update_time'] = array('gt',$temp_arr['update_time']);
                            }
                            $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]] = $temp_arr['update_time'];
                            $redis->hSet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$index_name[$p],$temp_arr['update_time']);
                        }
                    }else{
                        $index_name = C('LIANYING_SUPERLIER');//根据实际情况进行填补
                        foreach($index_name as $k=>$v){
                            //$v_update_time = $redis->hGet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$v);
                            $v_update_time = 0;
                            if($v_update_time>0){
                                $from_time = $v_update_time;
                            }else{
                                //取出上一次增量的id,把增量部分添加到es
                                if(!isset($last_time['liexin_sku_'.$i.'_'.$table.$j.$v]) || $last_time['liexin_sku_'.$i.'_'.$table.$j.$v]<=0){
                                    $from_time = 1;//把0去除
                                }else{
                                    $from_time = $last_time['liexin_sku_'.$i.'_'.$table.$j.$v];
                                }
                            }
                                
                            $where = array('update_time'=>array('EGT',$from_time),'goods_type'=>$p,'supplier_id'=>intval($k));
//                            if($k==15){
//                                $where['supplier_id'] = array('in',array(15,16,17,18,19));
//                            }
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
                                $redis->hSet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$v,$temp_arr['update_time']);
                            }
                        }  
                    }
                }
            }
        }
        file_put_contents('./sku_ids.txt', json_encode($last_time));dump($last_time);
    }
    //sku全量增量数据同步脚本
    public function es_sku(){
        set_time_limit(0);
        $con = C('MYSQL_POWER').'/liexin_sku_';
        $table = 'sku_';
        $content = file_get_contents('./sku_ids.txt');
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }
        $redis = new \Think\Cache\Driver\Redis(); 
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
                        $v_update_time = $redis->hGet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]);
                        //$v_update_time = 0;
                        if($v_update_time>0){
                            $from_time = $v_update_time;
                        }else{
                            if(!isset($last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]]) || $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]]<=0){
                                $from_time = 1;
                            }else{
                                $from_time = $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]];
                            }
                        }
                        $where = array('update_time'=>array('EGT',$from_time),'goods_type'=>$p);
                        $count = $m->where($where)->count();
                        if($count>0){
                            $total_page = ceil($count/1000);echo $total_page,'jjj';
                            for($n=0;$n<$total_page;$n++){
                                $start = $n*1000;
                                $datas = $m->field('*')->where($where)->order('update_time asc')->limit($start.',1000')->select();
                                $this->souOther($datas,$index_name[$p]);
                                $temp_arr = end($datas);echo 'liexin_sku_'.$i.'_'.$table.$j.$index_name[$p].'--'.$n.'===';
                                //$where['update_time'] = array('gt',$temp_arr['update_time']);
                            }
                            $last_time['liexin_sku_'.$i.'_'.$table.$j.$index_name[$p]] = $temp_arr['update_time'];
                            $redis->hSet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$index_name[$p],$temp_arr['update_time']);
                        }
                    }else{
                        $index_name = C('LIANYING_SUPERLIER');//根据实际情况进行填补
                        foreach($index_name as $k=>$v){
                            $v_update_time = $redis->hGet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$v);
                            //$v_update_time = 0;
                            if($v_update_time>0){
                                $from_time = $v_update_time;
                            }else{
                                //取出上一次增量的id,把增量部分添加到es
                                if(!isset($last_time['liexin_sku_'.$i.'_'.$table.$j.$v]) || $last_time['liexin_sku_'.$i.'_'.$table.$j.$v]<=0){
                                    $from_time = 1;//把0去除
                                }else{
                                    $from_time = $last_time['liexin_sku_'.$i.'_'.$table.$j.$v];
                                }
                            }
                                
                            $where = array('update_time'=>array('EGT',$from_time),'goods_type'=>$p,'supplier_id'=>intval($k));
//                            if($k==15){
//                                $where['supplier_id'] = array('in',array(15,16,17,18,19));
//                            }
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
                                $redis->hSet('data_spot_sku','liexin_sku_'.$i.'_'.$table.$j.$v,$temp_arr['update_time']);
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
        $responses = $this->client->bulk($params);
        return $responses;
        
    }
    //spu全量增量数据同步脚本
    public function es_spu(){
        set_time_limit(0);
        $con = C('MYSQL_POWER').'/liexin_spu';
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
        $content = file_get_contents('./start_match.txt');
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }
        if(!isset($last_time['match']) || $last_time['match']<=0){
            $last_time['match'] = 100;
            file_put_contents('./start_match.txt', json_encode($last_time));
            $this->es_matches();
        }
        
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
        return $responses;
    }
    
    //撮合的数据同步
    //spu全量增量数据同步脚本
    public function es_matches(){
        set_time_limit(0);
        $content = file_get_contents('./matches_ids.txt');
        if($content){
            $last_time = json_decode($content,true);
        }else{
            $last_time = array();
        }

        //按表查
        for($j = 0;$j<10;$j++){
            $m = M('goods', 'lie_', C('DB_CONFIG1'));
            //取出上一次增量的id,把增量部分添加到es
            if(!isset($last_time['matches_lie_goods']) || $last_time['matches_lie_goods']<=0){
                $from_time = 0;
            }else{
                $from_time = $last_time['matches_lie_goods'];
            }
            $where = array('last_update'=>array('EGT',$from_time));
            $count = $m->where($where)->count();
            if($count>0){
                $total_page = ceil($count/1000);echo $total_page,'jjj';
                for($n=0;$n<$total_page;$n++){
                    $start = $n*1000;
                    $datas = $m->field('*')->where($where)->order('last_update asc')->limit($start.',1000')->select();
                    $this->souMatches($datas,'matches');
                    $temp_arr = end($datas);echo 'matches_lie_goods'.'--'.$n.'===';
                    //$where['spu_id'] = array('gt',$temp_arr['spu_id']);
                }
                $last_time['matches_lie_goods'] = $temp_arr['last_update'];
            }

        }

        file_put_contents('./matches_ids.txt', json_encode($last_time));dump($last_time);
    }
    //处理spu的数据信息
    public function souMatches($datas,$es_index){
        if(count($datas)<=0){
            return array();
        }
        $params = array('body'=>array());
        foreach ($datas as $k=>$v){
            $arr = array(
                "goods_id"=>$v['goods_id']+0,
                "goods_name"=>$v['goods_name']
            );
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
        $responses = $this->client->bulk($params);
        return $responses;
    }
    //sku数据更新
    public function updateSku(){
        $ids = $_POST['ids'];
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
            $arr1 = C('LIANYING_SUPERLIER');//联营
            if($datas[0]['goods_type']!=1){
                $res = $this->souOther($datas,$arr0[$datas[0]['goods_type']]);
            }else{
                $res = $this->souOther($datas,$arr1[$datas[0]['supplier_id']]);
            }
        }
        $this->return_date(0,'',1);
    }
    //sku数据更新
    public function updateSpu(){

        $ids = $_POST['ids'];
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
        $this->return_date(0,'',1);
    }
    //sku查询
    public function searchSku(){

        $supplier_id = $_POST['supplier_id'];
        $flag = $_POST['flag']?:false;//用于测试
        $arr  = $_POST;
        $arr['p']  = $arr['p']? $arr['p']:1;
        //$index_name = array(7=>'digikey',8=>'chip1stop');//供应商id对应供应商名称（即ES索引名称）
        $params = array();
        $index_name = C('SUPERLIER_ALL');//根据实际情况进行填补
        $index_name_all = C('SUPERLIER_ALL');//根据实际情况进行填补
        if(isset($index_name[$supplier_id]) && !empty($index_name[$supplier_id])){
            $params['index'] = $index_name[$supplier_id];
            $params['type'] = 'goods';
        }else{
            $params['index'] = $index_name_all;
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
                if($flag != 100){
                    $params['body']['_source'] = array('goods_id',"old_goods_id","goods_name");
                }
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
                //单边范围查询
                if(isset($k_temp[1]) && $k_temp[1] == 'sr'){
                    $v_temp = explode(',', $v);
                    $params['body']['query']['bool']['must'][] = array('range'=>array($k_temp[0]=>array($v_temp[0]=>$v_temp[1]+0)));
                }
            }
            if(isset($arr['agg']) && $arr['agg'] != ''){
                $params['body']['size'] = 0;
                $params['body']['aggs'] = array('tatol'=>array('terms'=>array('field'=>$arr['agg'])));
            }else{
                //如果没有排序，则开启默认排序
                if(!isset($params['body']['sort']) && !isset($arr['goods_name/condition'])){
                    $params['body']['sort']['update_time'] = array('order'=>'desc');//默认
                }
                $length = $arr['offset']>0? intval($arr['offset']):10;
                $start = ($arr['p']-1)*$length;
                $params['body']['from'] = $start;
                $params['body']['size'] = $length;
            }
            
        }
        //echo dump(json_encode($params));
        $results = $this->client->search($params);
        if($flag == 100){
            dump($results);exit;
        }
        $res = array();
        if(isset($arr['agg']) && $arr['agg'] != ''){
            $temp = $results['aggregations']['tatol']['buckets'];
            if(count($temp)>0){
                foreach($temp as $k=>$v){
                    $res[$v['key']] = strval($v['doc_count']);
                    //$res['old_goods_id'][] = strval($v['_source']['old_goods_id']);
                }
            }
        }else{
            $temp = $results['hits']['hits'];
            if($results['hits']['total']>0){
                $res['total'] = $results['hits']['total'];
                foreach($temp as $k=>$v){
                    $res['goods_id'][] = strval($v['_source']['goods_id']);
                    //$res['goods_name'][] = strval($v['_source']['goods_name']);
                }
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
                //单边范围查询
                if(isset($k_temp[1]) && $k_temp[1] == 'sr'){
                    $v_temp = explode(',', $v);
                    $params['body']['query']['bool']['must'][] = array('range'=>array($k_temp[0]=>array($v_temp[0]=>$v_temp[1]+0)));
                }
            }
            //如果没有排序，则开启默认排序
            if(!isset($params['body']['sort']) && !isset($arr['spu_name/condition'])){
                $params['body']['sort']['update_time'] = array('order'=>'desc');//默认
            }
            $length = $arr['offset']>0? intval($arr['offset']):10;
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
        );
        echo json_encode($arr);exit;
    }
    //测试，查找没有在引擎的数据
    public function check_data(){
        set_time_limit(0);
        $con = C('MYSQL_POWER').'/liexin_sku_'; 
        $table = 'sku_';
        //按库查
        for($i = 0;$i<10;$i++){
            //按表查
            for($j = 0;$j<10;$j++){
                $m = M($table.$j, 'lie_', $con.$i);
                //自营、联营、专卖查
                $where = array('supplier_id'=>7);
                $count = $m->where($where)->count();
                if($count>0){
                    
                    $total_page = ceil($count/1000);
                    for($n=0;$n<$total_page;$n++){
                        $start = $n*1000;
                        $datas = $m->field('goods_id')->where($where)->limit($start.',1000')->select();
                        //$this->souOther($datas,$index_name[$p]);
                        foreach ($datas as $v){
                            $arr = array();
                            $arr['goods_id/condition'] = $v['goods_id']+0;
                            $this->testSku($arr);
                        }
                    }
                }
            }
        }
    }
    
    //sku查询
    public function testSku($arr){
//        $_POST['supplier_id'] = 0;
//        $_POST['spu_id/condition'] = '123,234';
//        $_POST['sale_time/order'] = 'desc';
//        $_POST['p'] = 1;
//        $_POST['goods_name/condition'] = 'kkkk,111111';
//        $supplier_id = $_POST['supplier_id'];
//        $arr  = $_POST;
        $arr['p']  = $arr['p']? $arr['p']:1;
        $index_name = array(7=>'digikey');//供应商id对应供应商名称（即ES索引名称）
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
        if($results['hits']['total']<=0){
            dump($arr);exit;
        }
        
    }
    public function create_index(){exit;
        
        /*$con = C('MYSQL_POWER').'/liexin_sku_9';
        $table = 'sku_1';
        $m = M($table, 'lie_', $con);
        $datas = $m->where(array('supplier_id'=>19))->count();dump($datas);
        exit;*/
        //$redis = new \Think\Cache\Driver\Redis();
        //$res = $redis->hgetall('data_spot_sku');dump($res);exit;
        //$res = file_get_contents('http://172.18.137.29:9211/_cluster/health');dump($res);exit;
        $str = '{    "settings": { "number_of_shards":   5,"number_of_replicas": 0,        "analysis": {            "tokenizer": {                "trigrams_filter": {                    "type":     "ngram",                    "min_gram": 1,                    "max_gram": 100,					"token_chars": ["letter","digit","punctuation","symbol","whitespace"]                }            },            "analyzer": {                "trigrams": {                    "type":      "custom",                    "tokenizer": "trigrams_filter",                    "filter":   [                        "uppercase"                    ]                }            }        }    },    "mappings": {		"spu": { 		  "_all":       { "enabled": false  }, 		  "properties": { 			"spu_id":    { "type": "long" ,"index": "not_analyzed"  }, 			"spu_name":      { "type": "string" ,"analyzer": "trigrams"},						"brand_id":     { "type": "integer"  },		"brand_name":    { "type": "string" ,"analyzer": "trigrams" }, 	"status":     { "type": "byte"  }, 						"class_id1":      { "type": "integer" },			"class_id2":    { "type": "integer"  }, 			"class_id3":     { "type": "integer"  }, 			"sale_time":    { "type": "integer"  }, 			"create_time":     { "type": "integer"  }, 			"update_time":      { "type": "integer" },			"sort":      { "type": "integer" }		  }		}	}}';
        $res = json_decode($str,true);
        $params = [
            'index' => 'lie_spu',
            'body' => $res
        ];
        //$ss = $this->client->indices()->create($params);dump($ss);
        $str = '{    "settings": { "number_of_shards":   5,"number_of_replicas": 0,        "analysis": {            "tokenizer": {                "trigrams_filter": {                    "type":     "ngram",                    "min_gram": 1,                    "max_gram": 100,					"token_chars": ["letter","digit","punctuation","symbol","whitespace"]                }            },            "analyzer": {                "trigrams": {                    "type":      "custom",                    "tokenizer": "trigrams_filter",                    "filter":   [                        "uppercase"                    ]                }            }        }    },    "mappings": {		"goods": { 		  "_all":       { "enabled": false  }, 		  "properties": { 			"goods_id":       { "type": "long"  }, 			"goods_name":      { "type": "string" ,"analyzer": "trigrams"}		  }		}	}}';
        $res = json_decode($str,true);
        $params = [
            'index' => 'matches',
            'body' => $res
        ];
        //$ss = $this->client->indices()->create($params);dump($ss);
        $str = '{    "settings": { "number_of_shards":   5,"number_of_replicas": 0,        "analysis": {            "tokenizer": {                "trigrams_filter": {                    "type":     "ngram",                    "min_gram": 1,                    "max_gram": 100,					"token_chars": ["letter","digit","punctuation","symbol","whitespace"]                }            },            "analyzer": {                "trigrams": {                    "type":      "custom",                    "tokenizer": "trigrams_filter",                    "filter":   [                        "uppercase"                    ]                }            }        }    },    "mappings": {		"goods": { 		  "_all":       { "enabled": false  }, 		  "properties": { 			"goods_id":       { "type": "long"  }, 			"old_goods_id":     { "type": "long"  }, 			"goods_name":      { "type": "string" ,"analyzer": "trigrams"},			"brand_name":    { "type": "string" ,"analyzer": "trigrams" },     "brand_id":     { "type": "integer"  }, 			"supplier_name":     { "type": "string","index": "not_analyzed" }, 			"class_id1":      { "type": "integer" },			"class_id2":     { "type": "integer"  }, 			"class_id3":     { "type": "integer"  }, 			"spu_id":       { "type": "string","index": "not_analyzed" },			"sale_time":    { "type": "integer"  }, 			"create_time":     { "type": "integer"  }, 			"update_time":      { "type": "integer" },			"single_price":    { "type": "double"  }, 			"stock":    { "type": "integer"  }, 			"goods_status":     { "type": "byte"  }, 			"sort":          { "type": "integer" },			"encoded":       { "type": "string" ,"index": "not_analyzed" }, 			"encap":         { "type": "string" ,"index": "not_analyzed" }, 			"delivery_place":      { "type": "byte" }		  }		}	}}';
        $res = json_decode($str,true);
        $index_name = array(9=>'aipco',11=>'bisco');//根据实际情况进行填补
        foreach($index_name as $v){
            $params = [
                'index' => $v,
                'body' => $res
            ];
            $ss = $this->client->indices()->create($params);dump($ss);
        }
        
    }
    //sku查询
    public function index(){  
        set_time_limit(0);
        //$this->getMouser('570-PICO-PI-IMX7');exit;
        $project_num = I('get.k'); //供应商标示 如12 表示 chip1stop
        $project = C("OTHER_DB.{$project_num}"); //获取供应商名称
        $q = I('get.keyword'); //型号关键词
        $stock = intval($_GET['kNums']);
        if(!$project || !$q) {
            return false;
        }
        $data_list = array();
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
        if($project=='company'){    //原厂联营数据，其中包括5个不同品牌

            $arr_brand = array('3729'=>'POWEREX','15'=>'MICROCHIP','2940'=>'MAXIM','12'=>'Linear','1023'=>'Coilcraft');
            if(isset($_GET['brand_id']) && $_GET['brand_id']>0){
                $res = $this->getGoodsId($project,$q,$stock,$_GET['brand_id']);
                if(!empty($res)){
                    $data_list[] = $res;
                }
            }else{
                foreach ($arr_brand as $k=>$v){
                    $res = $this->getGoodsId($project,$q,$stock,$k);
                    if(!empty($res)){
                        $data_list[] = $res;
                    }
                }
            }
                
        }else if($project == 'zhuanmai'){    //专卖数据、ti、竞调数据
            $res = $this->getGoodsId($project,$q,$stock);
            if(!empty($res)){
                $data_temp['data'][] = $res;
                $data_temp['status'] = 1;
            }else{
                $data_temp['data'][] = array();
                $data_temp['status'] = 0;
            }
            $res = $this->getGoodsId('liexin_ti',$q,0);//ti推广没有库存的限制
            if(!empty($res)){
                $data_temp['ti'] = $res['data'];
            }else{
                $data_temp['ti'] = array();
            }
            $data_temp['lianyin_status'] = 0;
            //竞调数据
            $data_temp['temp_goods'] = $this->getTempData($q,$stock);
            
            $data_list['data'] = $data_temp;
            $data_list['err_code'] = 0;
            $data_list['err_msg'] = '';
        }else if($project == 'matches'){    //撮合数据
            $res = $this->handleMatches($q,$project);
            if(!empty($res)){
                $data_list = $res;
            }
        }else{             //其他联营数据
            $res = $this->getGoodsId($project,$q,$stock);
            if(!empty($res)){
                $data_list[] = $res;
            }
        }
        $this->returndata($data_list);
    }
    //输出数据处理
    public function returndata($finalResArr){
        if(isset($_GET['callback']) && !empty($_GET['callback'])){
            echo $_GET['callback'].'('.json_encode($finalResArr).')';
        }else{
            echo json_encode($finalResArr);
        }
    }
    //竟调数据获取
    public function getTempData($keyword,$stock_num){
        
        $m_goods = M('tmp_goods', 'lie_', C('MYSQL_POWER').'/liexin');
        //根据相关条件去出每个供应商的goods总条数
        $goods_where = array();
        $goods_where['goods_name'] = array('like',$keyword . '%');
        $goods_where['goods_number'] = array('egt',$stock_num);

        $res = $m_goods->where($goods_where)->select();
        $data_list = array();
        if(empty($res)){
            return $data_list;
        }
        foreach($res as $k=>$v){
            $temp = array();
            $temp['brand_name'] = $v['brand_name'];
            $temp['goods_id'] = $v['goods_id'];
            $temp['goods_name'] = $v['goods_name'];
            $temp['goods_name_temp'] =  str_ireplace($keyword, "<b class='f-red'>".strtoupper($keyword)."</b>",$v['goods_name']);
            $temp['increment'] = $v['increment'];
            $n1 = $v['moq']? $v['moq']:'--';
            $n2 = $v['goods_number']? $v['goods_number']:'--';
            $temp['stock'] = array($n1,$n2);
            $temp['company_name'] = $v['company_name'];
            $temp['company_id'] = $v['company_id'];
            $temp['tiered'][0] = array(0=>$n1,1=>$v['rmb_price'],2=>$v['rmb_price'],3=>$v['usd_price']);
//            $data_list[0]['data'][] = $temp;
//            $data_list[0]['DT'] = array();
//            $data_list[0]['PIUrl'] = '';
//            $data_list[0]['PUrl'] = '';
//            $data_list[0]['com_id'] = $v['company_id'];
//            $data_list[0]['com_name'] = $v['company_name'];
//            $v['company_id'] = intval($v['company_id']);
            $data_list[$v['company_id']]['data'][] = $temp;
            $data_list[$v['company_id']]['DT'] = array();
            $data_list[$v['company_id']]['PIUrl'] = '';
            $data_list[$v['company_id']]['PUrl'] = '';
            $data_list[$v['company_id']]['com_id'] = $v['company_id'];
            $data_list[$v['company_id']]['com_name'] = $v['company_name'];
        }
        if(!empty($data_list)){
            sort($data_list);
        }
        return $data_list;
    }
    //根据供应商名称获取，关键词、分页获取从es数据信息
    public function getGoodsId($project,$q,$stock,$brand_id=0){
        //查询条件 
        $params = array();
        $params['index'] = $project;//索引
        $params['type'] = 'goods';  //类型
        $params['body']['_source'] = array('goods_id',"old_goods_id"); //返回字段
        $params['body']['query']['bool']['must'][] = array('term'=>array('goods_name'=> strtoupper($q))); //根据关键词查找
        if($brand_id>0){
            $params['body']['query']['bool']['must'][] = array('terms'=>array('brand_id'=> $brand_id+0)); //根据品牌id查找
        }
        if($stock>0){
            $params['body']['query']['bool']['must'][] = array('range'=>array('stock'=>array('gte'=>$stock+0)));//根据库存查找
        }
        if ($project == 'digikey') { //digkey 超过四天不展示
            $time_after_first = time()-C('DIGKEY_TIME');
            $params['body']['query']['bool']['must'][] = array('range'=>array('update_time'=>array('gte'=>$time_after_first+0)));
        }
        //future,chip1stop,element14 更新时间超过2天的，不显示
        if ($project == 'future' || $project == 'chip1stop' || $project == 'element14') {
            $time_after_first = time()-C('FCE_TIME');
            $params['body']['query']['bool']['must'][] = array('range'=>array('update_time'=>array('gte'=>$time_after_first+0)));
        }
        //页码，第一页为2页，接下来以每页5条展示
        $pagesize = 2;
        $pagecur = !empty($_REQUEST['p'])?$_REQUEST['p']:1;
        $first = $pagesize*($pagecur-1);
        if($pagecur>1){
            $pagesize = 5;
            $first = $pagesize*($pagecur-1)-2-1;
        }
        $params['body']['from'] = $first;
        $params['body']['size'] = $pagesize;  

        $results = $this->client->search($params);
        //处理返回数据
        $temp = $results['hits']['hits'];
        $res = array();
        if($results['hits']['total']>0){
            $redis = new \Think\Cache\Driver\Redis();  
            foreach($temp as $k=>$v){
                //取出redis详细数据
                $redis_data = $redis->hGet('sku',$v['_source']['goods_id']);
                if(!empty($redis_data)){
                    $arr = json_decode($redis_data,true);
                    if($project == 'zhuanmai'){
                        $data = $this->handleDataZhuanMai($arr,$q,$project);
                        //如果满足要求的数据，即加入返回
                        if(count($data)>0){
                            $res['data_list'][] = $data;
                            $res['total'] = $results['hits']['total'];
                            $res['suplier_ids'] = 30;
                            $res['temp_com_id'] = 0;
                            $res['total_num'] = $results['hits']['total'];
                        }
                    }else{
                        $data = $this->handleData($arr,$q,$project);
                        //如果满足要求的数据，即加入返回
                        if(count($data)>0){
                            $res['data'][] = $data;
                            $res['total'] = $results['hits']['total'];
                            if($project == 'company'){
                                $temp_p = preg_replace('# #','',$data['brand_name']);
                                $res['DT'] = C("DT.company") ? C("DT.company") : array(); //期货
                                $res['PIUrl'] = C("PIUrl.{$temp_p}"); //供应商图片
                                $res['PUrl'] =  C("PUrl.{$temp_p}"); //供应商官网
                                $res['com_name'] = $data['brand_name']; //供应商
                                $res['brand_id'] = $brand_id; //品牌id,特殊处理
                            }else{
                                $res['DT'] = C("DT.{$project}") ? C("DT.{$project}") : array(); //期货
                                $res['PIUrl'] = C("PIUrl.{$project}"); //供应商图片
                                $res['PUrl'] =  C("PUrl.{$project}"); //供应商官网
                                $res['com_name'] = $project; //供应商
                                $res['brand_id'] = 0; //品牌id
                            }
                        }
                    } 
                } 
            }
        }
        return $res;
    }
    //撮合数据输出转换
    public function handleMatches($q,$project){
        //查询条件 
        $params = array();
        $params['index'] = $project;//索引
        $params['type'] = 'goods';  //类型
        $params['body']['_source'] = array('goods_id'); //返回字段
        $params['body']['query']['bool']['must'][] = array('term'=>array('goods_name'=> strtoupper($q))); //根据关键词查找
        
        //页码，第一页为2页，接下来以每页5条展示
        $pagesize = 2;
        $pagecur = !empty($_REQUEST['p'])?$_REQUEST['p']:1;
        $first = $pagesize*($pagecur-1);
        if($pagecur>1){
            $pagesize = 5;
            $first = $pagesize*($pagecur-1)-2-1;
        }
        $params['body']['from'] = $first;
        $params['body']['size'] = $pagesize;  

        $results = $this->client->search($params);
        //处理返回数据
        $temp = $results['hits']['hits'];
        $v3 = array();
        if($results['hits']['total']>0){
            foreach ($temp as $key => $value) { //获取商品id结果集
                $goodsArr[$key] = floatval($value['goods_id']);
            }
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
        }
        return $v3;    
    }
    //转换输出数据，用于专卖数据输出
    public function handleDataZhuanMai($arr,$q,$project){
        if(empty($res)){
            return array();
        }
        $arr = array();
        $arr['goods_id'] = $res['goods_id'];
        $arr['old_goods_id'] = $res['old_goods_id'];
        $arr['goods_name'] = $res['goods_name'];
        $arr['goods_name_temp'] =  str_ireplace($q, "<b class='f-red'>".strtoupper($q)."</b>",$res['goods_name']);
        $arr['brand_name'] = $res['brand_name'];
        $arr['com_name'] = '猎芯专卖';
        $arr['hk_delivery_time'] = $res['hk_delivery_time'];
        $arr['cn_delivery_time'] = $res['cn_delivery_time'];
        $arr['goods_number'] = $res['stock'];
        $arr['moq'] = $res['moq'];
        $arr['mpq'] = $res['mpq'];
        $arr['product_batch'] = $res['batch_sn'];
        $arr['sale_no'] = $res['encoded'];
        //价格参数处理
        $priceType = 1;
        if ($project == 'future') { //富昌欧洲仓（goods_sn 末尾含€€E）调用另一个欧洲价格系数
            $needle = '€€E';
            if (isset($res['goods_sn']) && strpos($res['goods_sn'], strval($needle))!==false) { //
                $priceType = 2;
            } 
        }
        if(count($res['ladder_price'])>0){
            foreach($res['ladder_price'] as $k => $v) {
                $temp = array();
                $temp['goods_num'] = $v['purchases'];
                $temp['dollor_price'] = $v['price_us'];
                $temp['rmb_price'] = $v['price_cn'];
                $arr['msg_price'][] = $temp;           
            }
        }else{
            $arr['msg_price'] = array();
        }
        //图片参数处理，如果商品没有图片，则使用spu的图片
        if($res['goods_images']){
            $img_arr = explode('|',$res['goods_images']);
            $arr['goods_img'] = $img_arr[0];
        }else{
            if($res['sku_img']){
                $img_arr = explode('|',$res['sku_img']);
                $arr['goods_img'] = $img_arr[0];
            }else{
                $arr['goods_img'] = '';
            }
        }
        return $arr;
    }
    //转换输出信息
    public function handleData($res,$q,$project){
        if(empty($res)){
            return array();
        }
        $arr = array();
        $arr['goods_id'] = $res['goods_id'];
        $arr['old_goods_id'] = $res['old_goods_id'];
        $arr['goods_name'] = $res['goods_name'];
        $arr['goods_name_temp'] =  str_ireplace($q, "<b class='f-red'>".strtoupper($q)."</b>",$res['goods_name']);
        $arr['brand_name'] = $res['brand_name'];
        $arr['increment'] = $res['mpq'];
        $arr['stock'] = array($res['moq'],$res['stock']);
        //价格参数处理
        $priceType = 1;
        if ($project == 'future') { //富昌欧洲仓（goods_sn 末尾含€€E）调用另一个欧洲价格系数
            $needle = '€€E';
            if (isset($res['goods_sn']) && strpos($res['goods_sn'], strval($needle))!==false) { //
                $priceType = 2;
            } 
        }
        $arr['tiered'] = getPriceArrByCode($project,$res['ladder_price'],$priceType);
        //图片参数处理，如果商品没有图片，则使用spu的图片
        if($res['goods_images']){
            $img_arr = explode('|',$res['goods_images']);
            $arr['goods_img'] = $img_arr[0];
        }else{
            if($res['sku_img']){
                $img_arr = explode('|',$res['sku_img']);
                $arr['goods_img'] = $img_arr[0];
            }else{
                $arr['goods_img'] = '';
            }
        }
        return $arr;
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
                    if(isset($_GET['kNums']) && $_GET['kNums']>$pd[$key]['stock'][1]){
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
            foreach ($priceArr as $k => $v) {
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
        $res = $mon->select('mouser',array('goods_sn'=>array('$in'=>$goods_sn_arr)),array('goods_sn','time','goods_id','brand_name'),array()); //根据数据表及条件查询结果
        $update_data = array();
        $insert_data = array();//将要插入的数据
        if(!empty($res)){
            foreach($res as $k=>$v){
                if(isset($goods_list[$v['goods_sn']]) && !empty($goods_list[$v['goods_sn']])){
                    $temp = time()-$v['time']-86400;
                    if($temp>0){
                        $redis_data = $redis->hGet('sku',$v['goods_id']);
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
                        $redis->hSet('sku',$v['goods_id'], json_encode($redis_arr));
                        
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
    public function insertMonAndMysql($insert_data,$temp_data){
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
}
    ?>
