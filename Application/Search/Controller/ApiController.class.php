<?php
namespace Vendor\Xuns;//命名空间
namespace Search\Controller;
use Think\Controller;
use Search\Model\MonModel;

class ApiController extends Controller {

    public function index(){

    $resJson = isset($_POST['key']) && trim($_POST['key']) ? trim($_POST['key']) : ''; //获取json格式的字符串，格式：[{"goods_id":1300011476,"goods_name":"1709100201"},{"goods_id":1300011477,"goods_name":"1709100254"}]
    if (empty($resJson)) {
        return ;
    }
    $resArr = json_decode($resJson,true);

    $act = I('post.act'); //获取行为 add,delete,del
    $op = !isset($act) ? 'update' : $act;

    foreach ($resArr as $key => &$value) {
        $value['goods_id'] = floatval($value['goods_id']);
        $res = $this->batchXS($value,$op);
        var_dump($res);
    }

    echo "success";die;

    }

    //处理进迅搜
    public function batchXS($goodsInfo,$op){
        $goods_id = $goodsInfo['goods_id'];
        $goodsInfo['goods_name'] = concat_ic($goodsInfo['goods_name']);

        if(!$goods_id){
            return false;
        }

        $project = getPnByGoodsID($goods_id);//获取供应商名称
        if (!$project) {
            return false;
        }

        if($project){
            Vendor('XunS.XS');
            $xs = new \XS($project);
            $doc = new \XSDocument($goodsInfo);
            $index = $xs->index;

            //索引操作
            if($op != 'del'){
                $doc->setFields($goodsInfo);//批量设置字段值 这里是以合并方式赋值, 即不会清空已赋值并且不在参数中的字段.
            }

            if($op == 'add'){ //添加索引
                $res = $index->add($doc); // 添加文档，不检测便索引库内是否已有同一主键数据
            }elseif ($op == 'del'){ //删除索引
                $ret = $index->del($goods_id); // 删除主键值为如 2100747670 的文档
            }else{ //更新索引，默认
                $index->update($doc); // 更新文档，若有同主键数据则替换之
            }
            $index->flushIndex();//索引同步
            return true;
        }else{
            return false;
        }

    }

}