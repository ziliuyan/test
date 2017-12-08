<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\JPushZDY;
// header("Content-type: text/html; charset=utf-8"); 
class OrderController extends Controller
{
	/** 施工订单首页
	* type 0 未支付 1 进行中  2  已完成  3 未评论
	*/
	public function constructlist($page = 1,$r = 10){
		$data = I('get.');
		$page = $data['page']?$data['page']:1;
		// dump(C('PASE'));
		$url = C('PASE');
		$type = M('user')->where(array('id'=>$data['uid']))->getField('type');
		// $state = M('duei')->where(array)
		if($type == 1){// 用户
			$map['u_id'] = $data['uid'];
		}else{
			$map['c_id'] = $data['uid'];
		}
		$value['type'] = $type;// 施工队长
		
		// 默认为全部订单
		$type = $data['type'];
		
		// paynum 0未支付 1 2 3为进行中，4全款已付 5 已完成  6 待评价 7 已评价
		// if($type){
		// 	if($type == 1){ //未完成订单
		// 		$order_sn = M('constructorder')->where($map)->field('order_sn')->select();
		// 		foreach($order_sn as $k=>$v){
		// 			$value[] = $v;
		// 			$pid[] = M('flow')->where($order_sn = $v)->field('id')->select();

		// 		}
		// 		for($i=0;$i<count($pid);$i++){
		// 			$state = M('smallflow')->where(array('pid'=>$pid[$i]))->field('state')->select();
		// 			echo json_encode($pid[$i]['id']);
		// 			exit;
					
					
		// 		}
				
		// 	}
		// }
		
		
		$state = M('smallflow')->where($pid)->select();
		// 
		if($type){
			if($type == 1){// 进行中
				$map['order_type'] = array('in','2,3,4');
			}elseif($type == 2){// 已完成
				if($value['type'] == 1){// 业主
					$map['order_type'] = 6;
				}else{// 施工队长
					$map['order_type'] = array('in','4,5');
				}
			}elseif($type == 3){// 未评论，要完成并且未评论
				$map['order_type'] = 5;
			}else{
				$map['order_type'] = 1;
			}	
		}
		// 施工订单有效果图
		// 查询订单
		$value['dingdan'] = M('constructorder')->where($map)->order('addtime desc')->field('id,addtime,order_sn,amout,payint,f_id,x_id,order_type')->page($page,$r)->select();
		$kuan = M('constructorder')->where(array('u_id'=>$map['u_id']))
				->field('u_id,f_id,s_id,order_sn')
				->page($page,$r)->select();
		foreach($kuan as $k=>$v){
			$pid[] = M('flow')->where(array('order_sn'=>$kuan[$k]['order_sn']))
					->getField('id');
		}
		// $pid = M('flow')->where(array('order_sn'=>$kuan['order_sn']));
		$value['num'] = M('constructorder')->where($map)->count();
		//如果有订单
		if($value['dingdan']){
			foreach($value['dingdan'] as $k=>$v){
				//查询每次需付款金额
				$money = M('duei')
						->where(array('order_id'=>$v['id']))
						->field('percnet')->select();
				foreach($money as $key=>$val){
					$mo[$key]['percnet'] = round($v['amout']*$val['percnet']/100,2);
				}
				$value['dingdan'][$k]['money'] = $mo;

				//查询下次应付款金额
				// $jine = M('duei')->where(array('num'=>$value['dingdan'][$k]['payint']+1,'order_id'=>$value['dingdan'][$k]['id']))->getField('percnet');
				// $value['dingdan'][$k]['money'] = round($v['amout']*$jine/100,2);
				// 查询本次支付的订单号
				$pid = M('flow')->where(array('order_sn'=>$value['dingdan'][$k]['order_sn']))->getField('id');
				//查询是否可付款
				//付完尾款后，payint==100，不需要付款
				//查询总共付款次数
				$zong = M('duei')
						->where(array('order_id'=>$v['id']))
						->count();
				if($value['dingdan'][$k]['payint'] == 100){
					$value['dingdan'][$k]['is_pay'] = 1;
				}elseif($value['dingdan'][$k]['payint']+1 == $zong){
					$is_pay = M('smallflow')->where(array('pid'=>$pid,'pay'=>100,'type'=>1))->getField('state',true);
					if(in_array('1',$is_pay) || in_array('2',$is_pay)){
						$value['dingdan'][$k]['is_pay'] = 1;
					}else{
						$value['dingdan'][$k]['is_pay'] = 2;
					}
					//当订单状态为4时，不允许付款
					if($value['dingdan'][$k]['order_type'] == 4){
						$value['dingdan'][$k]['is_pay'] = 1;
					}
				}else{
					$is_pay = M('smallflow')->where(array('pid'=>$pid,'pay'=>$value['dingdan'][$k]['payint']+1,'type'=>1))->getField('state',true);
					if(in_array('1',$is_pay) || in_array('2',$is_pay)){
						$value['dingdan'][$k]['is_pay'] = 1;
					}else{
						$value['dingdan'][$k]['is_pay'] = 2;
					}
				}
				//没有支付尾款
		
				// ！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
				// 这里还要查询当前应付款
				// if($v['paynum'] < 10 ){ // 订单未付完款
				// 	// 查询下次应付金额百分比
				// 	if($v['payint'] == 0){
				// 		$sort = 1;
				// 	}elseif($v['payint'] == 1){
				// 		$sort = 2;
				// 	}elseif($v['payint'] == 2){
				// 		$sort = 3;
				// 	}else{
				// 		$sort = 4;
				// 	}
				// 	switch ($sort) {
				// 		case 1:
				// 			$bai = M('duei')->where(array('order_id'=>$v['id'],'type'=>3))->getField('one');
				// 		break;
				// 		case 2:
				// 			$bai = M('duei')->where(array('order_id'=>$v['id'],'type'=>3))->getField('two');
				// 		break;
				// 		case 3:
				// 			$bai = M('duei')->where(array('order_id'=>$v['id'],'type'=>3))->getField('three');
				// 		break;
				// 		default:
				// 			$bai = M('duei')->where(array('order_id'=>$v['id'],'type'=>3))->getField('four');
				// 		break;
				// 	}
				// 	$value['dingdan'][$k]['due'] = round($v['amout']*$bai/100,2); // 应付金额 = 总金额 * 百分比
				// }else{
				// 	$value['dingdan'][$k]['due']  = '';
				// }
				
				$value['dingdan'][$k]['addtime'] = date("Y-m-d H:i:s",$v['addtime']); // 订单添加时间
				$value['dingdan'][$k]['xiaoguo'] = $url.M('fitmentimg')->where(array('pid'=>$v['x_id']))->getField('img');  // 订单列表只取一张
				// 查询监管id
				if($v['order_type'] > 0){
					// 有监管
					if($value['type'] == 1){// 业主
						$j_id = M('flow')->where(array('uid'=>$data['uid'],'f_id'=>$v['f_id']))->getField('id');
					}else{
						$j_id = M('flow')->where(array('c_id'=>$data['uid'],'f_id'=>$v['f_id']))->getField('id');
					}
					$value['dingdan'][$k]['j_id'] = $v['f_id'];
					$value['dingdan'][$k]['k_id'] = M('smallflow')->where(array('pid'=>$j_id,'flowname'=>'开工进场'))->getField('id');
					$value['dingdan'][$k]['w_id'] = M('smallflow')->where(array('pid'=>$j_id,'flowname'=>'竣工'))->getField('id');
				}
				if($v['order_type'] == 7){
					// 查询所有的流程是否完成
					$complete = M('smallflow')->where(array('pid'=>$j_id,'flowname'=>array('neq','竣工')))->getField('state',true);

					if(!in_array(2,$complete)){
						if(!in_array(1,$complete)){
							$value['dingdan'][$k]['is_complete'] = 1;
						}else{
							$value['dingdan'][$k]['is_complete'] = 2;
						}
					}else{
						$value['dingdan'][$k]['is_complete'] = 2;
					}
				}

				// 合同编号
				$value['dingdan'][$k]['pact_sn'] = M('pact')->where(array('did'=>$v['id']))->getField('pact_sn');
				unset($value['dingdan'][$k]['x_id']);
				$affiliated = M('affiliated')->where(array('order_sn'=>$value['dingdan'][$k]['order_sn']))->order('sort asc')->getField('sort');
				$value['dingdan'][$k]['sort'] = $affiliated['sort'];
		
				if($affiliated){
					//have为2 ，存在附属订单
					$value['dingdan'][$k]['have'] = 1;
				}else{
					//不存在附属订单
					$value['dingdan'][$k]['have'] = 2;
				}
				if($v['order_type'] > 5){
					$value['dingdan'][$k]['payint'] = 100;
					$value['dingdan'][$k]['have'] = 2;
				}
			}
		}else{
			$value['dingdan'] = array();
		}

		$value['state'] = 1;
		echo json_encode($value);
	}

