<?php
namespace Home\Controller;

use Home\Controller\BaseController;

class TitleController extends BaseController
{
    
    //专题页
  public function title()
    {
        $option = array(
            'host'       => C('FS_REDIS_HOST'),
            'port'       => C('FS_REDIS_PORT'),
            'password'   => C('FS_REDIS_PASSWORD')
        );
        //根据供应商名称
        $re_info = $_SERVER['REQUEST_URI'];
        $arr_info = explode('/', $re_info);
        $supplier_name = $arr_info[2];
        $arr_id = array_keys(C('SUPERLIER_ALL'),$supplier_name);
        //赋予默认值
        if(count($arr_id)<=0){
            $supplier_name = 'mouser';
            $arr_id[0] = 14;
        }
        $supplier_id = $arr_id[0]>0? $arr_id[0]:0;
        
        //获取mouser的品牌
        $redis = new \Think\Cache\Driver\Redis($option);
        $res = $redis->get('hhs_'.$supplier_name.'_brand');
        $brand_name = $_GET['brand_name']? trim($_GET['brand_name']):'';
        $brand_id_temp = $_GET['brand_id']? $_GET['brand_id']:0;
        $brand_id = 0;
        //根据品牌名获取品牌id
        if($brand_name){
            foreach($res as $k=>$v){
                if(strtolower($brand_name) == strtolower($v)){
                    $brand_id = $k;          
                    break;
                }
            }
        }else if($brand_id_temp>0){
            foreach($res as $k=>$v){
                if($brand_id_temp == $k){
                    $brand_id = $k;          
                    break;
                }
            }
        }
            
        $data = array();
        $data['all_brand'] = $res;
        $this->assign('ClassList',$data);
        $this->assign('brand_id',intval($brand_id));
        $this->assign('supplier_id',intval($supplier_id));
        $this->assign('supplier_name',$supplier_name);
        $banner = $this->newsBrandAd('mouser_banner',2,$supplier_id);
        $this->assign('mouser_banner',$banner[0]);
        $banner_bottom = $this->newsBrandAd('mouser_banner',5,$supplier_id);
        $this->assign('mouser_banner_bottom',$banner_bottom[0]);
        $this->display();
    }
    //频道页
    public function index()
    {
        $this->nav('title');
        $cms = D('Cms');
        $where = array();
        $where['status'] = 1;
        $where['tag'] = 'mouser_template';
        $nav = $cms->getData('lie_template',$where,'tem_id,tem_name,ad_words,link_words,url,tag,class,sort','','sort desc');
        //根据供应商名称
        $re_info = $_SERVER['REQUEST_URI'];
        $arr_info = explode('/', $re_info);
        $supplier_name = $arr_info[2];
        $arr_id = array_keys(C('SUPERLIER_ALL'),$supplier_name);
        //赋予默认值
        if(count($arr_id)<=0){
            $supplier_name = 'mouser';
            $arr_id[0] = 14;
        }
        $supplier_id = $arr_id[0]>0? $arr_id[0]:0;
        $list = array();
        foreach($nav as $k=>$v){
            if($v['class'] == 'temp1'){
                $map = array();
                $map['tem_id'] = $v['tem_id'];
                $map['status'] = 1;
                $map['type'] = 5;
                $map['supplier_id'] = $supplier_id;
                $data = $this->getGoods($map);
                $list[$k]['mouser_nav'] = $v;
                $list[$k]['data_list'] = $data;
            }else if($v['class'] == 'temp2'){
                $map = array();
                $map['tem_id'] = $v['tem_id'];
                $map['status'] = 1;
                $map['type'] = 7;
                $map['company_id'] = $supplier_id;
                $data = $this->hotBrands($map);
                $list[$k]['mouser_nav'] = $v;
                $list[$k]['data_list'] = $data;
                $map = array();
                $map['tem_id'] = $v['tem_id'];
                $map['status'] = 1;
                $map['type'] = 6;
                $map['company_id'] = $supplier_id;
                $ad = $cms->getData('lie_base',$map,'*',3,'sort desc');
                $list[$k]['ad'] = $ad;
            }
        }
        
        $banner = $this->newsBrandAd('mouser_banner',1,$supplier_id);
        $bottom_ad = $this->newsBrandAd('mouser_banner',4,$supplier_id);
        $this->assign('mouser_list',$list);
        $this->assign('mouser_banner',$banner);
        $this->assign('bottom_ad',$bottom_ad[0]);
        $this->display();
    }
    
