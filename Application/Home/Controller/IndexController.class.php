<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\WsMessageSend;
class IndexController extends Controller {
	public function index(){
		$url = C('PASE');
		//查询轮播图
		$res = M('carousel')->where(array('state'=>1))->field('id,img,is_package')->select();
		foreach($res as $k => $v){
			$res[$k]['img'] = $url.$res[$k]['img'];
		}
		//查询用户有无消息
		if($_GET['uid']){
			$num = M('message')->where(array('is_read'=>0,'to_uid'=>$_GET['uid']))->count();
			$status = M('user')->where(array('id'=>$_GET['uid']))->field('status,city,usertoken,type')->find();
		}else{
			$num = 0;
		}
		echo json_encode($res);
	}
}