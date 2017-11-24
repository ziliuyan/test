<?php
	/**
	 *用户上传图片
	 * @param  string $img 图片信息
	 */
	function get_img($img){
		$upload = new \Think\Upload();// 实例化上传类
      	$upload->maxSize   =     99999999;// 设置附件上传大小
      	$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
      	$upload->savePath  =     '/'; // 设置附件上传根目录
      	$upload->saveName  = array('uniqid', '');//上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
      	$upload->saveExt   = 'jpg';
      	$upload->replace   = true;//存在同名是否覆盖
      	$upload->hash      = true; //是否生成hash编码
      	$upload->callback  = false;//检测文件是否存在回调函数，如果存在返回文件信息数组
     	 	// 上传文件 
      	$info   =   $upload->uploadOne($img);
      	$img = $info['savepath'].$info['savename'];
      	return $img;
      	$dir = 	$info['savepath'];
      	$filename = $info['savename'];
  		$path= './Uploads'.$dir;
  		// // if(file_exists($path)){
  			$img =new \Think\Image();//实例化
	        $img->open($path.$filename);//打开物理图片
	       // 使用thumb方法生成缩略图并改名为：som_.$filename此时在项目根目录上
	        $img->thumb(750,750)->save(som_.$filename);
	        //重新赋值方便处理
	        $oldfile=som_.$filename;
	        //rename()更改成新的文件名，此时还在项目根目录上
	        rename($oldfile, s_.$filename);
	        //重新赋值方便处理 new_.$filename为更名后新文件名
	        $newfile=s_.$filename;
	        //移动新文件到物理$path 目录最终生缩略图文件为：new_xxxx.jpg(后缀名不作更改只是在前加了new_)
	        rename($newfile,"$path/$newfile" );
	        //$thumb获取缩略图的地址和文件名用于写放数据库用
	        // $thumb  = $file['savepath'].$newfile;
	        $thumb  = ltrim($info['savepath'],'/').$newfile;
		    return $thumb; 
  		// }else{
  		// 	return $img;
  		// }
  
	}
	// 旋转图片
	 // function rotate($filename,$degrees){
  //       //创建图像资源，以jpeg格式为例
  //       $source = imagecreatefromjpeg($filename);
  //       //使用imagerotate()函数按指定的角度旋转
  //       $rotate = imagerotate($source, $degrees, 0);
  //       //旋转后的图片保存
  //       $imagejpeg($rotate,$filename);
  //   }
 
    //把一幅图像brophp.jpg旋转180度
    // rotate("brophp", 180);

	function get_imgs($bb){
		$upload = new \Think\Upload();// 实例化上传类
      	$upload->maxSize   =     99999999;// 设置附件上传大小
      	$upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
      	$upload->savePath  =     '/'; // 设置附件上传根目录
      	$upload->saveName  = array('uniqid', '');//上传文件命名规则，[0]-函数名，[1]-参数，多个参数使用数组
      
      	// $upload->thumb = true; //设置需要生成缩略图，仅对图像文件有效 
      	// $upload->thumbPrefix = 's_';  //生成1张缩略图 
       //  //设置缩略图最大宽度 
       //  $upload->thumbMaxWidth = '80'; 
       //  //设置缩略图最大高度 
       //  $upload->thumbMaxHeight = '80';
       //  $upload->thumbPath = '/';  // 缩略图保存路径
      	$upload->saveExt   = 'jpg';
      	$upload->replace   = true;//存在同名是否覆盖
      	$upload->hash      = true; //是否生成hash编码
      	$upload->callback  = false;//检测文件是否存在回调函数，如果存在返回文件信息数组
     	// 上传文件 
      	$info   =   $upload->upload(array($bb));
      	foreach($info as $k=>$v){
        	$filename = $v['savename'];
        	$img = "./Uploads".$v['savepath'].$v['savename'];//获取文件上传目录  
  			$dir = 	$v['savepath'];
      		$path= './Uploads'.$dir;
      		// if(file_exists($path)){
      			$img =new \Think\Image();//实例化
		        $img->open($path.$filename);//打开物理图片
		       //使用thumb方法生成缩略图并改名为：som_.$filename此时在项目根目录上
		        $img->thumb(750,750)->save(som_.$filename);
		        //重新赋值方便处理
		        $oldfile=som_.$filename;
		        //rename()更改成新的文件名，此时还在项目根目录上
		        rename($oldfile, s_.$filename);
		        //重新赋值方便处理 new_.$filename为更名后新文件名
		        $newfile=s_.$filename;
		        //移动新文件到物理$path 目录最终生缩略图文件为：new_xxxx.jpg(后缀名不作更改只是在前加了new_)
		        rename($newfile,"$path/$newfile" );
		        //$thumb获取缩略图的地址和文件名用于写放数据库用
		        // $thumb [] = $file['savepath'].$newfile;
		        $thumb[]  = ltrim($dir,'/').$newfile;
		    // }else{
		    // 	$thumb[]  = $img;
		    // }

      	}
      	
        return $thumb; 
	}

	function array_unset_tt($arr,$key){     
        //建立一个目标数组  
        $res = array();        
        foreach ($arr as $value) {           
           //查看有没有重复项  
           $tmp = array();  
           if(isset($res[$value[$key]])){  
                 //有：销毁  
                  
                 unset($value[$key]);  
                   
           }  
           else{  
                  
                $res[$value[$key]] = $value;  
           }    
        }  
        return $res;  
    } 

    //对象转数组,使用get_object_vars返回对象属性组成的数组
	function objectToArray($obj){
	    $arr = is_object($obj) ? get_object_vars($obj) : $obj;
	    if(is_array($arr)){
	        return array_map(__FUNCTION__, $arr);
	    }else{
	        return $arr;
	    }
	}
	/**
	*上传图片存储数据库路径的处理
	* @param  string $img 图片信息
	*/
	function img_path($str){
		$str = trim($str,'.');
		$str = substr($str,9);
		return $str;
	}

	/**
	*报价页房屋信息处理
	*@param string    $shi 室  
	*@param string    $ting 厅  
	*@param string    $wei 卫 
	*@param string    $chu 厨房 
	*@param string    $yangtai 阳台
	*/
	function housetype($shi,$ting,$wei,$chu,$yangtai){
		$str = '';
		if($shi != '无' ){
			$str .=$shi.'室';
		}
		if($ting !='无'){
			$str .=$ting.'厅';
		}

		if($wei != '无'){
			$str .=$wei.'卫';
		}
		if($chu != '无'){
			$str .=$chu.'厨';
		}
		if($yangtai != '无'){
			$str .=$yangtai.'阳台';
		}
		return $str;
	}

	// 递归获取评论
	function get_pinglun($id,$pid){ 

	    $res = M('flowcomment')->where(array('did'=>$id,'pid'=>$pid))->field('content,pid,uid,did,id')->select();
	    static $str = array();
	    foreach($res as $k=>$bb){
	    	$str[] = $bb;
	    	$num = M('flowcomment')->where(array('did'=>$id,'pid'=>$bb['id']))->count();
	    	for($i=0;$i<$num;$i++){
	            get_pinglun($id,$bb['id']);
	        }
	    }
	    return  $str;
	    unset($str);
	}
	/**
	* 	设计师（施工队长）工作时间显示处理
	* 	@param int $Time 待显示的时间
	*/
	function worktime($Time){
		$cTime = data('Y',time());
		$sTime = date('Y',$Time);
		$wTime = $cTime - $sTime;
		return $wTime;
	}
	/**
	 * 友好的时间显示
	 *
	 * @param int    $sTime 待显示的时间
	 * @param string $type  类型. normal | mohu | full | ymd | other
	 * @param string $alt   已失效
	 * @return string
	 */
	function friendlyDate($sTime,$type = 'normal',$alt = 'false') {
	    if (!$sTime)
	        return '';
	    //sTime=源时间，cTime=当前时间，dTime=时间差
	    $cTime      =   time();
	    $dTime      =   $cTime - $sTime;
	    $dDay       =   intval(date("z",$cTime)) - intval(date("z",$sTime));
	    //$dDay     =   intval($dTime/3600/24);
	    $dYear      =   intval(date("Y",$cTime)) - intval(date("Y",$sTime));
	    //normal：n秒前，n分钟前，n小时前，日期
	    if($type=='normal'){
	        if( $dTime < 60 ){
				
				if($dTime<0){
					 return date("Y-m-d H:i",$sTime);    //by yangjs
				}else if($dTime < 10){
	                return '刚刚';    //by yangjs
	            }else{
	                return intval(floor($dTime / 10) * 10)."秒前";
	            }
	        }elseif( $dTime < 3600 ){
	            return intval($dTime/60)."分钟前";
	            //今天的数据.年份相同.日期相同.
	        }elseif( $dYear==0 && $dDay == 0  ){

	            return intval($dTime/3600)."小时前";
	            //return '今天'.date('H:i',$sTime);

	        }elseif($dYear==0){
	            return date("m月d日",$sTime);
	        }else{
	            return date("Y-m-d",$sTime);
	        }
	    }elseif($type=='mohu'){
	        if( $dTime < 60 ){
	            return $dTime."秒前";
	        }elseif( $dTime < 3600 ){
	            return intval($dTime/60)."分钟前";
	        }elseif( $dTime >= 3600 && $dDay == 0  ){
	            return intval($dTime/3600)."小时前";
	        }elseif( $dDay > 0 && $dDay<=7 ){
	            return intval($dDay)."天前";
	        }elseif( $dDay > 7 &&  $dDay <= 30 ){
	            return intval($dDay/7) . '周前';
	        }elseif( $dDay > 30 ){
	            return intval($dDay/30) . '个月前';
	        }
	        //full: Y-m-d , H:i:s
	    }elseif($type=='full'){
	        return date("Y-m-d , H:i:s",$sTime);
	    }elseif($type=='ymd'){
	        return date("Y-m-d",$sTime);
	    }else{
	        if( $dTime < 60 ){
	            return $dTime."秒前";
	        }elseif( $dTime < 3600 ){
	            return intval($dTime/60)."分钟前";
	        }elseif( $dTime >= 3600 && $dDay == 0  ){
	            return intval($dTime/3600)."小时前";
	        }elseif($dYear==0){
	            return date("Y-m-d H:i:s",$sTime);
	        }else{
	            return date("Y-m-d H:i:s",$sTime);
	        }
	    }
	}


	/**
	 * 系统邮件发送函数
	 * @param string $to    接收邮件者邮箱
	 * @param string $name  接收邮件者名称
	 * @param string $subject 邮件主题 
	 * @param string $body    邮件内容
	 * @param string $attachment 附件列表
	 * @return boolean 
	 */
	function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null){
		$config = C('THINK_EMAIL');
		Vendor('PHPMailer.PHPMailerAutoload'); //从PHPMailer目录导class.phpmailer.php类文件
		$mail =new PHPMailer(); //PHPMailer对象
		$mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
		$mail->IsSMTP(); // 设定使用SMTP服务
		$mail->SMTPDebug = 0; // 关闭SMTP调试功能
		// 1 = errors and messages
		// 2 = messages only
		$mail->SMTPAuth = true; // 启用 SMTP 验证功能
		$mail->SMTPSecure = 'ssl'; // 使用安全协议
		$mail->Host = $config['SMTP_HOST']; // SMTP 服务器
		$mail->Port = $config['SMTP_PORT']; // SMTP服务器的端口号
		$mail->Username = $config['SMTP_USER']; // SMTP服务器用户名
		$mail->Password = $config['SMTP_PASS']; // SMTP服务器密码
		$mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);
		$replyEmail = $config['REPLY_EMAIL']?$config['REPLY_EMAIL']:$config['FROM_EMAIL'];
		$replyName = $config['REPLY_NAME']?$config['REPLY_NAME']:$config['FROM_NAME'];
		$mail->AddReplyTo($replyEmail, $replyName);
		$mail->Subject = $subject;
		$mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";
		$mail->MsgHTML($body);
		$mail->AddAddress($to, $name);
		if(is_array($attachment)){ // 添加附件
			foreach ($attachment as $file){
			is_file($file) && $mail->AddAttachment($file);
			}

		}

		return $mail->Send() ? true : $mail->ErrorInfo;
	}
	
	function accreditRegister($options='') {
		$config = C('huanxin');
		$url = $path = 'https://a1.easemob.com/'.implode('/',explode('#',$config['appkey'])).'/users';
		//$url = $path = 'https://a1.easemob.com/dyz-2010/milu/users';
		$access_token =getToken ();
		$header [] = 'Authorization: Bearer ' . $access_token;  
		$result =postCurl( $url, $options, $header );
		$result = json_decode($result);
		return $result;
		/*if(isset($result['entities']))
		{ 
			echo '成功';
		}*/
	}
	function getToken() {
		$config = C('huanxin');
		$path = 'https://a1.easemob.com/' . implode('/', explode('#', $config['appkey'])) . '/';
		$option ['grant_type'] = "client_credentials";
		$option ['client_id'] = $config['client_id'];
		$option ['client_secret'] = $config['client_secret'];
		$url = $path . "token";
		if ($config['expires_in'] > time ()) {
			return $config['access_token'];
		}
		$result =postCurl ( $url, $option, $head = array());
		$result = json_decode($result,true);
		//var_dump($result);die;
		$result ['expires_in'] += time ();
		/*$fp = @fopen ( "easemob.txt", 'w' );
		@fwrite ( $fp, serialize ( $result ) );
		fclose ( $fp );*/
		// $config['access_token'] = $result['access_token'];
		// $config['expires_in'] = $result['expires_in'];
		// $config['application'] = $result['application'];
		// save_config('default',array('hx_cnf' => $config));
		return $result ['access_token'];	
	}
	function postCurl($url, $option, $header = array(), $type = 'POST') {
		$curl = curl_init (); // 启动一个CURL会话
		curl_setopt ( $curl, CURLOPT_URL, $url ); // 要访问的地址
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYPEER, FALSE ); // 对认证证书来源的检查
		curl_setopt ( $curl, CURLOPT_SSL_VERIFYHOST, FALSE ); // 从证书中检查SSL加密算法是否存在
		curl_setopt ( $curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0)' ); // 模拟用户使用的浏览器
		if (! empty ( $option )) {
			$options = json_encode ( $option );
			curl_setopt ( $curl, CURLOPT_POSTFIELDS, $options ); // Post提交的数据包
		}
		curl_setopt ( $curl, CURLOPT_TIMEOUT, 30 ); // 设置超时限制防止死循环
		curl_setopt ( $curl, CURLOPT_HTTPHEADER, $header ); // 设置HTTP头
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, 1 ); // 获取的信息以文件流的形式返回
		curl_setopt ( $curl, CURLOPT_CUSTOMREQUEST, $type );
		$result = curl_exec ( $curl ); // 执行操作
		//$res = object_array ( json_decode ( $result ) );
		//$res ['status'] = curl_getinfo ( $curl, CURLINFO_HTTP_CODE );
		//pre ( $res );
		curl_close ( $curl ); // 关闭CURL会话
		return $result;
	}
	//获得账户
	function get_user($ch,$apikey){		https://sms.yunpian.com/v2/sms/single_send.json
	    curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/user/get.json');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
	    return curl_exec($ch);
	}

	function send($ch,$data){
	    curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/send.json');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    return curl_exec($ch);
	}

	function tpl_send($ch,$data){
	    curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v1/sms/tpl_send.json');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    return curl_exec($ch);
	}
	function voice_send($ch,$data){
	    curl_setopt ($ch, CURLOPT_URL, 'http://voice.yunpian.com/v1/voice/send.json');
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
	    return curl_exec($ch);
	}

    function my_array_unique($arr,$key){
	    $tmp_arr = array();
	    foreach($arr as $k => $v)
	    {
	        if(in_array($v[$key], $tmp_arr))   //搜索$v[$key]是否在$tmp_arr数组中存在，若存在返回true
	        {
	            unset($arr[$k]); //销毁一个变量  如果$tmp_arr中已存在相同的值就删除该值
	        }
	        else {
	            $tmp_arr[$k] = $v[$key];  //将不同的值放在该数组中保存
	        }
	   	}
	   //ksort($arr); //ksort函数对数组进行排序(保留原键值key)  sort为不保留key值
	    return $arr;
    }

    /**
	 * 下载远程文件
	 * @param  string  $url     网址
	 * @param  string  $filename    保存文件名
	 * @param  integer $timeout 过期时间
	 * return boolean|string
	*/
	function http_down($filename) {
	    $fileinfo = pathinfo($filename);
        header('Content-type: application/x-'.$fileinfo['extension']);
        header('Content-Disposition: attachment; filename='.$fileinfo['basename']);
        readfile($filename);
	}
	function download($file_url,$new_name=''){
        if(!isset($file_url)||trim($file_url)==''){  
            echo '500';  
        }  
        if(!file_exists($file_url)){ //检查文件是否存在  
            echo '404';  
        } 
        $file_name=basename($file_url);  
        $file_type=explode('.',$file_url);  
        $file_type=$file_type[count($file_type)-1];  
        $file_name=trim($new_name=='')?$file_name:urlencode($new_name);  
        $file_type=fopen($file_url,'r'); //打开文件  
        //输入文件标签 
        header("Content-type: application/octet-stream");  
        header("Accept-Ranges: bytes");  
        header("Accept-Length: ".filesize($file_url));  
        header("Content-Disposition: attachment; filename=".$file_name);  
        //输出文件内容  
        echo fread($file_type,filesize($file_url));  
        fclose($file_type);
	}