	/**
	* 施工订单详情
	* uid Internet 用户id	
	* id int  订单id
	*/
	public function constructorder(){
		$data = I('get.');	
		$url = C('PASE');
		$type = M('user')->where(array('id'=>$data['uid']))->getField('type');
		$res = M('constructorder')->where(array('id'=>$data['id']))->field('s_id,f_id,c_id,id,u_id,addtime,paynum,style,order_sn,state,amout,x_id,order_type,payint')->find();
		$pid = M('flow')->where(array('order_sn'=>$res['order_sn']))->getField('id');
		$fushu = M('affiliated')->where(array('order_sn'=>$res['order_sn']))->count();
		// $affiliated = M('affiliated')->where(array('order'=>$res['order_sn']))->getField('id');
		$affiliated = M('affiliated')->where(array('order_sn'=>$res['order_sn']))->getField('id');
		$zong = M('duei')->where(array('order_id'=>$data['id']))->count();
		//如果有附属订单，为1没有为2
		if($affiliated){
			$value['have'] = 1;
		}else{
			$value['have'] = 2;
		}
		//当payint==100时，不显示付款
		if($res['payint'] == 100){
			$value['is_pay'] = 1;
		}elseif($res['payint']+1 == $zong){
			$is_pay = M('smallflow')->where(array('pay'=>100,'pid'=>$pid,'type'=>1))->getField('state',true);
			if(in_array('1',$is_pay) || in_array('2',$is_pay)){
				$value['is_pay'] = 1;
			}else{
				$value['is_pay'] = 2;
			}
		}else{
			$is_pay = M('smallflow')->where(array('pay'=>$res['payint']+1,'pid'=>$pid,'type'=>1))->getField('state',true);
			if(in_array('1',$is_pay) || in_array('2',$is_pay)){
				$value['is_pay'] = 1;
			}else{
				$value['is_pay'] = 2;
			}
			
		}
			//订单状态为4时，不允许付款
		if($res['order_type'] == 4){
				$value['is_pay'] = 1;
			}
		if($type == 1){// 用户
			$map['u_id'] = $data['uid'];
			$value['type'] = 1;		// 返回前台
		}elseif($type == 2){// 设计师
			$value['type'] = 2;
			$value['state'] = 0;
			$value['error'] = '你是设计师，哪有施工订单啊！';
			$value['uid'] = $data['uid'];
			echo json_encode($value);
			exit;
		}else{// 施工队长
			$value['type'] = 3;//	施工队长 
			$map['c_id'] = $data['uid'];
		}
		$user = M('user')->where(array('id'=>$res['c_id']))->field('username,mobile')->find(); // 设计师信息	
		$f = M('budget')->where(array('id'=>$res['f_id']))->field('address,district,housetype,area')->find();// 施工队长有效果图
		// 效果图,查看报价类型，一站式装修效果图是一站式装修案例
		$genre = M('offer')->where(array('to_uid'=>$res['c_id'],'form_id'=>$res['u_id'],'pid'=>$res['f_id']))->getField('type');
		if($genre == 1){
			$value['xiaoguo'] = M('fitmentimg')->where(array('pid'=>$res['x_id']))->getField('img',true);
		}else{
			$value['xiaoguo'] = M('drawingimg')->where(array('pid'=>$res['x_id']))->getField('img',true);
		}
		for($i=0;$i<count($value['xiaoguo']);$i++){
			$value['xiaoguo'][$i] = $url.$value['xiaoguo'][$i];
		}
		$yingfu = M('duei')->where(array('order_id'=>$data['id'],'type'=>3))->field('percnet,num')->select();

		$count = M('duei')->where(array('order_id'=>$data['id'],'type'=>3))->count();
			//附属订单信息
		$affiliated = M('affiliated')->where(array('order_sn'=>$res['order_sn']))->select();
		//查询附属订单所需金额
		$yufu = M('affiliated')->where(array('order_sn'=>$res['order_sn'],'sort'=>1))->getField('money');
		if($yufu){
			$value['affiliated'] = $yufu*0.5;
		}else{
			$value['affiliated'] = "";
		}
		$value['order_amout'] = $res['amout'];
	
		//每次付款金额
		
		//如果存在附属订单，将施工订单和附属订单放入money
		if($affiliated){
			
			foreach($affiliated as $k=>$v){
				$mon[$k]['money'] = strval(round($v['money']*0.5,2));
				$mon[$k]['name'] = $v['num'];
				$mon[$k]['pay'] = $v['pay'];
				$sum += $mon[$k]['money'];
				$mon[$k]['sort'] = $v['sort'];
			}
			foreach($yingfu as $key=>$val){
				$mo[$key]['percnet'] = round($res['amout']*$val['percnet']/100,2);
				$mo[$key]['name'] = 'a';
				$mo[$key]['pay'] = $val['num'];
				if($mo[$key]['pay'] == 100){
					$mo[$key]['percnet'] = round($res['amout']*$val['percnet']/100,2)+$sum.'('.'包含变更尾款:'.$sum.')';
				}
				$arr = array_merge($mo,$mon);
				array_multisort(i_array_column($arr,'pay'),SORT_ASC,i_array_column($arr,'name'),SORT_ASC,$arr);
				$value['money'] = $arr;
			}
			//查询本次需要付款金额，
			if($res['payint']<$count-1){
				$value['this_pay'] = M('duei')->where(array('order_id'=>$data['id'],'type'=>3,'num'=>$yingfu['num']+1))
									->getField('percnet');
				$value['this_pay'] = strval(round($value['this_pay']*$res['amout']/100,2));
			}elseif($res['payint'] == $count-1){
				$value['this_pay'] = M('duei')->where(array('order_id'=>$data['id'],'type'=>3,'num'=>100))
									->getField('percnet');
				$value['this_pay'] = strval(round($value['this_pay']*$res['amout']/100,2)+$sum);

			}else{
				$value['this_pay'] = $res['amout']+$sum;

			}
			
		}else{
			//如果不存在附属订单，将施工订单放入money
		
			foreach($yingfu as $key=>$val){
				$arr[$key]['percnet'] = strval(round($res['amout']*$val['percnet']/100,2));
				$arr[$key]['name'] = 'a';
				$arr[$key]['pay'] = $val['num'];
				if($arr[$key]['pay'] == 100){
					$arr[$key]['percnet'] = round($res['amout']*$val['percnet']/100,2);
				}
			}
			$value['money'] = $arr;
		

		if($res['payint']<$count-1){
				$value['this_pay'] = M('duei')->where(array('order_id'=>$data['id'],'type'=>3,'num'=>$res['payint']+1))
									->getField('percnet');
				$value['this_pay'] = strval(round($value['this_pay']*$res['amout']/100,2));
				}elseif($res['payint'] == $count-1){
					$value['this_pay'] = M('duei')->where(array('order_id'=>$data['id'],'type'=>3,'num'=>100))
									->getField('percnet');
					$value['this_pay'] = strval(round($value['this_pay']*$res['amout']/100,2));
				}else{
					$value['this_pay'] = $res['amout'];
				}
		
		}
		// $value['due1'] = round($res['amout']*$yingfu['one']/100,2);
		// $value['due2'] = round($res['amout']*$yingfu['two']/100,2);
		// $value['due3'] = round($res['amout']*$yingfu['three']/100,2);
		// $value['due4'] = round($res['amout']*$yingfu['four']/100,2);

		// $value=['dingdan'][$k]['due'] = $v['amout']*$bai/100; // 应付金额 = 总金额 * 百分比
		// $bai1 = M('describe')->where(array('sort'=>1,'type'=>3))->getField('money');
		// $value['due1'] = $res['amout']*$bai1/100; // 应付金额 = 总金额 * 百分比
		// $bai2 = M('describe')->where(array('sort'=>2,'type'=>3))->getField('money');
		// $value['due2'] = $res['amout']*$bai2/100; // 应付金额 = 总金额 * 百分比
		// $bai3 = M('describe')->where(array('sort'=>3,'type'=>3))->getField('money');
		// $value['due3'] = $res['amout']*$bai3/100; // 应付金额 = 总金额 * 百分比
		// $bai4 = M('describe')->where(array('sort'=>4,'type'=>3))->getField('money');
		// $value['due4'] = $res['amout']*$bai4/100; // 应付金额 = 总金额 * 百分比
		// 房屋信息
		$value['address'] = $f['district'].$f['address'];// 地址
		$value['area'] = $f['area'];	// 面积
		$value['housetype'] = $f['housetype'];// 户型
		$value['addtime'] = date("Y-m-d H:i",$res['addtime']);
		$value['f_id'] = $res['f_id'];
		$value['order_type'] = $res['order_type'];
		$value['payint'] = $res['payint'];
		//如果是尾款，将payint设置为100
		if($res['payint'] == (count($value['money'])-$fushu)){
			$value['payint'] = 100;
		}
		
		if($res['style']){
			$value['style'] = $res['style'];
		}else{
			$value['style'] = '';
		}
		// $valeu['state'] = $res['state'];
		if($user){
			$value['username'] = $user['username'];// 设计名
			$value['mobile'] = $user['mobile'];// 设工队长电话			
		}else{
			$value['username'] = '';
			$value['mobile'] = '';
 		}
 		$value['state'] = 1;
 		if($res['order_type'] > 1){
 			// 查询监管信息
			// 有监管
			if($value['type'] == 1){// 业主
				$j_id = M('flow')->where(array('uid'=>$data['uid'],'f_id'=>$res['f_id']))->getField('id');
			}else{
				$j_id = M('flow')->where(array('c_id'=>$data['uid'],'f_id'=>$res['f_id']))->getField('id');
			}
			$value['j_id'] = $res['f_id'];
			$value['k_id'] = M('smallflow')->where(array('pid'=>$j_id,'flowname'=>'开工进场'))->getField('id');
			$value['w_id'] = M('smallflow')->where(array('pid'=>$j_id,'flowname'=>'竣工'))->getField('id');
 		}

		if($v['order_type'] == 7){
			// 查询所有的流程是否完成
			$complete = M('smallflow')->where(array('pid'=>$j_id,'flowname'=>array('neq','竣工')))->getField('state',true);
			if(!in_array(2,$complete)){
				if(!in_array(1,$complete)){
					$value['is_complete'] = 1;
				}else{
					$value['is_complete'] = 2;
				}
			}else{
				$value['is_complete'] = 2;
			}
		}
 		// 合同编号
 		$value['pact_sn'] = M('pact')->where(array('did'=>$data['id']))->getField('pact_sn');
		$value['order_sn'] = strval($res['order_sn']);// 订单编号
		$value['paynum'] = $res['paynum']; // 订单状态
		$value['uid'] = $data['uid'];
		echo json_encode($value);
	}
	/**
	 * 附属订单
	 * order_sn    订单号
	 */
	public function indent_affiliated(){
		$data = I('post.');
		$affiliated = M('affiliated')->where(array('order_sn'=>$data['order_sn'],'sort'=>1))->order('num desc')->select();
		$did = M('constructorder')->where(array('order_sn'=>$data['order_sn']))->getField('id');
		foreach($affiliated as $k=>$v){
			if($v['sort']==1){
				$value['id'] = $did;
				$value['money'] = $v['money'];
				$value['order_sn'] = $v['order_sn'];
				$value['describe'] = $v['describe'];
				$value['sort'] = $v['sort'];
				$value['pay'] = $v['pay'];
				$value['name'] = $v['name'];
				$value['type'] = $v['type'];
				$value['amount'] = round($v['money']*0.5,2);
				$value['state'] = 1;
			}
		}
		if(!$affiliated){
			$value['state'] = 0;
			$value['error'] = "没有找到该附属订单";
		}
		echo json_encode($value);
	}
	/**设计订单
	*	uid 用户id
	*	type 0 未支付 1 进行中 2 已完成 3 未评论	
	* 	status 0 未付款 1 已付1次 未发送图纸 2 付1 已发送图纸  3 付2 未发送图纸  4 付2 已发送图纸 5 已评价
	*/
	public function designlist($page = 1,$r = 10){
		if(M('user')->where(array('id'=>$_GET['uid']))->getField('type') == 1){// 用户
			$map['u_id'] = $_GET['uid'];
			$value['type'] = 1;		// 返回前台
		}else{
			$value['type'] = 2;// 设计师
			$map['s_id'] = $_GET['uid'];
		}
		$page = I('get.page')?I('get.page'):1;
		// 默认为全部订单
		$type = $_GET['type'];
		if($type){
			if($type == 1){// 进行中
				$map['status'] = array('in','1,2,3');
			}elseif($type == 2){// 已完成
				if($value['type'] == 2){
					$map['status'] = array('in','4,5');
				}else{
					$map['status'] = 5;
				}
			}elseif($type == 3){// 未评论，要完成并且未评论
				$map['status'] = 4;
			}else{ //未支付
				$map['status'] = 0;
			}			
		}

		$value['dingdan'] = M('designorder')->where($map)
							->field('id,addtime,order_sn,status,s_id,amout')
							->order('addtime desc')
							->page($page,$r)->select();
		$value['num'] = M('designorder')->where($map)->count();
		if($value['dingdan']){
			foreach($value['dingdan'] as $k=>$v){
				$value['dingdan'][$k]['addtime'] = date("Y-m-d H:i",$v['addtime']); // 订单添加时间
				$value['dingdan'][$k]['pact_sn'] = M('pact')->where(array('did'=>$v['id']))->getField('pact_sn');// 合同编号
				// 查询应付款
				if($v['status'] < 3){
					// 查询下次应付金额百分比
					$sort = $v['status'];
					if($v['status'] == 0){
						$sort = $v['status']+1;
					}elseif($v['status'] == 1){
						$sort = $v['status']+1;
					}else{
						$sort = $v['status'];
					}
					
					switch ($sort) {
						case 1:
							$bai = M('due')->where(array('order_id'=>$v['id'],'type'=>2))->getField('one');
						break;
						default:
							$bai = M('due')->where(array('order_id'=>$v['id'],'type'=>2))->getField('two');
						break;
					}
					// dump($bai);
					$value['dingdan'][$k]['due'] = $v['amout']*$bai/100; // 应付金额 = 总金额 * 百分比
				}
			}
		}else{
			$value['dingdan'] = array();
		}
		$value['state'] = 1;
		// if($value['type'] && $value['dingdan']){
		// 	$value['state'] = 1;
		// }else{
		// 	$value['state'] = 1;
		// 	$value['error'] = '你还没有设计订单~';
		// }
		echo json_encode($value);	
	}

