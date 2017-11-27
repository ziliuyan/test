<?php
namespace Home\Controller;
use Think\Controller;
use Common\Builder\WsMessageSend;
class ShareController extends Controller {
	/**
	 * 标签选择
	 * @return [type] [description]
	 */
	public function label(){
		$value['list'] = M('share_label')->where(array('is_show'=>1))->field('id,name')->select();
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '无标签';
		}
		echo json_encode($value);
	}
	/**
	 * 添加分享文章
	 * @return [type] [int]   类型：1装修故事，2主题活动，3问答
	 */
	public function addshare(){
		$data = I('post.');
		if(!$data['uid'] || !$data['title'] || !$data['content'] || !$data['type']){
			$value['state'] = 0;
			$value['error'] = '参数不全';
			echo json_encode($value);
			exit;
		}
		$map['title'] = $data['title'];
		$map['content'] = $data['content'];
		$map['uid'] = $data['uid'];
		$map['type'] = $data['type'];
		$map['label'] = $data['label'];
		$map['addtime'] = time();
		if(!$data['id']){
			$res = M('share')->add($map);
			$re['pid'] = $res;
			$re['img'] = $data['pic'];
			$re['type'] = $data['type'];
			M('share_img')->add($re);
		}else{
			$res = M('share')->where(array('id'=>$data['id']))->save($map);
		}
		if($res){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '添加失败';
		}
		echo json_encode($value);
	}
	/**
	 * 分类列表
	 * @return [type] [description]
	 */
	public function calssify(){
		$value['calssfiy'] = M('share_calssify')->field('name,type')->select();
		if($value){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '类型读取失败';
		}
		echo json_encode($value);
	}
	/**
	 * 分享首页
	 * @return type [int]   1我的关注，2推荐，3装修故事  4问答   5活动
	 */
	public function share_show($r=5){
		$data = I('post.'); 
		$url = C('PASE');
		$search = $data['search'];
		$aa = array();
		$n = 0;
		$page = $_POST['page']?$_POST['page']:1;
		if($search){
			$map['title|content|label'] = array('like','%'.$search.'%');
		}
		$type = M('share_calssify')->where(array('name'=>$data['name']))->getField('type');
		//推荐
		if($data['name'] == '推荐'){
			//按数量查询前9标签
			$label = M('share_num')->where(array('uid'=>$data['uid']))->order('num desc')->limit('9')->field('label')->select();
			// 所有文章ID和标签
			$share = M('share')->where($map)->order('addtime desc')->field('id,label')->select();
			//如果有浏览标签记录
			if($share){
				//循环所有文章
				foreach($share as $k => $v){
					//所有标签
					foreach($label as $y => $z){
						// 如果文章标签内有浏览标签，拿出
						$int = strpos($share[$k]['label'],$label[$y]['label']);
						// echo json_encode($int.',');
						if($int !== false){
							$id[] = $share[$k]['id'];
							continue 2;
						}
					}
					// 如果文章内没有标签记录，放入另一个数组
					$ids[] = $share[$k]['id'];
				}
				// 将两个数组合并
				if($ids && $id){
					$id = array_merge($id,$ids);
				}elseif(!$ids){
					$id = $id;
				}else{
					$id = $ids;
				}
				// 根据拿到的排序ID，读取
				foreach($id as $k => $v){
					$res[] = M('share')->where(array('id'=>$id[$k]))->field('id,uid,title,content,addtime,label,type,praise,help')->find();
					if($value && $res){
						$value['list'] = array_merge($value['list'],$res);
					}else{
						$value['list'] = $res;
					}
					unset($res);
				}
			}else{
				// 如果没有浏览记录，按时间倒序取出
				$value['list'] = M('share')->order('addtime desc')->page($page,$r)->field('id,uid,title,content,addtime,label,type,praise,help')->select();
			}
		}elseif($data['name'] == '我的关注'){
			// 我的关注
			$id = M('share_collect')->where(array('uid'=>$data['uid'],'type'=>3))->order('addtime desc')->field('collect_id')->select();
			foreach($id as $k => $v){
				$value['list'][$k] = M('share')->where(array('id'=>$id[$k]['collect_id']))->page($page,$r)->order('addtime desc')->field('id,uid,title,content,addtime,label,type,praise,help')->find();
			}
			
		}else{
			$value['list'] = M('share')->where(array('type' => $type))
							->page($page,$r)
							->order('addtime desc')
							->field('id,uid,title,content,addtime,label,type,praise,help')
							->select();
		}
		foreach($value['list'] as $k=>$v){
			$value['list'][$k]['content'] = str_replace('<img src="',' ',$value['list'][$k]['content']);
			$value['list'][$k]['content'] = str_replace('"/>',' ',$value['list'][$k]['content']);
		}
		
		// 读取作者信息、内容图片
		foreach($value['list'] as $k => $v){
			$user = M('user')->where(array('id'=>$value['list'][$k]['uid']))->field('username,userpic,type')->find();
			$value['list'][$k]['username'] = $user['username'];
			$value['list'][$k]['userpic'] = $url.$user['userpic'];
			$value['list'][$k]['usertype'] = $user['type'];
			$is_img = M('share_img')->where(array('pid'=>$value['list'][$k]['id']))->field('id')->find();
			if($is_img){
				$value['list'][$k]['is_img'] = 1;
			}else{
				$value['list'][$k]['is_img'] = 0;
			}
			$img = M('share_img')->where(array('pid'=>$value['list'][$k]['id'],'type'=>1))->getField('img',true);
			$num = M('share_img')->where(array('pid'=>$value['list'][$k]['id'],'type'=>1))->count();
			$value['list'][$k]['num'] = $num;
			$value['list'][$k]['img'] = $img;
		}
		if($value['list']){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '读取失败';
		}
		echo json_encode($value);
	}
	/**
	 * 分享详情页
	 * @return [type] [description]
	 */
	public function details(){
		$data = I('post.');
		$url = C('PASE');
		$problem = M('share')->where(array('id'=>$data['id']))->field('id,uid,title,content,addtime,label,type,praise,help')->find();
		$user = M('user')->where(array('id'=>$problem['uid']))->field('username,userpic')->find();
		$problem['username'] = $user['username'];
		$problem['userpic'] = $url.$user['userpic'];
		$value['problem'] = $problem;
		// if($problem['type'] == 3){
			if($data['type'] == 1){
				$answer = M('share_comment')->where(array('pid'=>$problem['id']))->order('addtime desc')->field('id,uid,content,help,addtime')->select();
			}else{
				$answer = M('share_comment')->where(array('pid'=>$problem['id']))->order('praise desc')->field('id,uid,content,help,addtime')->select();
			}
			foreach($answer as $k => $v){
				$use = M('user')->where(array('id'=>$answer[$k]['uid']))->field('username,userpic')->find();
				$answer[$k]['name'] = $user['username'];
				$answer[$k]['pic'] = $url.$user['userpic'];
				$img = M('share_img')->where(array('pid'=>$answer[$k]['id'],'type'=>2))->count();
				$answer[$k]['img'] = $img;
				unset($use);
			}
			if($answer){
				$value['answer'] = $answer;
			}else{
				$value['answer'] = 0;
			}
		// }else{
			// 是否点赞
			$is_praise = M('share_collect')->where(array('collect_id'=>$value['problem'],'uid'=>$data['uid'],'type'=>1))->field()->find();
			if($is_praise){
				$value['problem']['is_praise'] = 1;
			}else{
				$value['problem']['is_praise'] = 0;
			}
			// 是否有帮助
			$is_help = M('share_collect')->where(array('collect_id'=>$value['problem'],'uid'=>$data['uid'],'type'=>2))->field()->find();
			if($is_praise){
				$value['problem']['is_help'] = 1;
			}else{
				$value['problem']['is_help'] = 0;
			}
			// 是否收藏
			$is_collect = M('share_collect')->where(array('collect_id'=>$value['problem'],'uid'=>$data['uid'],'type'=>3))->field()->find();
			if($is_collect){
				$value['problem']['is_collect'] = 1;
			}else{
				$value['problem']['is_collect'] = 0;
			}
		// }
		if($value['problem']){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = '网络错误';
		}

		echo json_encode($value);
	}
	/**
	 * 点击有帮助
	 * @return type [int]   1装修故事，2问答，3主题活动
	 * @return oreration int 1点赞 ，2有帮助, 3 收藏
	 * cancel  1 有帮助  ，0取消有帮助
	 */
	public function help(){
		$data = I('post.');
		// 有帮助
		if($data['cancel']){
			if($data['type'] != 2){
				$map['type'] = $data['type'];
				$map['id'] = $data['id'];
				$int = M('share')->where($map)->setInc('help',1);
			}else{
				$map['id'] = $data['id'];
				$int = M('share_comment')->where($map)->setInc('help',1);
			}
			$res['uid'] = $data['uid'];
			$res['type'] = $data['type'];
			$res['collect_id'] = $data['id'];
			$res['operation'] = 2;
			$res['addtime'] = time();
			$help = M('share_collect')->add($res);
			if($res){
				$value['state'] = 1;
			}else{
				$value['state'] =0;
				$value['error'] = '点击失败';
			}
		}else{
		//取消有帮助
			if($data['type'] != 2){
				$map['type'] = $data['type'];
				$map['id'] = $data['id'];
				$int = M('share')->where($map)->setDec('help',1);
			}else{
				$map['id'] = $data['id'];
				$int = M('share_comment')->where($map)->setDec('help',1);
			}
			$int = M('share_collect')->where(array('collect_id'=>$data['id'],'uid'=>$data['uid']))->delete();
			if($int){
				$value['state'] = 1;
			}else{
				$value['state'] =0;
				$value['error'] = "取消失败";
			}
		}
		echo json_encode($value);
	}
	/**
	 * 点赞
	 * @return type [int]   1装修故事，2问答，3主题活动
	 */
	public function praise(){
		$data = I('post.');
		if($data['cancel']){
			if($data['type'] != 2){
				$map['type'] = $data['type'];
				$map['id'] = $data['id'];
				$int = M('share')->where($map)->setInc('praise',1);
			}else{
				$map['id'] = $data['id'];
				$int = M('share_comment')->where($map)->setInc('praise',1);
			}
			$res['uid'] = $data['uid'];
			$res['type'] = $data['type'];
			$res['collect_id'] = $data['id'];
			$res['operation'] = 1;
			$res['addtime'] = time();
			$help = M('share_collect')->add($res);
			if($int){
				$value['state'] = 1;
			}else{
				$value['state'] =0;
				$value['error'] = '点击失败';
			}
		}else{
			//取消有帮助
			if($data['type'] != 2){
				$map['type'] = $data['type'];
				$map['id'] = $data['id'];
				$int = M('share')->where($map)->setDec('help',1);
			}else{
				$map['id'] = $data['id'];
				$int = M('share_comment')->where($map)->setDec('help',1);
			}
			$int = M('share_collect')->where(array('collect_id'=>$data['id'],'uid'=>$data['uid']))->delete();
			if($int){
				$value['state'] = 1;
			}else{
				$value['state'] =0;
				$value['error'] = "取消失败";
			}
		}
		echo json_encode($value);
	}
	/**
	 * 收藏
	 * @return type [int]   1装修故事，2问答，3主题活动
	 */
	public function collect(){
		$data = I('post.');
		if($data['cancel']){
			$res['uid'] = $data['uid'];
			$res['type'] = $data['type'];
			$res['collect_id'] = $data['id'];
			$res['operation'] = 3;
			$res['addtime'] = time();
			$help = M('share_collect')->add($res);
			if($res){
				$value['state'] = 1;
			}else{
				$value['state'] =0;
				$value['error'] = '点击失败';
			}
		}else{
			$int = M('share_collect')->where(array('collect_id'=>$data['id'],'uid'=>$data['uid']))->delete();
			if($int){
				$value['state'] = 1;
			}else{
				$value['state'] =0;
				$value['error'] = "取消失败";
			}
		}
		echo json_encode($value);
	}
	/**
	 * 评论
	 * @return [type] [int] 1装修故事，2问答，3主题活动
	 */
	public function comment(){
		$data = I('post.');
		$map['pid'] = $data['id'];
		$map['uid'] = $data['uid'];
		$map['content'] = $data['content'];
		$map['addtime'] = time();
		$map['type'] = $data['type'];
		$int = M('share_comment')->add($map);
		if($int){
			$value['state'] = 1;
		}else{
			$value['state'] = 0;
			$value['error'] = "评论失败";
		}
		echo json_encode($value);
	}
	/**
	 * 图片上传
	 * @return [type] [description]
	 */
	  public function get_img(){
	  	$url = C(PASE);
	  	$value['path'] = $url.get_img($_FILES['pic']);
	  	if($value['path']){
	  		$value['state'] = 1;
	  	}else{
	  		$value['state'] = 0;
	  		$value['error'] = '上传失败,请重新上传';
	  	}
	  	echo json_encode($value);
	  }
	  /**
	   * 测试富文本
	   * @return [type] [description]
	   */
	  public function textstr(){
	  	$data = I('post.');
	  	$map['test'] = $data['str'];
	  	$int = M('test')->add($map);
	  	$str = M('test')->where(array('id'=>5))->getField('test');
	  	if($int){
	  		$value['state'] = 1;
	  		$value['str'] = $str;
	  	}else{
	  		$value['state'] = 0;
	  		$value['error'] = '出错了';
	  	}
	  	echo json_encode($value);
	  }

	}
