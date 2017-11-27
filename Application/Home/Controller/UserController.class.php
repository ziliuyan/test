<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\WsMessageSend;
class UserController extends Controller {
	/**
	 * 个人中心首页
	 * @return [type] [description]
	 */
	public function index(){
		$url = C('PASE');
		$data = I('post.');
		$type = M('user')->where(array('id'=>$data['uid']))->field('type')->find();
		if($type == 1){
			$user = M('user')->where(array('id'=>$data['uid']))->field('id,username,userpic,type,address')->find();
		}else{
			$user = M('user')->where(array('id'=>$data['uid']))->field('id,username,userpic,type')->find();
			$details = M('userdetails')->where(array('uid'=>$user['id']))->field('grade,process,goods')->find();
		}
		$user['userpic'] = $url.$user['userpic'];
		$value['list']['grade'] = $details['grade'];
		$value['list']['process'] = $details['process'];
		$value['list']['goods'] = $details['goods'];
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '个人信息读取失败';
		}
		echo json_encode($value);
	}
	/**
	 * 我的收藏
	 * type 1 灵感案例，2 一站式装修， 3设计师，4 施工队
	 * @return [type] [description]
	 */
	public function collect(){
		$url = C('PASE');
		$data = I('post.');
		$type = $data['type']?$data['type']:1;
		if($type == 1){
			$collect = M('collect')->where(array('type'=>1,'uid'=>$data['uid']))->getField('collect_id',true);
			foreach($collect as $k=>$v){
				// 查询灵感
				$value[$k] = M('package')->where(array('id'=>$collect[$k]))->field('name,lunbo_img')->find();
				// 查询收藏数
				$value[$k]['num'] = M('collect')->where(array('collect_id'=>$collect[$k],'type'=>1))->count();
				$value[$k]['lunbo_img'] = $url.$value[$k]['lunbo_img'];
			}
		}elseif($type == 2){
			// 一站式装修
			$collect = M('collect')->where(array('type'=>5,'uid'=>$data['uid']))->getField('collect_id',true);
			foreach($collect as $k=>$v){
				$value[$k] = M('fitment')->where(array('id'=>$collect[$k]))->field('name,list')->find();
				$value[$k]['num'] = M('collect')->where(array('collect_id'=>$collect[$k],'type'=>5))->count();
				$value[$k]['list'] = $url.$value[$k]['list'];
			}
		}else{
			// 设计师
			$collect = M('collect')->where(array('uid'=>$data['uid'],'type'=>2))->getField('collect_id',true);

			foreach($collect as $k=>$v){
				$value[$k] = M('user')
					->join('as u LEFT JOIN yaj_userdetails d on u.id=d.uid')
					->where(array('u.id'=>$collect[$k]))
					->field('u.id,u.userpic,u.username,d.style,d.worktime,d.education,d.process,d.place')->order('d.worktime desc')
					->find();
				$value[$k]['userpic'] = $url.$value[$k]['userpic'];
			}
		}
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '读取失败';
		}
		echo json_encode($value);
	}
	/**
	 * 添加房屋信息
	 */
	public function addAddress(){
		$data = I('post.');
		$map['uid'] = $data['uid'];
		$map['province'] = $data['province'];
		$map['city'] = $data['city'];
		$map['area'] = $data['area'];
		$map['detail'] = $data['detail'];
		$map['addtime'] = time();
		if($data['house']){
			//拼接户型
			$map['house'] = housetype($_POST['shi'],$_POST['ting'],$_POST['wei'],$_POST['chu'],$_POST['yangtai']);;
			$map['acreage'] = $data['acreage'];
			$map['house_img'] = get_img($_FILES['house_img']);
		}
		$int = M('house')->add($map);
		if($int){
			$value['state'] = 1;
			$value['succeed'] = '房屋信息添加成功';
		}else{
			$value['state'] = 0;
			$value['error'] = '房屋信息填失败';
		}
		echo json_encode($value);
	}
	/**
	 * 用户上传图纸
	 */
	public function addDrawing(){
		if(!$_POST['name'] ){
			$value['state'] = 0;
			$value['error'] = '资料不完整';
			echo json_encode($value);
			exit;
		}
		$data['name'] = $_POST['name'];
		$data['uid'] = $_POST['uid'];
		$data['stylistId'] = $_POST['stylistId'];
		$data['addtime'] = time();
		$data['state'] = 1;
		$pid = M('drawing')->add($data);
		$img = $_FILES['img'];
		$info = get_imgs($img);
      	foreach($info as $k=>$v){
        	$img = $v;
        	$map['img']=$img;
        	$map['pid']=$pid;
        	M('drawingimg')->add($map);
      	}
      	if($pid){
      		$value['id'] = $pid;
      		$value['state'] = 1;
      	}else{
      		$value['state'] = 0;
      		$value['error'] = '上传错误';
      	}
      	echo json_encode($value);
      	exit;
	}
	/**
	 * 设计师/施工队上传案例，标签选择
	 * @return [type] [description]
	 */
	public function style(){
		$uid = $_POST['uid'];
		$type = M('user')->where(array('id'=>$uid))->getField('type');
		$value['list'] = M('guild')->where(array('type'=>$type,'is_show'=>1))->field('name,id')->select();
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '暂无标签';
		}
		echo json_encode($value);
	}
	/**
	 * 设计师/施工队上传案例
	 * name  string   名称
	 * depict   string   详情
	 * style   string   风格
	 * uid   string  用户id
	 * stylistId   int  设计师id
	 */
	public function production(){
		if(!$_POST['name'] || !$_POST['depict'] || !$_POST['style'] || !$_FILES['img']){
			$value['state'] = 0;
			$value['error'] = '缺少参数';
			echo json_encode($value);
			exit;
		}
		$data['name'] = $_POST['name'];
		$data['depict'] = $_POST['depict'];
		$data['style'] = $_POST['style'];
		$data['state'] = 2;
		$data['uid'] = $_POST['uid'];
		$data['stylistId'] = $_POST['uid'];
		$data['addtime'] = time();
		$pid = M('drawing')->add($data);
		$img = $_FILES['img'];
		$info = get_imgs($img);
		foreach($info as $k=>$v){
        	$img = $v;
        	$map['img']=$img;
        	$map['pid']=$pid;
        	M('drawingimg')->add($map);
      	}
      	if($pid){
      		$value['id'] = $pid;
      		$value['state'] = 1;
      	}else{
      		$value['state'] = 0;
      		$value['error'] = '上传错误';
      	}
		echo json_encode($value);
	}
	/**
	 * 案例/图纸展示
	 */
	public function show(){
		$url = C('PASE');
		$data = I('post.');
		$type = M('user')->where(array('id'=>$data['uid']))->getField('type');
		$list = M('drawing')->where(array('uid'=>$data['uid']))->field('id,name')->select();
		foreach($list as $k=>$v){
			$list[$k]['img'] = M('drawingimg')->where(array('pid'=>$list[$k]['id']))->getField('img',true);
			$list[$k]['num'] = M('drawingimg')->where(array('pid'=>$list[$k]['id']))->count();
			foreach($list[$k]['img'] as $key=>$va){
				$list[$k]['img'][$key] = $url.$list[$k]['img'][$key];
			}
		}
		echo json_encode($list);
	}
	/**
	 * 地址展示
	 */
	public function address(){
		$url = C('PASE');
		$data = I('post.');
		$value['address'] = M('house')->where(array('uid'=>$data['uid']))->select();
		echo json_encode($address);
	}
}