	/**
	* 设计订单详情
	* uid Internet  用户id
	* id int 订单id
	*/
	public function designorder(){
		$data = I('get.');
		$url = C('PASE');
		$type = M('user')->where(array('id'=>$data['uid']))->getField('type');
		if($type == 1){// 用户
			$map['u_id'] = $data['uid'];
			$value['type'] = $type;		// 返回前台
		}elseif($type == 2){
			$value['type'] = $type;// 设计师
			$map['s_id'] = $data['uid'];
		}else{// 用户为施工队长，没有设计订单
			$value['type'] = $type;
			$value['state'] = 0;
			$value['uid'] = $data['uid'];
			echo json_encode($value);
			exit;
		}
		$map['id'] = $data['id'];
		$res = M('designorder')->where($map)->field('s_id,f_id,id,addtime,order_sn,status,u_id,amout')->find();
		$user = M('user')->where(array('id'=>$res['s_id']))->field('username,mobile')->find(); // 设计师信息
		$f = M('budget')->where(array('id'=>$res['f_id']))->field('address,district,housetype,area,style')->find();
		// 获取合同编号
		$value['pact_sn'] = M('pact')->where(array('did'=>$data['id']))->getField('pact_sn'); // 合同编号
		$value['order_sn'] = $res['order_sn'];
		// 查询应付款
		if($res['status'] < 3){
			// 查询下次应付金额百分比
			if($res['status'] == 0){
				$sort = $res['status']+1;
			}elseif($res['status'] == 1){
				$sort = $res['status']+1;
			}else{
				$sort = $res['status'];
			}
			switch ($sort) {
				case 1:
					$bai = M('due')->where(array('order_id'=>$map['id'],'type'=>2))->getField('one');
				break;
				default:
					$bai = M('due')->where(array('order_id'=>$map['id'],'type'=>2))->getField('two');
				break;
			}

			$value['due'] = $res['amout']*$bai/100; // 应付金额 = 总金额 * 百分比
		}
		if($res['status'] > 1){
			// 已发送图纸，查询图纸
			$imgId = M('offer')->where(array('pid'=>$res['f_id'],'form_id'=>$res['u_id'],'to_uid'=>$res['s_id']))->getField('drawingId');
			// dump($imgId);
			$img = M('drawingimg')->where(array('pid'=>$imgId))->getField('img',true);
			for($i=0;$i<count($img);$i++){
				$path[$i] = $url.$img[$i];
			}
			$value['img'] = $path;
			// dumps($value['img']);
		}
		$value['order_amout'] = $res['amout'];
		$value['addtime'] = date("Y-m-d H:i",$res['addtime']);
		$value['status'] = $res['status'];
		$value['name'] = $user['username'];
		$value['mobile'] = $user['mobile'];
		$value['area'] = $f['area'];
		$value['address'] = $f['district'].$f['address'];
		$value['housetype'] = $f['housetype'];
		$value['uid'] = $data['uid'];
		$value['style'] = $f['style'];
		$value['state'] = 1;
		echo json_encode($value);
	}