    /**
     * 首页热门品牌
     * @return [type] [description]
     */
    public function hotBrands($map)
    {
        $CmsModel = D('Cms');
        //取出热门品牌
        $brands = $CmsModel->getMonopolyGoods('lie_base',$map,'images,title,url,content,window_open','sort desc','8'); 
        if (!empty($brands)) {
//            $ids = '';
//            $data_list = array();
//            foreach($brands as $k=>$v){
//                $ids .= ','.$v['id'];
//                $data_list[$v['id']] = $v;
//            }
//            //$m_brand = D('brand');
//            $m_brand = D('Home/Supplier');
//            $where = array();
//            $ids = trim($ids,',');
//            $where['brand_id'] = array('in',$ids);$m_brand->getMsg('liexin_spu','brand',$where, 'brand_id,brand_name,brand_logo');
//            $brand_data = $m_brand->getMsg('liexin_spu','brand',$where, 'brand_id,brand_name,brand_logo');
//            if(empty($brand_data)){
//                return $this->apiReturn(1, '数据错误');
//            }
//            foreach($brand_data as $k=>$v){
//                $data_list[$v['brand_id']]['brand_name'] = $v['brand_name'] ? $v['brand_name']:'';
//                $data_list[$v['brand_id']]['brand_logo'] = $v['brand_logo'];
//            }
//            return $data_list;
            return $brands;
        } else {
            return array();
        }
    }
    /**
     * 首页广告位
     * @return [type] [description]
     */
    public function newsBrandAd($tag,$type,$company_id=0)
    {
        $m_goods = D('Cms');
        $sql = 'select a.title,a.content,a.url,a.images,a.window_open from lie_base as a left join lie_base_cat as b on a.bcat_id=b.bcat_id where b.status=1 and a.status=1 and b.tags="'.$tag.'" and a.type='.$type.' and a.company_id='.$company_id.' order by a.sort desc limit 5';
        $brand_data = $m_goods->query($sql);
        return $brand_data;
    }
    /**
     * 专卖lieshi商品输出
     * @return [type] [description]
     */
    public function getGoods($map)
    {
        $CmsModel = D('Cms');
        //取出优势物料
        $datas = $CmsModel->getMonopolyGoods('lie_goods_config',$map,'type_id as goods_id,goods_name,description,is_hot,url,active_words,window_open','sort desc','12');
        if (!empty($datas)) {
            $ids = '';
            $data_list = array();
            foreach($datas as $k=>$v){
                $option = array(
                    'host'       => C('FS_REDIS_HOST'),
                    'port'       => C('FS_REDIS_PORT'),
                    'password'   => C('FS_REDIS_PASSWORD')
                );
                $redis = new \Think\Cache\Driver\Redis($option);
                $redis_data = $redis->hGet('sku',$v['goods_id']);
                $redis_arr = json_decode($redis_data,true);
                //获取spu的信息
                $spu_data = $redis->hGet('spu',$redis_arr['spu_id']);
                $spu_arr = json_decode($spu_data,true);
                $redis_arr['sku_img'] = $spu_arr['images_l'];
                $redis_arr['goods_name'] = $spu_arr['spu_name'];
                $redis_arr['brand_id'] = $spu_arr['brand_id'];
                //品牌名称获取
                $redis_arr['brand_name'] = $redis->hget('brand',$spu_arr['brand_id']);

                $data_list[$v['goods_id']] = $v;
                if($redis_arr['ladder_price'] && !is_array($redis_arr['ladder_price'])){
                    $redis_arr['ladder_price'] = json_decode($redis_arr['ladder_price']);
                }
                if(isset($redis_arr['ladder_price'][0]['price_us']) && $redis_arr['ladder_price'][0]['price_us']>0){
                    $xi = C('PRICE_MULTI');
                    $redis_arr['ladder_price'][0]['price_cn'] = $redis_arr['ladder_price'][0]['price_us']*$xi['mouser']['cn'];
                    $redis_arr['ladder_price'][0]['price_cn'] = number_format($redis_arr['ladder_price'][0]['price_cn'], 4, '.', '');
                    $data_list[$v['goods_id']]['price'] = '￥'.$redis_arr['ladder_price'][0]['price_cn'];
                }else{
                    $data_list[$v['goods_id']]['price'] = '--';
                }
                    
                /*
                if(isset($redis_arr['ladder_price'][0]['price_cn']) && $redis_arr['ladder_price'][0]['price_cn']>0){
                    $redis_arr['ladder_price'][0]['price_cn'] = $redis_arr['ladder_price'][0]['price_us']*$xi['mouser']['cn'];
                    $data_list[$v['goods_id']]['price'] = '￥'.$redis_arr['ladder_price'][0]['price_cn'];
                }else{
                    $redis_arr['ladder_price'][0]['price_cn'] = $redis_arr['ladder_price'][0]['price_cn']*$xi['mouser']['cn'];
                    $data_list[$v['goods_id']]['price'] = $redis_arr['ladder_price'][0]['price_us']>0? '$'.$redis_arr['ladder_price'][0]['price_us']:'--';
                }
                */
                $data_list[$v['goods_id']]['goods_name'] = $v['goods_name'] ? $v['goods_name']:$redis_arr['goods_name'];
                $data_list[$v['goods_id']]['brand_name'] = $redis_arr['brand_name'];
                $data_list[$v['goods_id']]['brand_id'] = $redis_arr['brand_id'];
                //图片参数处理，如果商品没有图片，则使用spu的图片
                if($redis_arr['goods_images']){
                    $img_arr = explode('|',$redis_arr['goods_images']);
                    $data_list[$v['goods_id']]['default_img'] = $img_arr[0];
                }else{
                    if($redis_arr['sku_img']){
                        $img_arr = explode('|',$redis_arr['sku_img']);
                        $data_list[$v['goods_id']]['default_img'] = $img_arr[0];
                    }else{
                        $data_list[$v['goods_id']]['default_img'] = '';
                    }
                }
            }


            return $data_list;
        } else {
            return array();
        }
    }
}
