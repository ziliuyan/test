<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\WsMessageSend;
class ShopController extends Controller {
    /**
     * 商城首页
     * @param  integer $r [description]
     * @return [type]     [description]
     */
    public function index($r=5){
    	$data = I('post.');
        $url = C('PASE');
        $page = $_POST['page']?$_POST['page']:1;
        $id = array();
        // 最上方banner图
        $banner = M('goods_banner')->where(array('type'=>1))->getField('img');
        // 分类
        $classify = M('classify')->where(array('pid'=>0,'state'=>1,'is_show'=>1,'state'=>1))->limit('3')->field('id,name,img')->select();
        foreach($classify as $k => $v){
            $classify[$k]['img'] = $url.$classify[$k]['img'];
        }
        // 下发图片
        $below = M('goods_banner')->where(array('type'=>2,'state'=>1))->order('addtime')->field('id,img')->select();

        foreach($below as $k => $v){
            $below[$k]['img'] = $url.$below[$k]['img'];
        }
        // 定制商品
        $make = M('goods_make')->where(array('state'=>1))->order('addtime')->field('id,img,price,unit,name,brand')->select();
        foreach($make as $k => $v){
            $make[$k]['img'] = $url.$make[$k]['img'];
        }
        // 热门商品
        $collect = M('collect')->where(array('uid'=>$data['uid'],'type'=>4))->limit('3')->getField('collect_id',true);
        $num = count($collect);
        if($num == 3){
        	$map['_string'] = "id <> $collect[0] and id <> $collect[1] and id <> $collect[2]";
        }elseif($num == 2){
        	$map['_string'] = "id <> $collect[0] and id <> $collect[1]";
        }elseif($num == 1){ 
        	$map['_string'] = "id <> $collect[0]";
        }
        
        $mycollect = M('collect')->where($map)->group('collect_id')->order('count desc')->limit('3')->field('count(*) as count,collect_id')->select();
        foreach($collect as $k => $v){
        	$id1 = M('goods')->where(array('id'=>$collect[$k],'state'=>1))->field('id')->select();
        	if(!$id){
        		$id = $id1;
        	}else{
        		$id = array_merge($id,$id1);
        		unset($id1);
        	}
        }
        foreach($mycollect as $k => $v){
        	$id2 = M('goods')->where(array('state'=>1,'id'=>$mycollect[$k]['collect_id']))->field('id')->select();
        	if(!$id){
        		$id = $id2;
        	}else{
        		$id = array_merge($id,$id2);
        		unset($id2);
        	}
        }
        $collect_id = M('collect')->field('collect_id')->getField('collect_id',true);
        $con['id'] = array('not in',$collect_id);
        $id3 = M('goods')->where($con)->field('id')->select();
        if(!$id){
        		$id = $id3;
        	}else{
        		$id = array_merge($id,$id3);
        		unset($id3);
        	}
        foreach($id as $k => $v){
        	$goods['list'][$k] = M('goods')->where(array('id'=>$id[$k]['id']))->page($page,$r)->field('id,name,presentation,price,classifyId')->find();
        }
        foreach($goods['list'] as $k => $v){
            $goods['list'][$k]['presentation'] = $url.$goods['list'][$k]['presentation'];
            $goods['list'][$k]['class'] = M('classify')->where(array('id'=>$goods['list'][$k]['classifyId']))->getField('name');
        }
        $value['banner'] = $url.$banner;
        $value['classify'] = $classify;
        $value['below'] = $below; 
        $value['make'] = $make;
        $value['list'] = $goods['list'];
        if($value['list']){
            $value['state'] = 1;
        }else{
            $value['state'] = 0;
            $value['error'] = "商品读取失败";
        }
        echo json_encode($value);
    }
    /**
     * 定制详情
     * @return [type] [description]
     */
    public function makeMetails(){
        $data = I('post.');
        $goods_make = M('goods_make')->where(array('id'=>$data['id']))->field('id,name,min,max,brand,market,details,show_img')->find();
        $goods_make['is_collect'] = M('collect')->where(array('collect_id'=>$goods_make['id'],'type'=>7))->field('id')->find();
        if($goods_make){
            $value['goods'] = $goods_make;
            $value['state'] = 1;
        }else{
            $value['state'] = 0;
            $value['error'] = "读取错误";
        }
        echo json_encode($value);
    }
    /**
     * 加入购物车
     * @return [type] [int]   2定制商品，1普通商品
     */
    public function addshopping(){
        $map['goods_id'] = $_POST['goods_id'];      // 商品id
        $map['uid'] = $_POST['uid'];    // 用户id
        if($_POST['colorId']){
            $map['color'] = $_POST['colorId'];
        }
        if($_POST['standardId']){
            $map['standard'] = $_POST['standardId'];
        }
        if($_POST['capacityId']){
            $map['capacity'] = $_POST['capacityId'];
        }
        if(!$map['uid'] || !$map['goods_id']){
            $value['state'] = 0;
            $value['error'] = '请添加商品或登录~';
            echo json_encode($value);
            exit;
        }
        $map['type'] = $_POST['type'];
        // 查询购物车是否有此商品
        $num = M('goods_shoping')->where($map)->getField('id');
        $map['num'] = $_POST['num']?$_POST['num']:1; // 商品数量
        $map['addtime'] = time();
        if($num){
            $int = M('goods_shoping')->where(array('id'=>$num))->setInc('num',$map['num']); 
        }else{
            $int = M('goods_shoping')->data($map)->add();
        }
        if($int > 0){
            $value['state'] = 1;
            $value['goods_id'] = $map['goods_id'];
        }else{
            $value['state'] = 0;
            $value['error'] = "加入购物车失败";
        }
        echo json_encode($value);
    }
    /**
     * 可定制信息
     * @return [type] [description]
     */
    public function makeInfo(){
        $data = I('post.');
        $value['list'] = M('goods_make_info')->where(array('goods_id'=>$data['goods_id']))->field('id,property,criterion,info,goods_id,img')->select();
        if($value){
            $value['state'] = 0;
        }else{
            $value['state'] = 1;
            $value['error'] = "读取错误";
        }
        echo json_encode($value);
    }
    /**
     * 保存定制信息
     * @return [type] [description]
     */
    public function makeSave(){
        $data = I('post.');
        $property = M('goods_make_info')->where(array('pid'=>$data['goods_id']))->field('property')->select();
        foreach($property as $k => $v){
            $map['pid'] = $property[$k]['id'];
            $map['goods_id'] = $data['goods_id'];
            // $map['info'] = 
        }
        echo json_encode($property);
    }
    /**
     * 类别
     * @return [type] [description]
     */
    public  function classify(){
        $value['list'] = M('classify')->where(array('pid'=>0))->limit('3')->field('id,name')->select();
        if($value){
            $value['state'] = 1;
        }else{
            $value['state'] = 0;
            $value['error'] = "类别读取失败";
        }

    }
    /**
    *   商品列表
    *   id   分类id 
    *   is_best 推荐（默认）
    *   desc    价格高至低
    *   asc     价格低至高
    *   hot  销量
    *   type 1 从商品分类（筛选）  2 从商品活动   
    * 需要返回商品对应的顶级分类，筛选数据数据的加载用
    */
    public function goodslist($p = 1,$r = 15){
        // 默认是推荐的
        // dump($_POST);
        $url = C('PASE');
        $page = $_POST['page']?$_POST['page']:1;
        $type = $_POST['type'];
        $map['state'] = 1;
        // 是否有搜索条件
        if($_POST['search']){
            $search = $_POST['search'];
            if($_POST['id']){
                $list['name'] = array('like',"%".$search."%");
                $root_id = M('classify')->where($list)->getField('id',true);
                $sousuo = implode(',',$root_id);
                if(!$sousuo){
                    $map['keywords|name'] = array('like',"%".$search."%");
                }else{
                    $map['root_id'] = array('in',$sousuo);
                }
            }else{
                $map['keywords|name'] = array('like',"%".$search."%");
            }
        }
        if($type == 1){
            // 活动
            $goods = M('shopactivity')->where(array('id'=>$_POST['id']))->field('goodsid,img')->find();
            $map['classifyId'] = $goods['goodsid'];
            $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order($order)->page($page,$r)->select();
            // dump($res);
            foreach($res as $k=>$v){
                $res[$k]['img'] = $url.$v['img'];
                if(!$v['price']){
                    $res[$k]['price'] = '';
                }
            }
            if($res){
                $value['list'] = $res;
            }else{
                $value['list'] = array();
            }
            $value['img'] = $url.$goods['img'];  // 活动图
        }else{  
            if($_POST['id'] != ''){
                //顶级分类id
                $map['root_id'] = $value['pid'] = $_POST['id'];
            }
            $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc')->page($page,$r)->select();
            //按照销量排序
            if($_POST['hot']){
                // 价格排序
                if($_POST['desc']){
                    $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc,price desc,sales desc')->page($page,$r)->select();
                }elseif($_POST['asc']){
                    $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc,price asc,sales desc')->page($page,$r)->select();
                }else{
                    $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc,sales desc')->page($page,$r)->select();
                }

            }
            if($_POST['cold']){
                if($_POST['desc']){
                    $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc,price desc,sales desc')->page($page,$r)->select();
                }elseif($_POST['asc']){
                    $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc,price asc,sales desc')->page($page,$r)->select();
                }else{
                    $res = M('goods')->where($map)->field('id,name,img,price,brand_id')->order('is_best desc,add_time desc,sales asc')->page($page,$r)->select();
                }
            }
            foreach($res as $k=>$v){
                $res[$k]['img'] = $url.$v['img'];
                if(!$v['price']){
                    $res[$k]['price'] = '';
                }
                $res[$k]['banner'] = M('goods_brand')->where(array('id'=>$res[$k]['brand_id']))->getField('brandname');
            }
            $num = M('goods')->where($map)->count();
            if($res){
                $value['list'] = $res;
            }else{
                $value['list'] = array();
            }
            // 返回分类id
            $value['num'] = $num;
            $value['page'] = $page;
        }
        $value['state'] = 1;
        echo json_encode($value);
    }
    /**
     * 商品详情页
     * @return [type] [description]
     */
    public function goods_details(){
        $id = $_POST['id'];
        $url = C('PASE');
        // 商品点击量加1
        M('goods')->where(array('id'=>$id))->setInc('click_count');
                                                                            // 介绍        实价      轮播图    详情图                              
        $res = M('goods')->where(array('id'=>$_POST['id'],'state'=>1))->field('id,name,price,,showimg,stock')->find();
        // 商品详情页轮播图
        $showimg = explode(',',$res['showimg']);
        for($i=0;$i<count($showimg);$i++){
            $showimg[$i]  = $url.$showimg[$i];
        }
        $res['showimg'] = $showimg;
        // 优惠（coupon）
        // 获取商品所属分类
        // $classifyId = M('goods')->where(array('id'=>$id))->getField('classifyId');
        // $label = M('label')->where(array('pid'=>$classifyId))->field('id,label')->find(); // 优惠标签
        // $details = M('labelsmall')->where(array('pid'=>$label['id']))->field('oprice,cprice')->select();  // 优惠内容
        // foreach($details as $k=>$v){
        //     $list[] = '满'.$v['oprice'].'减'.$v['cprice'];
        // }
        // // dump($list);
        // unset($details);
        // if($label && $list){
        //     $details['list'] = implode(',',$list);
        //     $details['label'] = $label['label'];
        // }else{
        //     $details['list'] = '';
        //     $details['label'] = '';
        // }
        // dump($details);
        // 查询商品规格名                                          数据库存的是规格参数的ID
        $con = M('goodsconfig')->where(array('gid'=>$id))->field('color,standard,capacity')->find();
        if($con){
            if($con['color']){
                $father = M('configvalue')->where(array('id'=>$con['color']))->getField('pid');
                $config[] = M('configname')->where(array('id'=>$father))->getField('name'); // 第一个规格参数名
            }
            if($con['standard']){
                $father = M('configvalue')->where(array('id'=>$con['standard']))->getField('pid');
                $config[] = M('configname')->where(array('id'=>$father))->getField('name');
            }
            if($con['capacity']){
                $father = M('configvalue')->where(array('id'=>$con['capacity']))->getField('pid');
                $config[] = M('configname')->where(array('id'=>$father))->getField('name');
            }           
        }else{
            $config = array();
        }

        // 查询用户是否收藏
        if($_POST['uid']){
            $is_collect = M('collect')->where(array('uid'=>$_POST['uid'],'collect_id'=>$_POST['id'],'type'=>4))->find();
            if($is_collect){
                $value['is_collect'] = 1;
            }else{
                $value['is_collect'] = 0;
            }
        }else{
            $value['is_collect'] = 0;
        }
        $value['state'] = 1;
        $value['res'] = $res;
        // $value['coupon'] = $details;
        $value['config'] = $config;
        echo json_encode($value);
    }
    // 商品规格弹框
    public function goodsconfig(){
        $gid = $_POST['goods_id']; // 商品id
        // var_dump($gid);
        // exit;
        $url = C('PASE');
        // 规格id
        // 根据商品ID查询规格1、2、3
        $config = M('goodsconfig')->where(array('gid'=>$gid))->field('color,standard,capacity')->select();
        //根据商品ID查询价格，库存、规格图
        $res = M('goodsconfig')->where(array('gid'=>$gid))->field('productimg,stock,price')->find();
        $value['productimg'] = $url.$res['productimg'];
        //新建数组，将商品信息放入数组
        $value['name'] = M('goods')->where(array('id'=>$gid))->getField('name');
        $value['goods_id'] = $gid;
        $value['stock'] = $res['stock'];
        $value['price'] = $res['price'];
        foreach($config as $k=>$v){
            // 规格1
            $i = 0;
            if($v['color'] != ''){
                $value['color']['value'][$k]['color'] = M('configvalue')->where(array('id'=>$v['color']))->getField('value');
                $value['color']['value'][$k]['colorId'] = $v['color'];
                $colorPid = M('configvalue')->where(array('id'=>$v['color']))->getField('pid');
                $value['color']['name'] = M('configname')->where(array('id'=>$colorPid))->getField('name');
                $value['color']['value'] = my_array_unique($value['color']['value'],'color');
                $value['color']['value'] = array_values($value['color']['value']);
                $i++;
            }
            // 规格2
            if($v['standard'] != ''){
                $value['standard']['value'][$k]['standard'] = M('configvalue')->where(array('id'=>$v['standard']))->getField('value');
                $value['standard']['value'][$k]['standardId'] = $v['standard'];
                $standardPid = M('configvalue')->where(array('id'=>$v['standard']))->getField('pid');
                $value['standard']['name'] = M('configname')->where(array('id'=>$standardPid))->getField('name');
                $value['standard']['value'] = my_array_unique($value['standard']['value'],'standard');
                $value['standard']['value'] = array_values($value['standard']['value']);
                $i++;
            }
            // 规格3
            if($v['capacity'] != ''){
                $value['capacity']['value'][$k]['capacity'] = M('configvalue')->where(array('id'=>$v['capacity']))->getField('value');
                $value['capacity']['value'][$k]['capacityId'] = $v['capacity'];
                $capacityPid = M('configvalue')->where(array('id'=>$v['capacity']))->getField('pid');
                $value['capacity']['name'] = M('configname')->where(array('id'=>$capacityPid))->getField('name');
                $value['capacity']['value'] = my_array_unique($value['capacity']['value'],'capacity');
                $value['capacity']['value'] = array_values($value['capacity']['value']);
                $i++;
            }
        }
        $value['norms'] = $i;
        if($value != ''){
            $value['state'] = 1;
        }else{
            $value['state'] = 0;
            $value['error'] = '加载失败';
        }
        // dump($value);
        echo json_encode($value);
        // print_r($value);
    }
    /**
    *   选中规格时图片，价格，库存变
    *   colorId  规格1
    *   standardId  规格2
    *   capacityId  规格3
    *   goods_id    商品id
    *   norms   规格个数
    *   当商品对应的规格全选中时返回该商品规格对应的价格.图片.库存
    *   当未选中全部是返回有选中规格的对应其他规格id数组，未返回的规格置灰
    */ 
    public function selected(){
        $data = I('post.');
        // sdfasdf
        $url = C('PASE');
        $colorId = $data['colorId'];    // 规格1
        // dump($data);
        // dump($colorId);
        $standardId = $data['standardId'];  // 规格2
        $capacityId = $data['capacityId'];  // 规格3
        $goods_id = $data['goods_id'];  // 商品id
        $norms = $data['norms'];    // 规格个数
        $map['gid'] = $data['goods_id'];
        // dump($map['gid']);
        // 计算传递过来的个数
        unset($data['goods_id']);
        unset($data['norms']);
        $number = count($data);
        if($norms == $number){// 商品规格已传完，返回商品对应的库存，价格，以及商品图
            if($colorId){
                $map['color'] = $colorId;
            }
            if($standardId){
                $map['standard'] = $standardId;
            }
            if($capacityId){
                $map['capacity'] = $capacityId;
            }
            $res = M('goodsconfig')->where($map)->field('price,stock,productimg')->find();
            $value['price'] = $res['price'];
            $value['stock'] = $res['stock'];
            $value['productimg'] = $url.$res['productimg'];
        }else{
            // 规格未传完，返回其他规格
            if($colorId){
                $map['color'] = $colorId;
            }
            if($standardId){
                $map['standard'] = $standardId;
            }
            if($capacityId){
                $map['capacity'] = $capacityId;
            }
            if(!$colorId){
                $color = M('goodsconfig')->where($map)->getField('color',true);
            }
            if(!$standardId){
                $standard = M('goodsconfig')->where($map)->getField('standard',true);
                if(!$standard){
                    unset($standard);
                }
            }
            if(!$capacityId){
                $capacity = M('goodsconfig')->where($map)->getField('capacity',true);
                // dump($capacity);
                if(!$capacity){
                    unset($capacity);
                }
            }
            if($color[0]){
                $color = array_unique($color);
                $color = array_values($color);
                $value['color'] = $color;
            }
            if($standard[0]){
                $standard = array_unique($standard);
                $standard = array_values($standard);
                $value['standard'] = $standard;
            }
            if($capacity[0]){
                $capacity = array_unique($capacity);
                $capacity = array_values($capacity);
                $value['capacity'] = $capacity;
            }           
        }
        $map['gid'] = $goods_id;
        $value['state'] = 1;
        echo json_encode($value);
    }
    // 购物车
    public function shopping_cat(){
        $url = C('PASE');
        $page = I('get.page')?I('get.page'):1;
        $goods_id = M('shopping')-> where(array('uid'=>$_GET['uid']))->field('goods_id,num,id,color,capacity,standard')->page($page,$r)->select();
        $value['num'] = M('shopping')->where(array('uid'=>$_GET['uid']))->count();
        // 商品id集
        foreach($goods_id as $k=>$v){
            $res = M('product')->where(array('id'=>$v['goods_id']))->field('id,name,img,price,promote_price,state')->find();
            $goods[$k]['goods_id'] = $map['gid'] =  $v['goods_id'];     // 商品id
            $goods[$k]['id'] = $v['id'];
            if($v['color'] != ''){
                $goods[$k]['color'] = M('configvalue')->where(array('id'=>$v['color']))->getField('value');     // 规格1
                $goods[$k]['colorId'] = $map['color'] = $v['color'];
            }
            if($v['standard'] != ''){
                $goods[$k]['standard'] = M('configvalue')->where(array('id'=>$v['standard']))->getField('value');   // 规格2
                $goods[$k]['standardId'] = $map['standard'] = $v['standard'];
            }
            if($v['capacity'] != ''){
                $goods[$k]['capacity'] = M('configvalue')->where(array('id'=>$v['capacity']))->getField('value');   // 规格3
                $goods[$k]['capacityId'] = $map['capacity'] = $v['capacity'];
            }
            $config = M('goodsconfig')->where($map)->find();
            $goods[$k]['stock'] = $config['stock'];         // 库存
            if($config['stock'] < 0){
                $goods[$k]['state'] = 0;
            }else{
                $goods[$k]['state'] = 1;
            }
            // $state = M('product')->where(array('id'=>$v['goods_id']))->getField('state');
            if($res['state'] != 1){
                $goods[$k]['state'] = 0;
            }
            $goods[$k]['img'] = $url.$config['productimg']; // 商品缩略图
            $goods[$k]['name'] = $res['name'];          // 商品名
            $goods[$k]['num'] = $v['num'];
            // $goods[$k]['price'] = $config['relative'];       // 商品价格
            $goods[$k]['promote_price'] = $config['price']; // 原价格
        }
        if(!$goods){
            $value['goods'] = array();
        }else{
            $value['goods'] = $goods;
        }
        $value['state'] = 1;
        // dump($value);
        echo json_encode($value);
        
    }
    public function pay(){

    }
}