	public function evaluate_goods(){
		$data = json_decode($_POST['data'],true);
		if(M('goodscomment')->where(array('did'=>$data['order_id']))->select()){ // 订单已评价
			$value['state'] = 0;
			$value['error'] = '此订单已评价~';
			echo json_encode($value);
			exit;
		}
		foreach($data['goods'] as $k=>$v){
			$map['goods'] = $v['goods']; // 商品星级
			$map['goods_id'] = $list[] =  $v['goods_id']; // 商品id
			$map['uid'] = $data['uid'];  // 评价人id
			$map['anonymity'] = $data['anonymity']?$data['anonymity']:0;  // 是否匿名
			$map['content'] = $v['content'];
			$map['contentimg'] = $v['contentimg'];
			$map['addtime'] = time();
			$map['did'] = $data['order_id'];
			$int = M('goodscomment')->data($map)->add();
		}
		// $value['sql'] = M('goodscomment')->_SQL();
		if($int > 0){
			$goodsId = M('goodorder')->where(array('order_id'=>$data['order_id']))->getField('goods_id',true);
			$arr = array_diff($goodsId,$list);
			if($arr){
				for($i=0;$i<=count($arr);$i++){
					$res['goods_id'] = $arr[$i];
					$res['anonymity'] = $data['anonymity']?$data['anonymity']:0;
					$res['goods'] = 5;
					$res['uid'] = $data['uid'];
					$res['did'] = $data['order_id'];
					$res['content'] = '该用户给予5星好评';
					$res['addtime'] = time();
					M('goodscomment')->data($res)->add();
				}
			}
			M('goodsorder')->where(array('id'=>$map['did'],'user_id'=>$map['uid']))->save(array('order_status'=>4));
			$value['state'] = 1;
			$value['order_id'] = $data['order_id'];
		}else{
			$value['state'] = 0;
			$value['error'] = '评价失败1';
		}
		echo json_encode($value);
	}

	public function ios_evaluate(){
		$score['score'] = I('get.score');
		$lt['grade'] = array('egt',$score['score']);
		$lt['min'] = array('elt',$score['score']);
		$level = M('level')->where($lt)->getField('level');
		dump($level);
	}
	/**
	*	评价设计师
	*	uid 用户id
	*   s_id 设计师（施工队长）id
	*   serve 服务星级
	*   logistics 物流星级
	* 	goods 商品星级
	*   satisfied  综合评分
	*   content  评价内容
	*   contentimg  评价上传图片
	* 	pid 父级id
	* 	did 订单id
	* 	type 4 商品 3 施工队长 2 设计师
	*	anonymity 商品评价是否匿名 1 匿名  
	*/
	public function evaluate(){
		$data = I('post.');
		$map['content'] = $data['content']; // 评论内容
		$map['uid'] = $data['uid']; // 评论人id
		$map['satisfied'] = $data['satisfied']; //综合星级(满意度)
		$map['did'] = $data['did']; // 订单的id
		$map['addtime'] = time();
		$map['pid'] = 0;
		$bb = $_FILES['contentimg'];
		if($bb){
		    $info = get_imgs($bb);
			$map['contentimg'] = implode(',',$info);
		}
		if($data['type'] == 4){// 商品评价
			// $goods = $data['goods'];

			$map['serve'] = $data['serve']; // 服务星级
			$map['logistics'] = $data['logistics'];// 物流星级
			$map['goods'] = $data['goods'];// 商品星级
			// $map['goods_id'] = $data['goods_id'];// 商品id
			$map['anonymity'] = $data['anonymity']?$data['anonymity']:0; // 是否匿名

 			if(!$map['content'] || !$map['uid'] || !$map['did'] || !$map['goods'] || !$map['satisfied'] || !$map['serve'] || !$map['logistics']){
				return;
			}
			if(M('goodscomment')->where(array('did'=>$map['did']))->select()){ // 订单已评价
				$value['state'] = 0;
				$value['error'] = '此订单已评价~';
				echo json_encode($value);
				exit;
			}
			$int = M('goodscomment')->data($map)->add();
		}else{// 设计师（施工队长评价）
			if($data['type'] == 2){
				$map['s_id'] = M('designorder')->where(array('id'=>$data['did']))->getField('s_id');
				$int = M('designorder')->where(array('id'=>$data['did']))->getField('status');
				// 查询用户是否已评价，若评价就不能重复评价
				if($int == 5){// 已评价
					$value['state'] = 0;
					$value['error'] = '您已评价哦~';
					echo json_encode($value);
					exit;
				}
				$map['type'] = 2;
			}else{
				$map['s_id'] = M('constructorder')->where(array('id'=>$data['did']))->getField('c_id');
				$int = M('constructorder')->where(array('id'=>$data['did']))->getField('paynum');
				// 查询用户是否已评价，若评价就不能重复评价
				if($int == 11){// 已评价
					$value['state'] = 0;
					$value['error'] = '您已评价哦~';
					echo json_encode($value);
					exit;
				}
				$map['type'] = 3;
			}
			$int = M('evaluate')->data($map)->add();
		}
		if($int>0){
			if($data['type'] == 4){
				// 商品评价
				$value['state'] = 1;
				$value['uid'] = $map['uid'];
				// 修改订单状态
				M('goodsorder')->where(array('id'=>$map['did'],'user_id'=>$map['uid']))->save(array('order_status'=>4));
			}else{
				if($data['type'] == 2){ // 设计订单
					// 修改订单状态
					M('designorder')->where(array('id'=>$data['did']))->save(array('status'=>5));
					$message = M('webmessage')->where(array('name'=>'业主评价设计信息'))->field('left,right')->find();
					$list['subclass'] = 17;
				}else{// 施工订单
					// 修改订单状态
					M('constructorder')->where(array('id'=>$data['did']))->save(array('order_type'=>7));
					$message = M('webmessage')->where(array('name'=>'业主评价施工信息'))->field('left,right')->find();
					$list['subclass'] = 34;
				}
				$value['state'] = 1;
				$value['uid'] = $map['uid'];
				$name = M('user')->where(array('id'=>$data['uid']))->getField('username');
				$list['title'] = $message['left'].$name.$message['right'];
				$list['to_uid'] = $map['s_id'];
				// 这里向用户发送一条消息
				$list['form_id'] = 0;	// 发送人为系统
				$list['type'] = 0;  // 信息类型
				$list['create_time'] = time();
				$list['content_id'] = 0;

				$id = M('message')->data($list)->add();

				$push = new JPushZDY();
		        $m_type = $list['subclass'];//推送附加字段的类型
		        $tui = $list['content_id'];
		       //推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
		        $m_time = '86400';//离线保留时间
		        $to[] = $map['s_id'];
			    $receive['alias'] = $to;
		        $content = $list['title'];//推送的内容
		        $message="";//存储推送状态
		        $result = $push->push($receive,$content,$m_type,$tui,$id,$m_time);
			}
		}else{
			$value['state'] = 0;
			$value['error'] = '评价失败~';
		}
		echo json_encode($value);
	}

