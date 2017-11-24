<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\WsMessageSend;
class IndexController extends Controller {
	/**
	 * 灵感首页
	 * @param  int    $page  页数
	 * @return [array]     banner案例列表
	 */
	public function index($r=3){
		$url = C('PASE');
		//查询轮播图
		if(!$_POST['page']){
			$res = M('carousel')->where(array('state'=>1))->field('id,img,is_package')->select();
			foreach($res as $k => $v){
				$res[$k]['img'] = $url.$res[$k]['img'];
			}
			 $value['banner'] = $res;
		}
		 $page = $_POST['page']?$_POST['page']:1;
		 $show = M('package')->field('id,name,depict,style,lunbo_img')->order('edittime desc')->page($page,$r)->select();
		 //查询点赞数
		 foreach($show as $k=>$v){
		 	$show[$k]['num'] = M('collect')->where(array('collect_id'=>$show[$k]['id'],'type'=>1))->count();
		 }
		 $value['show'] = $show;
		 if($value == false){
		 	$value['state'] = 0;
		 	$value['error'] = '网络错误~';
		 }else{
		 	$value['state'] = 1;
		 }
		echo json_encode($value);
	}
	/**
	 * banner页详情
	 * @param [int]  id banner id
	 * @param  int    is_package   banner类型  1 h5页，2商品 ，3案例
	 * @param  int uid   用户id
	 */
	public function banner(){
		$data = I('post.');
		$url = C('PASE');
		if($data['is_package'] == 3){
			$d_id = M('carousel')->where(array('id'=>$data['id']))->getField('d_id');
			$content = M('carousel_h5')->where(array('id'=>$d_id))->getField('content');
		if(!$content){
			$str = <<<STR
		<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=100%, initial-scale=1.0, user-scalable=no"/>
<meta content="telephone=no" name="format-detection" />
<title></title>
<link href="http://mat1.gtimg.com/www/cssn/newsapp/wxnews/wechat20131204.css" rel="stylesheet" type="text/css">
</head>
<body>
<div id="content" class="main fontSize2">
<center><h1>没有找到相关信息~</h1></center>
</div>
</body>
</html>
STR;
			echo $str;
			exit;
		}
		$str = <<<STR
		<!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8"/>
<meta name="viewport" content="width=100%, initial-scale=1.0, user-scalable=no"/>
<meta content="telephone=no" name="format-detection" />
<title></title>
<link href="http://mat1.gtimg.com/www/cssn/newsapp/wxnews/wechat20131204.css" rel="stylesheet" type="text/css">
</head>
<style>
body { 
	background-color: transparent;
}
</style>
<body>
<div id="content" class="main fontSize2"><div style="width:100%;height:5px;"></div>
<div style="width:100%;height:2px;"></div>
<center>{$content}</center>
<div id="Comment"></div>
</div>
</body>
</html>
STR;
		echo $str;
		exit;
		}elseif($data['is_package'] == 2){

		}else{
			// 查询案例详情
			$d_id = M('carousel')->where(array('id'=>$data['id']))->getField('d_id');
			$value = M('package')->where(array('id'=>$d_id))->field('id,uid,style,depict,lunbo_img,addtime,fans,name,housetype')->find();
			if($value == false){
				$value['state'] = 0;
				$value['error'] = '查询出错';
			}else{
				$value['state'] = 1;
			}
			$value['img'] = M('packageimg')->where(array('pid'=>$value['id']))->getField('img',true);
			$res = M('user')->where(array('id'=>$value['uid']))->field('username,userpic')->find();
			$value['username'] = $res['username'];
			$value['userpic'] = $url.$res['userpic'];
			// 查询本案例用户是否收藏
			if($_POST['uid']){
				$int = M('collect')->where(array('uid'=>$uid,'collect_id'=>$id,'type'=>1))->getField('id');
				// echo M('collect')->_SQL();
				if($int){
					$value['is_collect'] = 1;
				}else{
					$value['is_collect'] = 0;
				}
			}else{
				$value['is_collect'] = 0;
			}
		}
		echo json_encode($value);
	}
	/**
	 * 案例，一站式，局部列表
	 * @param [int] [type]   1案例，2一站式，3局部
	 */
	public function list_search($r=3){
		$data = I('post.');
		$url = C('PASE');
		$page = $_POST['page']?$_POST['page']:1;
		$search = $_POST['search'];
		
		if($data['type'] == 1){
			$map['p.keyword|p.name|p.style|p.depict'] = array('like','%'.$search.'%');
			$value['list']  = M('Package')
			->join('as p LEFT JOIN yaj_packageimg d on p.id=d.pid')
			->field('p.id,p.lunbo_img,p.fans,p.name,d.img,p.style,p.depict')
			->where($map)->group('p.id')
			->order('p.click desc')
			->page($page,$r)
			->select();
		}else{
			$map['f.depict|f.name|f.style'] = array('like','%'.$search.'%');
			//局部和一站式
			if($data['type'] == 2){
				$map['is_topo'] = 1;
			}else{
				$map['is_topo'] = 0;
			}
			$value['list'] = M('fitment')
			->join('as f LEFT JOIN yaj_fitmentimg as d on f.id=d.pid')
			->field('f.name,f.style,f.fans,f.id,f.id,d.img,f.depict')
			->where($map)->group('f.id')
			->order('f.edittime')
			->page($page,$r)
			->select();
		}

		foreach($value['list'] as $k=>$v){
			$value['list'][$k]['img'] = $url.$value['list'][$k]['img'];
		}

		if($value == null){
			$value['state'] = 0;
			$value['error'] = '无此信息';
		}else{
			$value['state'] = 1;
		}
		echo json_encode($value);
	}
	/**
	 * 一站式装修/局部改造/案例详情   1案例  2一站式/局部
	 * @return [type] [description]
	 */
	public function detail_afflatus(){
		$data = I('post.');
		$url = C('PASE');
		if($data['type'] == 1){
			$value = M('package')->where(array('id'=>$data['id']))->where(array('state'=>1))->field('id,uid,name,depict,style,fans,subhead,keyword')->find();
			$img = m('packageimg')->where(array('pid'=>$value['id']))->getField('img',true);
			
		}else{
			$value = M('fitment')->where(array('id'=>$data['id']))->where(array('state'=>1))->field('id,uid,name,list,depict,fans,style')->find();
			$img = M('fitmentimg')->where(array('pid'=>$value['id']))->getField('img',true);
		}
			$user = M('user')->where(array('id'=>$value['uid']))->field('username,userpic')->find();
			foreach($img as $k=>$v){
				$img[$k] = $url.$img[$k];
			}
			$int = M('collect')->where(array('uid'=>$data['uid'],'collect_id'=>$id,'type'=>1))->getField('id');
			if($int){
				$value['is_collect'] = 1;
			}else{
				$value['is_collect'] = 0;
			}
			$value['list'] = $url.$value['list'];
			$value['username'] = $user['username'];
			$value['userpic'] = $url.$user['userpic'];
			$value['fitmentimg'] = $img;
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = "获取失败";
		}
		echo json_encode($value);
	}
	/**
	 * 收藏
	 * @return [type] [description]
	 * @param   int    type  1 案例 2 设计师 3施工队长 4 商品 5 装修效果图 6 设计师作品
	 * @param    int   collect_id     商品id
	 * @param    int    uid       收藏用户
	 */
	public function collect(){
		$uid = $_POST['uid'];
		$id = $_POST['id'];
		$type = $_POST['type'];
		$map['addtime'] = time();
		$map['uid'] = $uid;
		$map['collect_id'] = $id;
		$map['type'] = $type;
		$int = M('collect')->data($map)->add();
		if($type == 1){// 案例
			M('package')->where(array('id'=>$id))->setInc('fans');
		}elseif($type == 2 || $type == 3){// 设计师
			M('userdetails')->where(array('uid'=>$id))->setInc('fans');
		}elseif($type == 5){// 一站式装修
			M('fitment')->where(array('id'=>$id))->setInc('fans');
		}elseif($type == 4){// 商品
			M('goods')->where(array('id'=>$id))->setInc('fans');
		}else{// 设计师作品
			M('drawing')->where(array('id'=>$id))->setInc('fans');
		}
		if($int>0){
			$value['state'] = 1;
			$value['id'] = $id;
			$value['uid'] = $uid;
			$value['type'] = $type;
		}else{
			$value['state'] = 0;
			$value['error'] = '收藏失败~';
		}
		echo json_encode($value);
	}
	/**
	 * 取消收藏
	 */
	public function cancel_collect(){
		$data = I('post.');
		$int = M('collect')->where(array('uid'=>$data['uid'],'collect_id'=>$data['id'],'type'=>$data['type']))->delete();
		if($int){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '取消收藏失败';
		}
		echo json_encode($value);
	}
	/**
	 * 风格/擅长展示
	 * @param int type  类型（2设计师，3施工队）
	 * @return [type] [description]
	 */
	public function style(){
		$data = I('post.');
		$value['list'] = M('guild')->where(array('type'=>$data['type']))->field('id,name')->select();
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '读取失败';
		}
		
		echo json_encode($value);
	}
	/**
	 * 读取施工队服务地址
	 * @return [type] [description]
	 */
	public function serve(){
		$value['list'] = M('area')->where(array('level'=>1))->field('name')->select();
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '读取失败';
		}
		
		echo json_encode($value);
	}
	/**
	 * 施工队，设计师列表  2设计师，3施工队
	 */
	public function user_list($r=9){
		$data = I('post.');
		$url = C('PASE');
		$page = $_POST['page']?$_POST['page']:1;
		$search = $_POST['search'];
		$map['u.type'] = $_POST['type'];
		if($_POST['style']){
			$map['d.style'] = $_POST['style'];
		}
		//查询设计师列表，按地域排序，之后再按注册倒序
		if($data['type'] == 2){
			if($_POST['serve']){
				$map['d.serve'] = $_POST['serve'];
			}
			$res = M('user')
			->join('as u LEFT JOIN yaj_userdetails d on u.id=d.uid')
			->where($map)
			->field('u.id,u.userpic,u.username,d.style,d.worktime,d.education')->order('d.worktime desc')
			->select();
			if($_POST['serve']){
				$map['d.serve'] = array('neq',$data['serve']);
				$res1 = M('user')
				->join('as u LEFT JOIN yaj_userdetails d on u.id=d.uid')
				->where($map)
				->field('u.id,u.username,d.style,d.worktime,d.education')->order('d.worktime desc')
				->select();
			}
			$value['list'] = array_merge((array)$res,(array)$res1);
			// 查询案例图
			foreach($value['list'] as $k=>$v){
				$info = M('drawing')->where(array('stylistId'=>$value['list'][$k]['id'],'state'=>1))->field('id,name,depict')->find();

				$value['list'][$k]['name'] = $info['name'];
				$value['list'][$k]['depict'] = $info['depict'];
				$img = M('drawingimg')->where(array('pid'=>$info['id']))->field('img')->limit(3)->select();
				foreach($img as $k => $v){
					$img[$k] = $url.$img[$k]['img'];
				}
				$value['list'][$k]['img'] = $img;
			}
		}else{
			//查询施工队，全国，评分最高4+，成单最多，如相同倒序
			if($_POST['style']){
				$map['d.style'] = $_POST['style'];
			}
			//服务地点
			if($_POST['serve']){
				$map['d.serve'] = $_POST['serve'];
			}
			//全国施工队
			$res = M('user')
			->join('as u LEFT JOIN yaj_userdetails d on u.id=d.uid')
			->where(array('u.type'=>3,'d.is_all'=>1))
			->field('u.id,u.username,d.style,d.worktime,d.education,u.userpic,d.process,d.place,d.serve,d.grade')->order('d.worktime desc,d.num desc')
			->select();
			$map['d.is_all'] = array('neq',1);
			$map['d.grade'] = array('gt',4);
			//评分高于4分
			$res1 = M('user')
			->join('as u LEFT JOIN yaj_userdetails d on u.id=d.uid')
			->where($map)
			->field('u.id,u.username,d.style,d.worktime,d.education,u.userpic,d.process,d.place,d.serve,d.grade')->order('d.worktime desc,d.num desc')
			->select();
			$map['d.grade'] = array('lt',4);
			//成单量排序，
			$res2 = M('user')
			->join('as u LEFT JOIN yaj_userdetails d on u.id=d.uid')
			->where($map)
			->field('u.id,u.username,d.style,d.worktime,d.education,u.userpic,d.process,d.place,d.serve,d.grade')->order('d.worktime desc,d.num desc')
			->select();
			$value['list'] = array_merge((array)$res,(array)$res1,(array)$res2);
		}
		//计算出工龄，二十年以上
		foreach($value['list'] as $k=>$v){
			$dao = date('Y',$value[$k]['worktime']);
			$dang = date('Y',time());
			$expire = ($dang-$dao);
			if($expire > 20){
				$value['list'][$k]['worktime'] = '二十年以上';
			}else{
				$value['list'][$k]['worktime'] = $expire;
			}
			$value['list'][$k]['userpic'] = $url.$value['list'][$k]['userpic'];
		}
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = "网络错误";
		}
		// $value = array_values($value);
		echo json_encode($value); 
	}
	/**
	 * 设计师，施工队详情
	 * @return [type] [description]
	 */
	public function details(){
		$data = I('post.');
		$url = C('PASE');
		//查基础信息
		$user = M('user')->where(array('id'=>$data['uid']))->field('username,userpic,address,sex,type,signature,introduce')->find();
		$user['userpic'] = $url.$user['userpic'];
		//查扩展信息
		if($user['type'] == 2){
			$userdetails = M('userdetails')->where(array('uid'=>$data['uid']))->field('min,max,worktime,goods,education,grade,process,background')->find();
		}else{
			$userdetails = M('userdetails')->where(array('uid'=>$data['uid']))->field('min,max,worktime,goods,place,grade,process,education,background,area')->find();
		}
		$userdetails['background'] = $url.$userdetails['background'];
		$userdetails['goods'] = explode(',',$userdetails['goods']);
		//修改工龄
		$dao = date('Y',$userdetails['worktime']);
		$dang = date('Y',time());
		$expire = ($dang-$dao);
		if($expire > 20){
			$userdetails['worktime'] = '二十年以上';
		}else{
			$userdetails['worktime'] = "$expire"."年";
		}
		//查评论总数
		$num = M('evaluate')->where(array('s_id'=>$data['uid']))->count();
		//查1条评论内容
		$s_id = M('evaluate')->where(array('s_id'=>$data['uid'],'status'=>'1'))->order('addtime')->field('uid,content')->find();
		//查询评论人头像昵称
		$userinfo = M('user')->where(array('id'=>$s_id['uid']))->field('userpic,username')->find();
		$info['workpic'] = $url.$userinfo['userpic'];
		$info['name'] = $userinfo['username'];
		if($s_id){
			$value['list'] = array_merge($user,$userdetails,$s_id,$info);
		}else{
			$value['list'] = array_merge($user,$userdetails,$info);
		}
		$value['list']['address'] = "";//  评论人地址
		$value['list']['num'] = $num;
		// echo json_encode($userinfo);
		// exit;
		if(!$value){
			$value['state'] = 0;
			$value['error'] = '网络错误';
			echo json_encode($value);
			exit;
		}
		//查案例详情
		$pid = M('drawing')->where(array('to_uid'=>$data['uid'],'state'=>1))->field('id,name,depict,style,acreage,fans')->select();
		// 查案例图
		if($pid){
			foreach($pid as $k=>$v){
				$test[$k]['name'] = $v['name'];
				$test[$k]['id'] = $v['id'];
				$test[$k]['depict'] = $v['depict'];
				$test[$k]['style'] = $v['style'];
				$test[$k]['acreage'] = $v['acreage'];
				$test[$k]['fans'] = $v['fans'];
				$test[$k]['housetype'] = '';
				$test[$k]['img'] = M('drawingimg')->where(array('pid'=>$v['id']))->getField('img');
				for($i=0;$i<count($test[$k]['img']);$i++){
					$test[$k]['img'] = $url.$test[$k]['img'];  
				}
				$value['case'][] = $test[$k];
				unset($test[$k]);
			}
		}else{
			$value['case'] = array();
		}
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = "读取失败";
		}
		echo json_encode($value);
		exit;
	}
}