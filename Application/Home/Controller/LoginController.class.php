<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\WsMessageSend;
class LoginController extends Controller {
	/**
	 * 获取验证码
	 * type 	所获取验证码类型
	 * phoneNum	用户手机号
	 */

	public function getcode()
  {
    $data = I('post.');
    if(!$data['phoneNum']){
      $value['state'] = 0;
      $value['error'] = "请传手机号~";
      echo json_encode($value);
      exit;
    }
    if($data['type'] == 1){
        $map = M('user')->where(array('phoneNum'=>$data['phoneNum']))->find();
        if($map){
          $value['state'] = 0;
          $value['error'] = '该手机号已注册！';
          echo json_encode($value);
          exit;
        }
    }
    if($data['type'] == 2){
      $map = M('user')->where(array('phoneNum'=>$data['phoneNum']))->find();
      if(!$map){
        $value['state'] = 0;
        $value['error'] = '该手机号未注册,请更换！';
        echo json_encode($value);
        exit;
      }
    }
    // 清空该手机好对应的验证码
    M('code')->where(array('phoneNum'=>$data['phoneNum']))->delete();
    $code = '111111';
    // $code= rand('100000','999999');
    include "MessageSend.class.php";
    $account="KA00009";           //改为实际账户名
    $password="KA0000944";           //改为实际短信发送密码
    // $mobiles= $data['phoneNum'];
    $mobiles = "15501154285";   //目标手机号码，多个用半角“,”分隔
    $extno = "";
    $content="【金织巢】验证码：".$code."，该验证码用于金织巢APP注册或修改信息时使用，10分钟内有效。";
    $sendtime = "";
    $code = $code;   //验证码md5加密保存
    $result=WsMessageSend::send($account,$password,$mobiles,$extno,$content,$sendtime);
    $xml = simplexml_load_string($result);
    $retustatus = $xml->returnstatus;
    if($retustatus == "Success"){
        $info['phoneNum']=$data['phoneNum'];
        $info['code']=$code;
        $have = M('code')->where(array('phoneNum'=>$info['phoneNum']))->select();
        if($have){
          M('code')->where(array('phoneNum'=>$info['phoneNum']))->delete();
        }
        M('code')->add($info);
        $value['state'] = 1;
        $value['code']=$code;
    }else{
        $value['state'] = 0;
        $value['error'] = "验证码获取失败~";
    }
    echo json_encode($value);
  }
 /**
  *   用户新版（验证码）登录
  *   phoneNum  电话
  *   password  密码
  *   usertoken  用户token值
  */ 
  public function loginto(){
    $url = 'http://192.168.31.210/Jzc/Uploads/';
    $data = I('post.');
    $value = array();
    $user = M('user')->where(array('phoneNum'=>$data['phoneNum']))->find();
    if(!$user){
        $data['username'] = substr_replace($data['phoneNum'],'****',3,4);
	    //验证手机号、验证码
	    $int = M('code')->where(array('phoneNum'=>$data['phoneNum'],'code'=>$data['code']))->find();
	    if($int){
	      // 通过验证，插入
	      $map['phoneNum'] = $data['phoneNum'];
	      $map['userpic'] = '2017-09-14/59b9e820d1e99.jpg'; // 默认头像保密的
	      $map['username'] = $data['username'];
	      $map['registertime'] = time();
	      $map['usertoken'] = md5(time());
	      $uid = M('user')->data($map)->add();
	      if($uid){
	          $users=M("user")->where("id='$uid'")->find();
	          if(!$users['hx_id']){
	            $hx_name=$uid;
	            $result=accreditRegister(array('username'=>$hx_name,'password'=>'123456'));
	            if($result->entities){
	              $data1['hx_id'] =$hx_name;
	              M("user")->where("id='$uid'")->save($data1);
	            }else{
	                $value['state'] = 0;
	                $value['error'] = '环信错误~';
	                echo json_encode($value);exit;
	            }
	          }
	      }
	      $value = M('user')->where(array('phoneNum'=>$data['phoneNum']))->field('id,username,phoneNum,type,userpic,registertime,usertoken')->find();
	      $value['userpic'] = $url.$value['userpic'];
	      $value['state'] = 1;
	    }else{
	      $value['state'] = 0;
	      $value['error'] = '验证码不正确~';
	    }
	    echo json_encode($value);
	    exit;
    }else{
    	if($data['type'] == 1){
    		$int  = M('code')->where(array('phoneNum'=>$data['phoneNum'],'code'=>$data['code']))->find();
    	}else{
    		$int = M('user')->where(array('phoneNum'=>$data['phoneNum'],'password'=>md5(md5($data['password']))))->find();
    	}
	    if($int){
	    	$res = M('user')->where(array('phoneNum'=>$data['phoneNum']))->find();
	        if(empty($res['hx_id'])){
	            $hx_name=$res['id'];
	            $result=accreditRegister(array('username'=>$hx_name,'password'=>'123456'));
	            if($result->entities){
	              $data1['hx_id'] =$hx_name;
	              M("user")->where("id='$hx_name'")->save($data1);
	            }else{
	                $value['state'] = 0;
	                $value['error'] = '环信错误~';
	                echo json_encode($value);exit;
	            }
	        }
	        $value['state'] = 1;
	        $value['uid'] = $res['id'];
	        $value['username'] = $res['username'];
	        $value['userpic'] = $url.$res['userpic'];
	        $value['phoneNum'] = $res['phoneNum'];
	        $value['usertoken'] = md5(time());
	        M('user')->where(array('id'=>$res['id']))->save(array('usertoken'=>$value['usertoken']));
	        $value['type'] = $res['type'];
	        if($res['status'] == 3){
	          $value['state'] = 0;
	          $value['error'] = '您的账号存在风险，暂被禁用，请联系客服010-64796961。';
	          echo json_encode($value);
	          exit;
	        }elseif($res['status'] == 4){
	          $value['state'] = 0;
	          $value['error'] = '您的账号存在风险，暂被禁用，请联系客服010-64796961。';
	          echo json_encode($value);
	          exit;
	        }else{
	          $value['status'] = $res['status'];
	        }
	    }else{
	      $value['state'] = 0;
	      $value['error'] = '手机号和验证码不一致，请先确认';
	    }
	    echo json_encode($value);
    	
    }
  }
  /**
    * 用户设置密码
    * @param  string $phoneNum 手机`
    * @param  string $password 用户密码
    * @return string $value     修改成功    注册失败-错误编号
    */
  public function change_pwd(){
  	$data = I('post.');
  	$res = M('user')->where(array('phoneNum'=>$data['phoneNum']))->select();
  	if($res){
  		$code = M('code')->where(array('phoneNum'=>$data['phoneNum'],'code'=>md5($data['code'])))->select();
  		if($code){
		  	$password = md5(md5($data['password']));
		  	$int = M('user')->where(array('phoneNum'=>$data['phoneNum']))->save($data);
		  	if($int !== null){
		  		$value['state'] = 1;
		  	}else{
		  		$value['state'] = 0;
		  		$value['error'] = '修改失败';
		  	}
  		}else{
  			$value['state'] = 0;
  			$value['error'] = '验证码有误，请重新输入';
  		}
  	}else{
  		$value['state'] = 0;
  		$value['error'] = '输入手机号有误，请重新输入';
  	}
  	echo json_encode($value);
  }
  /**
   * 图片上传
   * @param file  $userpic   用户图片 	
   */
  public function get_img1(){
  	$url = C(PASE);
  	$value['path'] = $url.get_img($_FILES['userpic']);
  	if($value['path']){
  		$value['state'] = 1;
  	}else{
  		$value['state'] = 0;
  		$value['error'] = '上传头像失败';
  	}
  	echo json_encode($value);
  }
  /**
   * 设计师/施工队必填资料
   * @return [type] [description]
   */
  public function must_info(){
      $data = I('post.');
      if(!$_FILES['userpic']['name']){
      	$value['state'] = 0;
          $value['error'] = '缺少图片~';
      	echo json_encode($value);
      	exit;
      }
      if(!$data['username']){
      	$value['state'] = 0;
          $value['error'] = '缺少名称~';
      	echo json_encode($value);
      	exit;
      }
      if(!$data['type']){
      	$value['state'] = 0;
          $value['error'] = '缺少类型';
      	echo json_encode($value);
      	exit;
      }
      if(!$data['phoneNum']){
      	$value['state'] = 0;
          $value['error'] = '缺少电话~';
      	echo json_encode($value);
      	exit;
      }
      $int = M('user')->where(array('phoneNum'=>$data['phoneNum']))->field('id')->find();
      if($int){
          $value['state'] = 0;
          $value['error'] = "该用户已注册，请直接登录";
          echo json_encode($value);
          exit;
      }

      $map['userpic'] = get_img($_FILES['userpic']);
      
      $map['username'] = $data['username'];
      $map['phoneNum'] = $data['phoneNum'];
      $map['type'] = $data['type'];
      $map['registertime'] = time();
      $id = M('user')->add($map);
      if($id){
      	$value['uid'] = $id;
      	$value['state'] = 1;
      }else{
      	$value['state'] = 0;
      	$value['error'] = "输入失败";
      }
      echo json_encode($value);
  }
  /**
   * 审核资料填写
   * @return [file] [userpic]  头像
   * @return [string] [name]  姓名
   * @return [string] [phoneNum]  姓名
   * 
   */
  public function audit(){
  	$data = I('post.');

  	if(!$data['serve'] || !$data['worktime'] || !$data['goods'] || !$data['education'] || !$data['place'] || !$_FILES['cardpic']['name'] || !$data['type'] || !$data['card']){
        $value['state'] = 0;
        $value['error'] = '缺少参数~';
        echo json_encode($value);
        exit;
    }
    $list['status'] = 2;  //状态
    //工作的时间作处理
    $year = $data['worktime'];
    $list['worktime'] = strtotime("-$year year");
    $path = get_img($_FILES['cardpic']);
    $list['cardpic'] = $path;   //身份证照
    $prove = $_FILES['prove'];		// 资质证书
    if($prove){
      $ee = get_imgs($prove);
      $list['prove'] = implode(',',$ee);
    }  //证书
    $bb = $_FILES['goodcase'];
    // 优秀案例
    if($bb){
      $info1 = get_imgs($bb);
      $list['goodcase'] = implode(',',$info1);
    }
    // 插入用户详情表
    $newsinfo['username'] = $data['name'];		//用户昵称
    $newsinfo['phoneNum'] = $data['phoneNum'];		//手机号
    // 擅长传参为id数组
    $list['goods'] = $data['goods'];  //擅长
    $list['serve']  = $data['sarve'];  //服务地
    $list['card'] = $data['card'];// 身份证号
    $newsinfo['userpic'] = get_img($_FILES['userpic']);   //用户头像
    $newsinfo['type'] = $data['type'];   //用户类型
    $newsinfo['username'] = $data['name'];  //用户昵称
    $newsinfo['status'] = 2;  //用户状态
    $newsinfo['usertoken'] = md5(time());
    $newsinfo['registertime'] = time();
    $list['education'] = $data['education'];  //学历
    $list['place'] = $data['place'];  //籍贯
    $list['background'] = '2017-09-14/59b9e820d1e99.jpg';
    if(!$data['uid']){
    	$int = M('user')->add($newsinfo);
	    $list['uid'] = $int;
	    $num = M('userdetails')->add($list);
    }else{
    	$int = M('user')->where(array('id'=>$data['uid']))->save($data);
    	$num = M('userdetails')->where(array('uid'=>$data['uid']))->save($data);
    }
     $res = M('user')->where(array('phoneNum'=>$data['phoneNum']))->find();
	        if(empty($res['hx_id'])){
	            $hx_name=$res['id'];
	            $result=accreditRegister(array('username'=>$hx_name,'password'=>'123456'));
	            if($result->entities){
	              $data1['hx_id'] =$hx_name;
	              M("user")->where("id='$hx_name'")->save($data1);
	            }else{
	                $value['state'] = 0;
	                $value['error'] = '环信错误~';
	                echo json_encode($value);exit;
	            }
	        }
    if($num || $int){
      $value['state'] = 1;
      $value['uid'] = $int;      
    }else{
      $value['state'] = 0;
      $value['error'] = '上传失败~';
    }
    echo json_encode($value);
  }
  /**
   * 读取城市
   * @return [array] [value]  城市信息
   */
  	public function city(){
		$res = M('area')->where(array('level'=>1))->order('id')->getField('name',true);
		$sor = M('area')->where(array('level'=>1))->field('id')->select();
		foreach($sor as $k=>$v){
			$resource[$k] = M('area')->where(array('upid'=>$v['id']))->order('upid')->getField('name',true);
		}
		$value['city'] = $res;
		$value['area'] = $resource;
		$value['state'] = 1;
		//查询热门城市
		$remen = M('area')->where(array('level'=>2))->order('click desc')->limit('6')->getField('name',true);
		$value['remen'] = $remen;
		echo json_encode($value);
	}
	/**
	 * 读取擅长类型
	 * @param int  type    2、设计师   3、施工队
	 * @return   string  $value    擅长 
	 */
	public function guild(){
		$data = I('post.');
		if(!$data['type']){
			$value['state'] = 0;
			$value['error'] = '请选择用户类型';
		}else{
			$value['list'] = M('guild')->where($data)->field('id,name')->select();
			$value['state'] = 1;
		}
		echo json_encode($value);
	}
	/**
	 * 更改用户昵称
	 * @param $uid  int  用户id
	 * @param $username  string  用户昵称
	 * @return [type] [description]
	 */
	public function nickname(){
		$data = I('post.');
		if(!$data['uid'] || !$data['username']){
			$value['state'] = 0;
			$value['error'] = '缺少参数';
			echo json_encode($value);
			exit;
		}
		$int = M('user')->where(array('id'=>$data['uid']))->save(array('username'=>$data['username']));
		if($int !== null){
			$value['state'] = 1;
		}else{
			$value['state'] = 0; 
			$value['error'] = '更改失败';
		}
		echo json_encode($value);
	}
	/**
	 * 启动页获取用户资料
	 * @param [int] [uid]   用户id
	 * @return [array]  [value]   用户资料
	 */
	public function start(){
		$data = I('post.');
		$user = M('user')->where(array('id'=>$data['uid']))->field('id,username,phoneNum,type,signature,status,userpic,registertime,weixin,email,city,usertoken,alipay')->find();
		if($user['type'] != 1){
			$details = M('userdetails')->where(array('uid'=>$user['id']))->field()->find();
		}
		$value = $user + $details;
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '用户资料获取失败';
		}
		echo json_encode($value);
		exit;
	}
	/**
   * 设计师/施工队必填资料
   * @return [type] [description]
   */
  public function must_info1(){
      $data = I('post.');
      if(!$_FILES['userpic']['name']){
      	$value['state'] = 0;
          $value['error'] = $_FILES['userpic'];
      	echo json_encode($value);
      	exit;
      }
      if(!$data['username']){
      	$value['state'] = 0;
          $value['error'] = '缺少名称~';
      	echo json_encode($value);
      	exit;
      }
      if(!$data['type']){
      	$value['state'] = 0;
          $value['error'] = '缺少类型';
      	echo json_encode($value);
      	exit;
      }
      if(!$data['phoneNum']){
      	$value['state'] = 0;
          $value['error'] = '缺少电话~';
      	echo json_encode($value);
      	exit;
      }

      $int = M('user')->where(array('phoneNum'=>$data['phoneNum']))->field('id')->find();
      if($int){
          $value['state'] = 0;
          $value['error'] = "该用户已注册，请直接登录";
          echo json_encode($value);
          exit;
      }
      $map['userpic'] = get_img($_FILES['userpic']);
      $map['username'] = $data['username'];
      $map['phoneNum'] = $data['phoneNum'];
      $map['type'] = $data['type'];
      $id = M('user')->add($map);
      if($id){
      	$value['uid'] = $id;
      	$value['state'] = 1;
      }else{
      	$value['state'] = 0;
      	$value['error'] = "输入失败";
      }
      echo json_encode($value);

  }
  /**
   * 地址查询
   * @return [new] [更新次数]
   */
  public function area(){
  	$new = I('post.');
  	//地址未更新
  	if($new['newstNum'] == 0){
  		$value['area'] = M('area')->select();
  		$value['newstNum'] = 0;
  		//地址已更新
  	}else{
  		$map['is_new'] = array('lt',$new);
  		$value['area'] = M('area')->where($map)->select();
  		$value['newstNum'] = M('area')->order('is_new desc')->getField('is_new');
  	}
  	if($value){
  		$value['state'] = 1;
  	}else{
  		$value['state'] = 0;
  		$value['error'] = "读取城市失败";
  	}
  	echo json_encode($value);
  }
}