	/**
	*	回复评论
	*	uid 回复人的id
	*	pid 评论 的id
	*	type 	4 商品 2 设计师 3 施工队长
	*	order_id	订单ID   
	*/ 
	public function reply(){
		$map['pid'] = $_POST['pid'];
		$map['uid'] = $_POST['uid'];
		$map['did'] = $_POST['order_id'];
		$map['content'] = $_POST['content'];
		if(!$map['pid'] || !$map['uid'] || !$map['content']){
			return;
		}
		$int = M('evaluate')->data($map)->add();
		if($int>0){
			// 向业主发送推送信息
			$value['state'] = 1;
			$value['uid'] = $map['uid'];
		}else{
			$value['state'] = 0;
			$value['error'] = '回复失败';
		}
		echo json_encode($value);
	}
	/**
	*	施工投诉
	*	uid 用户id
	*	content 投诉内容
	*	did  投诉对应的订单
	*/
	public function complain(){
		$map['content'] = $_POST['content'];
		$map['uid'] = $_POST['uid'];
		$map['did'] = $_POST['did'];
		if(!$map['content'] || !$map['uid'] || !$map['did']){
			return;
		}
		// 查询施工订单状态
		$order_type = M('constructorder')->where(array('id'=>$map['did']))->getField('order_type');
		if($order_type < 6){
			$value['state'] = 0;
			$value['error'] = '此订单还未完成，不能投诉！';
			echo json_encode($value);
			exit;
		}
		$int = M('complain')->data($map)->add();
		if($int>0){
			// 向平台发送一条信息
			$list['subclass'] = 2;
			$username = M('user')->where(array('id'=>$map['uid']))->getField('username');
			$order_sn = M('constructorder')->where(array('id'=>$map['did']))->getField('order_sn');
			$list['title'] = '业主'.$username.'对施工单'.$order_sn.'投诉';
			$list['form_id'] = $map['uid'];	// 发送人为系统
			$list['to_uid'] = 0;  // 接受人为业主
			$list['type'] = 0;  // 信息类型
			$list['create_time'] = time();
			$list['content_id'] = $map['did'];
			M('message')->data($list)->add();
			$value['state'] = 1;
			$value['uid'] = $map['uid'];
		}else{
			$value['state'] = 0;
			$value['error'] = '投诉失败~';
		}
		echo json_encode($value);
	}
	/**
	*	设计（施工）支付成功
	*	id 订单id
	*	order_sn 订单编号
	*	uid 	业主id
	* 	type 2 设计订单 3施工订单 
	*	status 	订单状态
	*	due 支付金额
	*	
	*/ 
	public function win(){
		$data = I('post.');
		if($data['type'] == 2){// 设计订单
			// 查询设计师信息
			$sid = M('designorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->field('s_id,u_id')->find();
			// dump($sid);
			// 0 未付款 1 进行中 2付完全款  3 已完成 4 已评价
			$name = M('user')->where(array('id'=>$sid['u_id']))->getField('username');
			if($data['status'] == 0){
				$message = M('webmessage')->where(array('name'=>'业主支付设计首款信息'))->field('left,right,center')->find();
				$list['subclass'] = 13; // 业主支付设计首款信息
			}elseif($data['status'] == 2){
				$message = M('webmessage')->where(array('name'=>'业主支付设计尾款信息'))->field('left,right,center')->find();
				$list['subclass'] = 15;// 业主支付设计尾款信息
				
			}
			// 这里向业主发送一条消息
			$list['title'] = $message['left'].$name.$message['center'].$data['due'].$message['right'];
			$list['to_uid'] = $sid['s_id'];  // 接受人为设计师
		}elseif($data['type'] == 4){
			//如果是附属订单，将附属订单状态改为已支付
			$sid = M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->field('c_id,f_id,s_id,u_id,order_sn')->find();
		}else{
			// 施工队长信息
			$sid = M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->field('c_id,f_id,s_id,u_id,order_sn')->find();
			$name = M('user')->where(array('id'=>$sid['u_id']))->getField('username');
			//！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
			//当status=1时，未付首款，用户付首款
			if($data['status'] == 1){
				// 业主支付施工首款
				$message = M('webmessage')->where(array('name'=>'业主支付施工首款信息'))->field('left,right,center')->find();
				$list['subclass'] = 22;
				// 首次支付施工款生成监管
				$flow = M('flowname')->order('sort')->select();// 监管模板
				$fangwu['s_id'] = $sid['s_id'];// 设计师id
				$fangwu['c_id'] = $sid['c_id'];// 施工队长id
				$fangwu['f_id'] = $sid['f_id']; // 房屋id
				// ！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
				// 在flow内加入施工订单，关联constructorder用户流程表
				$fangwu['order_sn'] = $sid['order_sn']; //施工订单
				$fangwu['addtime'] = time(); //生成时间
				$fangwu['uid'] = $data['uid']; // 业主id
				$duration = M('offer')->where(array('form_id'=>$data['uid'],'to_uid'=>$sid['c_id'],'pid'=>$sid['f_id']))->field('prepare,duration')->find();
				$fangwu['duration'] = $duration['prepare'] + $duration['duration']; // 施工天数 = 准备期 +  工期
				$pid = M('flow')->data($fangwu)->add(); // 监管主表
				foreach($flow as $k=>$v){
					$res['flowname'] = $v['flowname'];
					$res['sort'] = $v['sort'];
					$res['pid'] = $pid;
					$res['pay'] = $v['pay'];
					M('smallflow')->data($res)->add();
				}
				$service = M('describe')->order('sort')->select();
				foreach($service as $k=>$v){
					$ser['name'] = $v['name'];
					$ser['money'] = $v['money'];
					$ser['describe'] = $v['describe'];
					$ser['sort'] = $v['sort'];
					$ser['type'] = $v['type'];
					$ser['pid'] = $pid;
					$ser['name'] = $v['name'];
					M('service')->data($ser)->add();
				}
				//！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
				//已付首款，更改订单状态为2
				$order_type = M('constructorder')
								->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))
								->save(array('order_type'=>2));
				if(!$order_type){
					$value['state'] = 0;
					$value['error'] = '订单状态更改失败';
				}
				$list['title'] = $message['left'].$name.$message['center'].$data['due'].$message['right'];
				//！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！！
				//当status<5,尾款未付
			}elseif($data['status'] < 5){
				$message = M('webmessage')->where(array('name'=>'业主阶段付款信息'))->field('left,right,lefting,center')->find();
				$list['title'] = $message['left'].$name.$message['lefting'].$data['status'].$message['center'].$data['due'].$message['right'];
				$list['subclass'] = 26;

			}else{// 支付尾款(业主验收)
				// 支付尾款施工队长积分增加
				$score = M('userdetails')->where(array('uid'=>$sid['c_id']))->find();
				M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))
								->save(array('payint'=>100));					
				M('userdetails')->where(array('uid'=>$sid['c_id']))->setInc('score',5); 
				// 查询施工队是否晋级，晋级就发送推送
				$lt['grade'] = array('gt',$score['score']++);
				$lt['min'] = array('lt',$score['score']++);
				$level = M('level')->where($lt)->getField('level');
				if(!$level){
					if($score['process'] <= 5){
						// 修改施工队等级
						$xiu = M('userdetails')->where(array('uid'=>$sid['c_id']))->save(array('process'=>5));
					}
				}else{
					if($level > $score['score']){
						$xiu = M('userdetails')->where(array('uid'=>$sid['c_id']))->save(array('process'=>$levev));
					}
				}
				if($xiu > 0 ){
					// 向施工队推送信息
					$mes = M('webmessage')->where(array('name'=>'施工队长晋级通知'))->field('left')->find();
					$list['subclass'] = 39;
					$list['title'] = $mes['left'];
					$list['form_id'] = 0;	// 发送人为系统
					$list['to_uid'] = $sid['c_id'];  // 接受人为设计师(施工队长)
					$list['type'] = 0;  // 信息类型
					$list['create_time'] = time();
					$list['content_id'] = 0;    // 详情对应
					$tiao = M('message')->data($list)->add(); // 给业主发送信息
					// 这里向业主发送一条消息
					$push = new JPushZDY();
			        $m_type = $list['subclass'];//推送附加字段的类型
			        $tui = $list['content_id'];
			       //推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
			        $m_time = '86400';//离线保留时间
			        $to[] = $list['to_uid'];
				    $receive['alias'] = $to;
			        $content = $list['title'];//推送的内容
			        $message="";//存储推送状态
			        $result = $push->push($receive,$content,$m_type,$tui,$tiao,$m_time);
			        unset($to);
				}
				$order_type = M('constructorder')
								->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))
								->save(array('order_type'=>6));
				$message = M('webmessage')->where(array('name'=>'业主完成验收信息'))->field('left,right,lefting,center')->find();
				$list['title'] = $message['left'].$name.$message['lefting'].$data['status'].$message['center'].$data['due'].$message['right'];
				$list['subclass'] = 33;
			}
			$list['to_uid'] = $sid['c_id'];  // 接受人为施工队长
		}
		// 业主每付一笔款，都要生成工资审核单
		// 查询设计师的信息
		if($data['type'] == 2){
			$where['uid'] = $sid['s_id'];
		}else{
			$where['uid'] = $sid['c_id'];
		}
		$user = M('userdetails')->where($where)->field('alipay,name,uid')->find();
		// echo M('userdetails')->_SQL();
		// dump($user);
		$wage['order_sn'] = $data['order_sn'];
		$wage['type'] = $data['type'];
		$wage['did'] = $data['id'];
		// 生成订单号
		list($usec, $sec) = explode(" ", microtime());
        $usec = substr(str_replace('0.', '', $usec), 0 ,4);
        $str  = rand(10,99);
        $wage['wage_sn'] =  date("YmdHis").$usec.$str;// 工费单号
        $wage['paytime'] = time(); // 订单支时间
        $wage['alipay'] = $user['alipay']; // 工资审核支付宝号码
        $wage['name'] = $user['name'];	// 施工队姓名
        $wage['uid'] = $user['uid']; // 施工队id
        $wage['due'] = $data['due']; // 工资金额为用户支付金额
       	// 用户交易总额添加
       	M('user')->where(array('id'=>$sid['u_id']))->setInc('gmv',$data['due']); 
        // 插入工资审核表
        $int  = M('wageaudit')->data($wage)->add();
        // echo M('wageaudit')->_SQL();
        // dump($int);
        // 发送信息
		$list['form_id'] = 0;	// 发送人为系统
		$list['type'] = 0;  // 信息类型
		$list['create_time'] = time();
		$list['content_id'] = $data['id'];    // 详情对应
		$num = M('message')->data($list)->add();
		$push = new JPushZDY();
        $m_type = $list['subclass'];//推送附加字段的类型
        $tui = $list['content_id'];
       //推送附加字段的类型对应的内容(可不填) 可能是url,可能是一段文字。
        $m_time = '86400';//离线保留时间
        $to[] = $list['to_uid'];
	    $receive['alias'] = $to;
        $content = $list['title'];//推送的内容
        $message="";//存储推送状态
        $result = $push->push($receive,$content,$m_type,$tui,$m_time);
        if($data['type'] == 4){
        	$pid = M('affiliated')->where(array('order_sn'=>$data['order_sn'],'sort'=>1))->order('num')->getField('id');
        	$affiliated = M('affiliated')->where(array('id'=>$pid))->save(array('sort'=>2));
        	$find = M('affiliated')->where(array('order_sn'=>$data['order_sn']))->order('num desc')->find();
        	$order_sn = M('flow')->where(array('order_sn'=>$data['order_sn']))->getField('id');
        	$state = M('smallflow')->where(array('pid'=>$order_sn))->getField('state',true);

        	//如果监管列表有未开工列，将附属订单放入
        	if(in_array('1',$state)){
        		$sort = M('smallflow')->where(array('pid'=>$order_sn,'state'=>1))->order('sort asc')->getField('sort');
        		$jia['pid'] = array('eq',$order_sn);
        		$jia['sort']=array('gt',$sort-1);
        		M('smallflow')->where($jia)->setInc('sort');
        		$smallflow['pid'] = $order_sn;
	        	$smallflow['flowname'] = $find['name'];
	        	$smallflow['state'] = $find['finish'];
	        	$smallflow['sort'] = $sort;
	        	$smallflow['pay'] = $find['pay'];
	        	$smallflow['finish'] = $find['finish'];
	        	$smallflow['type'] = 2;
	        	M('smallflow')->add($smallflow);
        	}elseif(in_array('2',$state)){
        		$sort = M('smallflow')->where(array('pid'=>$order_sn,'state'=>2))->order('sort asc')->getField('sort');
        		$jia['pid'] = array('eq',$order_sn);
        		$jia['sort']=array('gt',$sort-1);
        		M('smallflow')->where($jia)->setInc('sort');
        		$smallflow['pid'] = $order_sn;
	        	$smallflow['flowname'] = $find['name'];
	        	$smallflow['state'] = $find['finish'];
	        	$smallflow['sort'] = $sort+1;
	        	$smallflow['pay'] = $find['pay'];
	        	$smallflow['finish'] = $find['finish'];
	        	$smallflow['type'] = 2;
	        	M('smallflow')->add($smallflow);
        	}
        	//附属订单付款首款后，将附属订单信息存入smallflow并进行排序
        	
        	
			if($affiliated !== false){
				$value['state'] = 1;
				$value['uid'] = $data['uid'];
				$value['status'] = $data['status'];
			}else{
				$value['state'] = 0;
				$value['error'] = '订单状态更改失败';
				}
        	}else{
	        	if($num > 0 && $int > 0){
				if($data['type'] == 2){// 修改订单状态
					$i = M('designorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->setInc('status');
				}else{
					// 修改订单的支付次数
					$payint = M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->getField('payint');
					if($payint != 100){
						M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->setInc('payint');
						
					}
					$i = M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->getField('order_type');
					
				}
				if($i>0){
					// 查询施工订单对应的下一阶段的流程是否完成,如完成修改订单的状态
					// 查询监管id
					$id = M('flow')->where(array('uid'=>$data['uid'],'f_id'=>$sid['f_id']))->getField('id');
					// 对应第几次付款
					$a = M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->getField('payint');
					// dump($a.'a1');
					$a++;
					// dump($a.'a2');
					// dump($a);
					$res = M('smallflow')->where(array('pid'=>$id,'pay'=>$a))->getField('state',true);
					// echo M('smallflow')->_SQL();
					// dump($res);
					// exit;
					// if(!in_array(2,$res)){
					// 	if(!in_array(1,$res) && $data['status'] != 6){
					// 		// 修改订单状态
					// 		M('constructorder')->where(array('id'=>$data['id'],'order_sn'=>$data['order_sn']))->setInc('order_type');				
					// 		// echo 'aaa';
					// 		// exit;
					// 	}
					// }
					$value['state'] = 1;
					$value['uid'] = $data['uid'];
					$value['status'] = $data['status'];			
				}else{
					$value['state'] = 0;
					$value['error'] = '又是哪里错了~';
				}
			}else{
				// dump($num);
				// dump($int);
				$value['state'] = 0;
				$value['error'] = '发送信息和生成工资审核失败~';
			}
        }
		
		// dump($value);
		echo json_encode($value);
	}
	/**
	*	商品订单列表
	*	uid 用户id
	*   type 0 待支付 1 已付 待发货 2 已发货 3 已收货 4 已评价
	*/
	public function shoporder($page = 1,$r = 15){
		$data = I('get.');
		$map['user_id'] = $data['uid'];
		$url = C('PASE');
		//是否有筛选条件 
		if($data['type']){
			if($data['type'] == '-1'){
				$data['type'] =0;
				// sadfasdf
			}
			$map['order_status'] = $data['type'];
		}
		$map['del'] = 1;   // 未删除的
		$page = $data['page']?$data['page']:1;
		$order = M('goodsorder')->where($map)->field('shipping_fee,order_amount,order_status,id')->page($page,$r)->select();
		$value['num'] = M('goodsorder')->where($map)->count();
		if($order){
			foreach($order as $k=>$v){
				// 查询商品信息
				$good = M('goodorder')
						->where(array('order_id'=>$v['id']))
						->field('goods_id,num,color,standard,capacity')->select();
				foreach($good as $j=>$z){
					$goods[$j]['num'] = $z['num']; // 商品数量
					$list['gid'] = $goods[$j]['id'] = $z['goods_id']; // 商品id
					if($z['color'] >0){
						$goods[$j]['color'] = M('configvalue')->where(array('id'=>$z['color']))->getField('value');
						$list['color'] = $z['color'];
					}
					if($z['standard'] >0){
						$list['standard'] = $z['standard'];
						$goods[$j]['standard'] = M('configvalue')->where(array('id'=>$z['standard']))->getField('value');
					}
					if($z['capacity'] >0){
						$list['capacity'] = $z['capacity'];
						$goods[$j]['capacity'] = M('configvalue')->where(array('id'=>$z['capacity']))->getField('value');
					}
					$res = M('goodsconfig')->where($list)->field('price,productimg')->find();
					$goods[$j]['name'] = M('product')->where(array('id'=>$list['gid']))->getField('name');
					$goods[$j]['price'] = $res['price'];	// 商品价格
					$goods[$j]['productimg'] = $url.$res['productimg']; // 商品图片
					// 查询订单是否在售后
					$after = M('after')->where(array('order_id'=>$v['id'],'goods_id'=>$z['goods_id']))->find();
					if($after){
						$goods[$j]['after_status'] = $after['state'];
						$goods[$j]['after_content'] = $after['content'];
					}
				}
				$goodsorder[$k]['goods'] = $goods;
				unset($goods);
				$goodsorder[$k]['id'] = $v['id'];  // 订单id
				$goodsorder[$k]['shipping_fee'] = $v['shipping_fee']; // 运费总额
				$goodsorder[$k]['order_amount'] = $v['order_amount'];	// 订单总额
				$goodsorder[$k]['order_status'] = $v['order_status'];      // 订单状态
			}
			$value['goodsorder'] = $goodsorder;
		}else{
			$value['goodsorder'] = array();
		}
		$value['state'] = 1;
		// dump($value);
		echo json_encode($value);
	}

	/** 	
	*	商品订单详情
	*	id 订单id
	*/ 
	public function goodsdetails(){
		$id = I('get.id');
		$uid = I('get.uid');
		$url = C('PASE');
		$order = M('goodsorder')->where(array('id'=>$id,'user_id'=>$uid))->find();
		// 获取收货地址信息
		$value['linkarea'] = M('linkarea')->where(array('uid'=>$order['user_id'],'id'=>$order['consignee_id']))->find();
		// 获取商品信息
		$good = M('goodorder')
				->where(array('order_id'=>$order['id']))
				->field('goods_id,num,color,standard,capacity,state')->select();
		// 商品信息
		foreach($good as $k=>$v){
			$goods[$k]['num'] = $v['num']; // 商品id
			$goods[$k]['id'] = $v['goods_id']; // 商品数量
			$goods[$k]['state'] = $v['state'];	// 是否发货
			$goods[$k]['name'] = M('product')->where(array('id'=>$v['goods_id']))->getField('name');
			if($v['color'] >0){
				$goods[$k]['color'] = M('configvalue')->where(array('id'=>$v['color']))->getField('value');
				$list['color'] = $v['color'];
			}
			if($v['standard'] >0){
				$list['standard'] = $v['standard'];
				$goods[$k]['standard'] = M('configvalue')->where(array('id'=>$v['standard']))->getField('value');
			}
			if($v['capacity'] >0){
				$list['capacity'] = $v['capacity'];
				$goods[$k]['capacity'] = M('configvalue')->where(array('id'=>$v['capacity']))->getField('value');
			}
			$res = M('goodsconfig')->where($list)->field('price,productimg')->find();
			$goods[$k]['price'] = $res['price'];	// 商品价格
			$goods[$k]['productimg'] = $url.$res['productimg']; // 商品图片
			// 商品是否在售后中
			$after = M('after')->where(array('order_id'=>$id,'goods_id'=>$v['goods_id']))->find();
			if($after){
				$goods[$k]['after_status'] = $after['state'];
				$goods[$k]['after_content'] = $after['content'];
			}else{
				// 查询当前商品是否有售后
				$calssifyId = M('product')->where(array('id'=>$v['goods_id']))->getField('classifyId');
				$jurisdiction = M('jurisdiction')->where(array('calssifyId'=>$calssifyId))->find();
				if($jurisdiction){
					$goods[$k]['is_shouhou'] = 1;
				}else{
					$goods[$k]['is_shouhou'] = 0;
				}
			}
		}
		$value['goods'] = $goods; // 商品信息
		// 配送信息
		// if($order['shipping_time']){ // 发货时间
		// 	$value['shipping_time'] = date('Y-m-d H:i:s',$order['shipping_time']);
		// }else{
		// 	$value['shipping_time'] = '';
		// }
		// 发票信息
		if($order['bill_id'] > 0){
			$bill = M('bill')->where(array('id'=>$order['bill_id']))
								->field('title,name,content')->find();
			$lists[0]['title'] = $bill['title'];
			$lists[0]['name'] = $bill['name'];
			$lists[0]['content'] = $bill['content'];
		}else{
			$lists= array();
		}
		$value['bill']=$lists;
		$value['order_sn'] = $order['order_sn']; //订单编号
		$value['order_status'] = $order['order_status']; //订单状态
		$value['favorable'] = $order['favorable']; //优惠金额
		$value['addtime'] = date('Y-m-d H:i:s',$order['addtime']); // 订单添加时间
		$value['order_amount'] = $order['order_amount']; // 订单金额
		$value['shipping_fee'] = $order['shipping_fee']; // 运费
		$value['goods_amount'] = $order['goods_amount'];	// 商品总额
		$value['state'] = 1;
		// dump($value);
		echo json_encode($value);
	}
	/**
	*	删除(取消)商品订单
	*	uid 用户id
	*	id 	订单id 
	*	type 1 删除 2 取消
	*	order_status 订单状态
	*	url  order/delgoodsorder
	*	请求方式  get 
	*/ 
	public function delgoodsorder(){
		$data = I('get.');
		$order_status = $data['order_status'];
		if($data['type'] == 1){// 删除的订单要已完成，否则不能删除
			if($order_status < 3 && $order_status != 5){
				$value['state'] = 0;
				$value['error'] = '订单没完成，不能删除';
				echo json_encode($value);
				exit;
			}else{
				$int = M('goodsorder')->where(array('user_id'=>$data['uid'],'id'=>$data['id']))->save(array('del'=>0));// 修改状态为0 删除
			}
		}else{
			if($order_status > 0){// 已发货，不能取消
				$value['state'] = 0;
				$value['error'] = '商品已发出，不允许取消订单';
				echo json_encode($value);
				exit;
			}else{
				$int = M('goodsorder')->where(array('user_id'=>$data['uid'],'id'=>$data['id']))->save(array('order_status'=>5));// 修改状态为0 删除
			}
		}
		if($int > 0){
			$value['state'] = 1;
			$value['uid'] = $data['uid'];
		}else{
			$value['state'] = 0;
			$value['error'] = '删除失败';
 		}
 		echo json_encode($value);
	}

	/**
	*	商品订单支付成功改变订单状态
	*	uid 用户id
	*	id 	订单id
	*	
	*/ 
	public function shopwin(){
		$data = I('get.');
		$map['user_id'] = $data['uid'];
		$map['id'] = $data['id'];
		$list['pay_time'] = time(); // 支付时间
		$list['order_status'] = 1;
		$list['pay_id'] = $data['pay_id'];   // ping++ 返回的支付id
		// 用户交易金额添加
		$res = M('goodsorder')->where(array('id'=>$map['id']))->getField('order_amount');
		M('user')->where(array('id'=>$map['user_id']))->setInc('gmv',$res); 
		$int = M('goodsorder')->where($map)->save($list);
		if($int>0){
			$value['state'] = 1;
			$value['uid'] = $data['uid'];
			$value['id'] = $data['id'];
			$value['order_status'] = $list['order_status'];
		}else{
			$value['state'] = 0;
			$value['error'] = '修改商品订单状态失败';
		}
		echo json_encode($value);
	}

	// 提醒发货
	public function message(){
		$data = I('get.');
		$map['form_id'] = $data['uid'];  // 信息发送人
		$map['to_uid'] = 0;		// 接收人 0 为系统
		$map['title'] = '提醒发货';
		$map['content_id'] = $data['id']; 	// 信息详情为订单id
		$map['subclass'] = 1;    // 后台1 提醒发货
		$map['create_time'] = time(); // 信息发送时间
		$int = M('message')->data($map)->add();
		if($int > 0){
			$value['state'] = 1;
			$value['uid'] = $data['uid'];
		}else{
			$value['state'] = 0;
			$value['error'] = '提醒发货失败';
		}
		echo json_encode($value);
	}

	/**
	*	确认收货
	*	url order/harvest
	*	id  订单ID
	*/ 
	public function harvest(){
		$data = I('get.');
		$int = M('goodsorder')->where(array('id'=>$data['id']))->save(array('order_status'=>3));
		if($int > 0){
			$vlaue['id'] = $data['id'];
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '收货失败';
		}
		echo json_encode($value);
	}
	
	// 前端支付成功后退款
	public function refund(){
		// require_once(dirname().'./ping/init.php');
		// // api_key 获取方式：登录 [Dashboard](https://dashboard.pingxx.com)->点击管理平台右上角公司名称->企业设置->开发设置->Live/Test Secret Key
		// // const APP_KEY = '';

		// // // app_id 获取方式：登录 [Dashboard](https://dashboard.pingxx.com)->应用卡片下方
		// // const APP_ID = 'app_bvDmbTnH4inTLqj1';
		// // 设置 API Key
		// \Pingpp\Pingpp::setApiKey('sk_test_WnD8iPGCqvr54ujPOOKOaz18');

		// $order_id = I('get.id');

		// $id = M('goodsorder')->where(array('id'=>$order_id))->getField('pay_id');
		// // // 通过发起一次退款请求创建一个新的 refund 对象，只能对已经发生交易并且没有全额退款的 charge 对象发起退款
		// $ch = \Pingpp\Charge::retrieve("$id");// Charge 对象的 id
		// $re = $ch->refunds->create(
		//     array(
		//         'amount' => 1,// 退款的金额, 单位为对应币种的最小货币单位，例如：人民币为分（如退款金额为 1 元，此处请填 100）。必须小于等于可退款金额，默认为全额退款
		//         'description' => 'Your Descripton'
		//     )
		// );
		$data = I('get.');
		$int = M('goodsorder')->where(array('id'=>$data['id']))->save(array('order_status'=>6));
		if($int > 0){
			$order = M('goodsorder')->where(array('id'=>$data['id']))->find();
			$after['content'] = 2;
			$after['uid'] = $order['user_id'];
			$after['order_id'] = $data['id'];
			$after['addtime'] = time();
			$after['state'] = 1;
			M('after')->data($after)->add();
			$value['state'] = 1;
			$value['order_id'] = $data['id'];
		}else{
			$value['state'] = 0;
			$value['error'] = '退款生成失败';
		}
		echo json_encode($value);
	}
	/**
	*	物流信息
	*	order_id 订单id
	*/ 
	public function logistics(){
		$id = $_GET['order_id'];
		$uid = $_GET['uid'];
		$goods_id = $_GET['goods_id'];
		$ul = C('PASE');
		$order = M('goodorder')->where(array('order_id'=>$id,'goods_id'=>$goods_id))
									// 					物流公司     单号
								->field('state,shipping_id,shipping_number')
								->find();
		$res = M('goods_order')->where(array('id'=>$id))->find();
		$value['status'] = $order['state']; // 物流状态
		$value['shipping_number'] = $order['shipping_number']; // 快递单号
		// 查询商品信息
		// $goods = M('goodorder')->where(array('order_id'=>$id))->getField('goods_id');
		$img = M('product')->where(array('id'=>$goods_id))->getField('img');
		$value['img'] = $ul.$img;
		if($order['state'] < 1){ // 等待商家受理
			 // 等待商家受理
			$data[0]['context'] = '等待商家受理';
			$data[0]['ftime'] = '';
			$value['data'] = $data;
		}else{// 已发货，待取件或有快递信息
			$post_data = array();
			$post_data["customer"] = '5A695D0388A2C4CA43C2F4BD93189E13';
			$key= 'fFVBRXwj5735' ;
			// 查询物流公司信息
			$com = M('logistics')->where(array('id'=>$order['shipping_id']))->getField('coding');
			$num = $order['shipping_number'];
			$post_data["param"] = '{"com":"'.$com.'","num":"'.$num.'"}';
			$url='http://poll.kuaidi100.com/poll/query.do';
			$post_data["sign"] = md5($post_data["param"].$key.$post_data["customer"]);
			$post_data["sign"] = strtoupper($post_data["sign"]);
			$o=""; 
			foreach ($post_data as $k=>$v)
			{
			    $o.= "$k=".urlencode($v)."&";		//默认UTF-8编码格式
			}
			$post_data=substr($o,0,-1);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_HEADER, 0);
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			$result = curl_exec($ch);
			$data = str_replace("\&quot;",'"',$result);
			$datas = json_decode($data,true);
			if(!$datas){
				$data[0]['ftime'] = date('Y-m-d H:i:s',$order['post_time']);
				$data[0]['context'] = '商家已受理，待快递揽件';
			}else{
				$data = $datas['data'];
				$res['ftime'] = date('Y-m-d H:i:s',$order['post_time']);
				$res['context'] = '商家正通知快递公司揽件';
				array_push($res,$data);
				$value['data'] = $data;
			}
		}
		$value['state'] = 1;
		echo json_encode($value);	
	}


}