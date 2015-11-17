<?php

namespace app\controllers;

use Yii;

use yii\web\Controller;

use app\models\Member;
use app\models\Search;



class AppController extends Controller
{
	
	/**
	 * 个人用户登录
	 */
    public function actionLogin() {
		
		$email = empty($_REQUEST['email']) ? '' : $_REQUEST['email'];
		$member_phone = empty($_REQUEST['member_phone']) ? '' : $_REQUEST['member_phone'];
		$password = empty($_REQUEST['password']) ? '' : $_REQUEST['password'];
		
		$key = 'zhaopin200';
		$error = 'error';
		
		//是否为空
		if ($email == '' || $password == '' || $member_phone=='') {
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter transfer error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100023,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}

		//接收并防注入
		$email = $this->lib_replace_end_tag(trim($email));
		$member_phone = md5($this->lib_replace_end_tag(trim($member_phone))); 
		$password = md5($this->lib_replace_end_tag(trim($password))); 

		$Member=new Member();
		$query = $Member->checkemail($email);
		//print_r($query['password']);die;
		//var_dump($query);die;
		if($Member->checkemail($email)){
			if($password == $query['password']){
				//开启session
				$session = Yii::$app->session;
				$session->open();
				$session->set('email', $email);
				$session->set('member_id', $query['member_id']);
				$verifyCode = md5("prestr".$verify."endstr");
				//登陆成功
				$data = Array(
					'status'=>	200,
					'msg'	=>	'Landing success!',
					'sign'	=>	md5($query['email'].$key),
					'data'	=>	Array(
									'content'	=>	$query
								)
				);
				exit(json_encode($data));
			}else{
				//密码错误
				$data = Array(
					'status'=>	100,
					'msg'	=>	'Landing failure!',
					'sign'	=>	md5($error.$key),
					'data'	=>	Array(
									'code'	=>	100001,
									'content'	=>	$error
								)
				);
				exit(json_encode($data));
			}

		}else{
			//账号不存在
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'	=>	100002,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}

	}

	/**
	 * 个人用户注册
	 */
	public function register() {
		
		$key = 'zhaopin200';
		$error = 'error';
		$Member=new Member();

		$email = empty($_REQUEST['email']) ? '' : $_REQUEST['email'];
		$verify = empty($_REQUEST['verify']) ? '' : $_REQUEST['verify'];
		$password = empty($_REQUEST['password']) ? '' : $_REQUEST['password'];


		//是否为空
		if ($email == '' || $verify == '' || $password == '') {
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100023,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}
		
		//接收并防注入
		$data['email'] = $this->lib_replace_end_tag(trim($email));
		$data['verify'] = $this->lib_replace_end_tag(trim($verify));
		$data['user_pass'] = md5($this->lib_replace_end_tag(trim($password))); 

		if($Member->checkemail($data['email'])){
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100003,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}

		if($Member->checkinsert($data)){
			
			//注册成功 登陆状态
			$query = $Member->checkemail($email);
			
			$data = Array(
				'status'=>	200,
				'msg'	=>	'Success!',
				'sign'	=>	md5($query['email'].$key),
				'data'	=>	Array(
								'content'	=>	$query
							)
			);
			exit(json_encode($data));
		}
		//注册失败
		$data = Array(
			'status'=>	100,
			'msg'	=>	'Parameter error!',
			'sign'	=>	md5($error.$key),
			'data'	=>	Array(
							'code'		=>	100006,
							'content'	=>	$error
						)
		);
		exit(json_encode($data));
	}

	public function actionSet_session(){
		//开启session
		$session = Yii::$app->session;
		$session->open();
		$verify = $this->actionCreateverify();
		$verifyCode = md5("prestr".$verify."endstr");
		
		$session->set('codetime', date("Y-m-d H:i:s",time()));
		$session->set('verify', $verify);
		$session->set('randcode', $verifyCode);
		print_r($_SESSION);
	}

	/**
	 * 忘记密码 修改密码
	 */
	public function actionForgetpwd() {
		$Member=new Member();
		$key = 'zhaopin200';
		$error = 'error';
		$success = 'success';

		//开启session
		$session = Yii::$app->session;
		$session->open();
		$verify = $this->actionCreateverify();
		$verifyCode = md5("prestr".$verify."endstr");
		
		$session->set('codetime', date("Y-m-d H:i:s",time()));
		$session->set('randcode', $verifyCode);
		$member_phone = empty($_REQUEST['member_phone']) ? '' : $_REQUEST['member_phone'];
		$password = empty($_REQUEST['password']) ? '' : $_REQUEST['password'];
		$verify = empty($_SESSION['verify']) ? '' : $_SESSION['verify'];

		//是否为空
		if ($verify == '' || $member_phone == '' || $password == '') {
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'	=>	100023,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}
		
		//接收数据
		$member_phone = $this->lib_replace_end_tag(trim($member_phone));
		$password = md5($this->lib_replace_end_tag(trim($password))); 
		$verify = $this->lib_replace_end_tag(trim($verify));
		$data = $this->actionPutverify($verify);
		
		if($data['status']  == '200'){
			
			//数据库中是否有这个手机账号
			if($Member->checkphone($member_phone)){
				//修改成功
				if($Member->forgetpwd($member_phone,$password)){
					$data = Array(
						'status'=>	200,
						'msg'	=>	'Success!',
						'sign'	=>	md5($success.$key),
						'data'	=>	Array(
										'content'	=>	$success
									)
					);
					exit(json_encode($data));
				}
			}
			
			//没有这个账号，或修改失败
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Failure!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100032,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));

		}
		exit(json_encode($data));
	}

	/**
	 * 个人用户 获取手机验证码
	 */
	public function actionGetverify() {
		
		$key = 'zhaopin200';
		$error = 'error';
		$success='success';

		//开启session
		$session = Yii::$app->session;
		$session->open();

		$member_phone = empty($_REQUEST['member_phone']) ? '' : $_REQUEST['member_phone'];
		
		//判断是否过期
		$codetime = $session->get('codetime');
		if ($codetime != ''){
			if((strtotime( $codetime + 60 ) > time() )){
				//验证码还未失效
				$data = Array(
					'status'=>	100,
					'msg'	=>	'Parameter error!',
					'sign'	=>	md5($error.$key),
					'data'	=>	Array(
									'code'		=>	100011,
									'content'	=>	$error
								)
				);
				exit(json_encode($data));
			}
		}

		//生成随机验证码
		$verify = $this->actionCreateverify();
		$verifyCode = md5("prestr".$verify."endstr");

		$session->set('codetime', date("Y-m-d H:i:s",time()));
		$session->set('randcode', $verifyCode);
		$session->set('yuan', $verify);
		print_r($_SESSION);
die;
		//短信接口
		$http = 'http://api.sms.cn/mtutf8/';
		$uid = 'php1402a'; //用户账号
		$pwd = 'php1402a'; //密码
		$mobile = $member_phone; //号码，以英文逗号隔开
		$mobileids = 'PHP1402A'; //号码唯一编号
		$content = '这里是XX招聘网：您本次的验证码为：'.$verify.'，若不是本人操作，请忽视本信息。'; 
		
		//内容即时发送
		$res = $this->actionSendSMS($http,$uid,$pwd,$mobile,$content,$mobileids);
		if($res != ''){
			
			//发送成功
			$data = Array(
				'status'=>	200,
				'msg'	=>	'Send successful!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'content'=>$success
							)
			);
			exit(json_encode($data));

		}
		//发送失败
		$data = Array(
			'status'=>	100,
			'msg'	=>	'Send failed!',
			'sign'	=>	md5($error.$key),
			'data'	=>	Array(
							'code'		=>	100007,
							'content'	=>	$error
						)
		);
		exit(json_encode($data));

		/*//是否为空
		if ($member_phone == '') {
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100023,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}
		$mobile	= $this->lib_replace_end_tag(trim($member_phone));
		$data = $this->commonfunc->getverify($mobile);
		exit(json_encode($data));*/
	}

	//发送短信
	function actionSendSMS($http,$uid,$pwd,$mobile,$content,$mobileids,$time='',$mid=''){
		$data = array(
			'uid'=>$uid, //用户账号
			'pwd'=>md5($pwd.$uid), //MD5位32密码,密码和用户名拼接字符
			'mobile'=>$mobile, //号码
			'content'=>$content, //内容
			'mobileids'=>$mobileids,
			//'time'=>$time, //定时发送
		);
		$re= $this->actionPostSMS($http,$data); //POST方式提交
		return $re;
	}
	//发送短信
	function actionPostSMS($url,$data=''){
		$port="";
		$post="";
		$row = parse_url($url);			// 解析 URL，返回其组成部分 
		$host = $row['host'];
		@$port = $row['port'] ? $row['port']:80;
		$file = $row['path'];
		while (list($k,$v) = each($data)){
			$post .= rawurlencode($k)."=".rawurlencode($v)."&"; //转URL标准码
		}
		$post = substr( $post , 0 , -1 );
		$len = strlen($post);
		$fp = @fsockopen( $host ,$port, $errno, $errstr, 10);
		if (!$fp) {
			return "$errstr ($errno)\n";
		} else {
			$receive = '';
			$out = "POST $file HTTP/1.1\r\n";
			$out .= "Host: $host\r\n";
			$out .= "Content-type: application/x-www-form-urlencoded\r\n";
			$out .= "Connection: Close\r\n";
			$out .= "Content-Length: $len\r\n\r\n";
			$out .= $post;
			fwrite($fp, $out);
			while (!feof($fp)) {
				$receive .= fgets($fp, 128);
			}
			fclose($fp);
			$receive = explode("\r\n\r\n",$receive);
			unset($receive[0]);
			return implode("",$receive);
		}
	}

	// 生成手机验证码
	function actionCreateverify(){
		$str = "1,2,3,4,5,6,7,8,9,0";
		$list = explode(",", $str);
		$cmax = count($list) - 1;
		$verifyCode = '';
		for ( $i=0; $i < 6; $i++ ){
			$randnum = mt_rand(0, $cmax);
			//取出字符，组合成验证码字符
			$verifyCode .= $list[$randnum];
		}
		return $verifyCode;
	}

	/**
	 * 个人用户 验证手机验证码
	 */
	public function actionPutverify() {
		
		$key = 'zhaopin200';
		$error = 'error';
		$success='success';

		//开启session
		$session = Yii::$app->session;
		$session->open();
		
		$verify = empty($_REQUEST['verify']) ? '' : $_REQUEST['verify'];
		
		//是否为空
		if ($verify == '') {
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100023,
								'content'	=>	$error
							)
			);
			exit(json_encode($data));
		}
		
		//$verify = $this->lib_replace_end_tag(trim($verify));
		//$data = $this->commonfunc->putverify($verify);
		//exit(json_encode($data));
		

		$randcode = $session->get('randcode');
		$codetime = $session->get('codetime');
		//接收验证码，并加上前后缀
		$verifyCode = md5("prestr".$verify."endstr");


		/*echo $randcode = $session->get('randcode');
		echo "<br>";
		echo $codetime = $session->get('codetime');
		echo "<br>";
		//接收验证码，并加上前后缀
		echo $verifyCode = md5("prestr".$verify."endstr");
		echo "<br>";
		echo $session->get('yuan');die;*/

		//是否过期
		if((strtotime($codetime)+120) < time()) {
			//验证码已经失效
			$data = Array(
				'status'=>	100,
				'msg'	=>	'Parameter error!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'code'		=>	100008,
								'content'	=>	$error,
							)
			);
			exit(json_encode($data));
		}

		if($verifyCode == $randcode){
			//验证码不能为空
			$data = Array(
				'status'=>	200,
				'msg'	=>	'success!',
				'sign'	=>	md5($error.$key),
				'data'	=>	Array(
								'content'	=>	$success
							)
			);
			exit(json_encode($data));
			//return $data;
		}else{

		//验证码不能为空
		$data = Array(
			'status'=>	100,
			'msg'	=>	'Parameter error!',
			'sign'	=>	md5($error.$key),
			'data'	=>	Array(
							'code'		=>	100005,
							'content'	=>	$error,
						)
		);
		exit(json_encode($data));
		}
	}

	/**
     *行业类别信息接口  
    */
    public function actionGet_industry(){

        //接值
        $sign = $_REQUEST['sign'];
        $error = 'error';
        $key = 'zhaopin200';
        
        //判断sign值是否正确,正确返回信息，错误返回错误信息
        if($sign == md5($error.$key)){
            $query=new \yii\db\Query();
            $info = $query->select("*")->from('position')->where(['level'=>1])->all();
            $data = Array(
                    'status' => 200,
                    'msg'    => 'success',
                    'sign'   => md5($error.$key),
                    'data'   => Array(
                                    'content'  => $info
                                )
            );
            exit(json_encode($data));
        }else{
            $data = Array(
                    'status' => 100,
                    'msg'    => 'Parameter transfer error',
                    'data'   => Array(
                                    'code' => '100203',
                                    'content'  => $error
                                )
            );
            exit(json_encode($data));
        }     
    }

    /**
     *职业信息接口  
    */
    public function actionGet_professional(){

        //接值
        $sign = $_REQUEST['sign'];
        $error = 'error';
        $key = 'zhaopin200';

        //判断sign值是否正确,正确返回信息，错误返回错误信息
        if($sign == md5($error.$key)){
            $query=new \yii\db\Query();
            $info = $query->select("*")->from('position')->where(['level'=>2])->all();
            $data = Array(
                    'status' => 200,
                    'msg'    => 'success',
                    'sign'   => md5($error.$key),
                    'data'   => Array(
                                    'content'  => $info
                                )
            );
            exit(json_encode($data));
        }else{
            $data = Array(
                    'status' => 100,
                    'msg'    => 'Parameter transfer error',
                    'data'   => Array(
                                    'code' => '100203',
                                    'content'  => $error
                                )
            );
            exit(json_encode($data));
        }     
    }

    /**
     *职位信息接口  
    */
    public function actionGet_position(){

        //接值
        $sign = $_REQUEST['sign'];
        $error = 'error';
        $key = 'zhaopin200';

        //判断sign值是否正确,正确返回信息，错误返回错误信息
        if($sign == md5($error.$key)){
            $query=new \yii\db\Query();
            $info = $query->select("*")->from('position')->where(['level'=>3])->all();
            $data = Array(
                    'status' => 200,
                    'msg'    => 'success',
                    'sign'   => md5($error.$key),
                    'data'   => Array(
                                    'content'  => $info
                                )
            );
            exit(json_encode($data));
        }else{
            $data = Array(
                    'status' => 100,
                    'msg'    => 'Parameter transfer error',
                    'data'   => Array(
                                    'code' => '100203',
                                    'content'  => $error
                                )
            );
            exit(json_encode($data));
        }     
    }

    /**
     *城市信息接口  
    */
    public function actionGet_city(){
        //接值
        $sign = $_REQUEST['sign'];
        $error = 'error';
        $key = 'zhaopin200';

        //判断sign值是否正确,正确返回信息，错误返回错误信息
        if($sign == md5($error.$key)){
            $query=new \yii\db\Query();
            $info = $query->select("*")->from('region')->all();
            $data = Array(
                    'status' => 200,
                    'msg'    => 'success',
                    'sign'   => md5($error.$key),
                    'data'   => Array(
                                    'content'  => $info
                                )
            );
            exit(json_encode($data));
        }else{
           $data = Array(
                    'status' => 100,
                    'msg'    => 'Parameter transfer error',
                    'data'   => Array(
                                    'code' => '100203',
                                    'content'  => $error
                                )
            );
            exit(json_encode($data));
        }     
    }

	//职位搜索
	public function actionSearch(){
		$s_type = $_REQUEST['search_type'];
		$kd = $_REQUEST['kd'];
		$key = "zhaopin200"  ;
		//echo $s_type.$kd;
		if(empty($s_type) || empty($kd)){

			$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "Product information does not exist"
							)
			);
		}

		$page = empty($_GET['page'])?1:$_GET['page'];//当前页

		$otherCondition = array();//其它搜索条件

		//处理城市条件
		$city = !empty($_GET['city'])?$_GET['city']:'';
		//echo $city;die;
		if($city && $city != '全国'){
			
			$otherCondition['work_city'] = $city;

		}

		//处理工作条件
		$gj = !empty($_GET['gj'])?$_GET['gj']:'';
		if($gj){
			
			$otherCondition['work_year'] = $gj;

		}

		//处理学历条件
		$xl = !empty($_GET['xl'])?$_GET['xl']:'';
		if($xl){
			
			$otherCondition['education'] = $xl;

		}

		//处理工作性质条件
		$gx = !empty($_GET['gx'])?$_GET['gx']:'';
		if($gx){
			
			$otherCondition['work_type'] = $gx;

		}

		//处理发布时间条件
		$st = !empty($_GET['st'])?$_GET['st']:'';
		if($st){
			$today = strtotime(date('Y-m-d'));
			$treeday = strtotime("now")-3*3600*24;
			$weekday = strtotime("now")-7*3600*24;
			$monthday = strtotime("now")-30*3600*24;
			
			if($st == "今天"){
				
				$otherCondition['addtime'] = $today;
			}elseif($st == '一周内'){
				$otherCondition['addtime'] = $weekday;
			}elseif($st == '一月内'){
				$otherCondition['addtime'] = $monthday;
			}else{
				$otherCondition['addtime'] = $treeday;
			}

		}

		

		//处理月薪条件
		$yx = !empty($_GET['yx'])?$_GET['yx']:'';
		if($yx){
			
			if($yx == '2k以下'){

				$otherCondition['salary']['max_salary'] = '2';

			}elseif($yx == '50k以上'){

				$otherCondition['salary']['min_salary'] = '50';

			}else{

				$reg = "#^(\d*)k-(\d*)k#";

				preg_match_all($reg,$yx,$salary);

				$otherCondition['salary']['min_salary'] = $salary[1][0];
				$otherCondition['salary']['max_salary'] = $salary[2][0];

			}


		}

		if($s_type == 1 && !empty($_GET['kd'])){

			$condition = " where positiontype like '%".$_GET['kd']."%' OR rj_name like '%".$_GET['kd']."%'";

		}elseif($s_type == 2  && !empty($_GET['kd'])){
			
			$condition = $_GET['kd'];

		}else{
			$condition = NULL;
		}
		//var_dump($otherCondition);
		$model = new Search();
		$content = $model->get_where_job($condition,$page,$s_type,$otherCondition);
		$count = Yii::$app->db->createCommand($content['sql'])->queryScalar();
		$zhong = $model->get_job($count,$page,$content['condition']);

		$job_list = Yii::$app->db->createCommand($zhong['sql'])->queryAll();
		$data = $model->get_jb($job_list,$zhong['total_page']);
	
		$data['page'] = $page;
		 
		$data['kd'] = $_GET['kd'];

		$data['city'] = $city;
		//print_r($data);die;
		$datas = Array(
				'status'=>	200,
				'msg'	=>	'Landing success!',
				'data'	=>	Array(
								'content'	=> $data
							)
			);

		echo json_encode($datas);
	}


	//创建简历
	public function actionJianli(){
		$jianli = $_REQUEST['jianli'];
		if(empty($jianli)){
			$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "error"
							)
			);
		}else{
			//接收session值，判断用户
      		$session = Yii::$app->session;
			$email = $session->get('email');
  			
			if(isset($_REQUEST['email'])){
				$email = $_REQUEST['email'];
			}
			//查找会员表
			$sql = "select * from member where email='$email'";
			$arr= Yii::$app->db->createCommand($sql)->queryAll();
			$aa = "";
			foreach($arr as $k=>$v){
				$aa=$v;
			}
			$info["member"]=$aa; 
			$member_id=$info['member']['member_id'];
			
			//查找个人简历表
			$sql = "select * from resume where member_id=$member_id";
			$arr2= Yii::$app->db->createCommand($sql)->queryAll();
			$bb="";
			foreach($arr2 as $k=>$v){
				$bb=$v;
			}
			$info["resume"]=$bb;
			if($email){
				if($info['resume']){
					//查找工作经历表
					$sql = "select * from work_experience where member_id=$member_id";
					$arr3= Yii::$app->db->createCommand($sql)->queryAll();
					foreach($arr3 as $k=>$v){
						$cc=$v;
					}
					$info["work"]=$cc;
					//查找教育经历表
					$sql = "select * from edcucation_experience where member_id=$member_id";
					$arr4= Yii::$app->db->createCommand($sql)->queryAll();
					foreach($arr4 as $k=>$v){
						$dd=$v;
					}
					$info["edcucation"]=$dd;
					//展示到简历页面
					$session->set('member_id', $member_id);
	  				//print_r($info);die; 

					//$content = $this->output->get_output();
					//write_file($file_name,$content);
					$datas = Array(
						'status'=>	200,
						'msg'	=>	'Landing success!',
						'data'	=>	Array(
									'content'	=> $info
									)
						);
				}else{
					$datas = Array(
					'status'=>	200,
					'msg'	=>	'Landing success!',
					'data'	=>	Array(
								'content'	=> $info
								)
					);
				}	
			}else{
				$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "must login"
							)
			);
			}
			
		}
		//print_r($datas);die;
		echo json_encode($datas);
	}

	//信息完善页面二
	public function basic1(){   
	     $basic = $_REQUEST['basic'];
		if(empty($basic)){
			$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "error"
							)
			);
		}else{
        $member_name = $_REQUEST['name'];
        $member_phone = $_REQUEST['phone'];
        $member_id = $_REQUEST['iid'] ;  
    	//条件修改
    	$sql = "update member set member_name='$member_name',$member_phone='$member_phone' where member_id=$member_id";
       	$data = Yii::$app->db->createCommand($sql)->execute();
        
        $education = $_REQUEST['topDegree'];
        $work_years =  $_REQUEST['wokrYear'];
        $city = $_REQUEST['workCity'];
        //数据添加
        $sql = "insert into resume(education,work_years,city)values('$education','$work_years','$city')";
        $data2 = Yii::$app->db->createCommand($sql)->execute();
       	$datas = Array(
					'status'=>	200,
					'msg'	=>	'Landing success!',
					'data'	=>	Array(
								'content'	=> "success"
								)
					);
       	}
		echo json_encode($datas);
    }
        
	//信息完善页面三
    public function basic2(){  
        $basic1 = $_REQUEST['basic1'];
		if(empty($basic1)){
			$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "error"
							)
			);
		}else{
            $work_company = $_REQUEST['companyName'];
            $work_position = $_REQUEST['yourPosition'];
            $work_begin = $_REQUEST['startTime'];
            $work_end = $_REQUEST['endTime'];
            $member_id = $_REQUEST['iid'];    
           
		    //数据添加
	        $sql = "insert into work_experience(work_company,work_position,work_begin,work_end,member_id)values('$work_company','$work_position','$work_begin','$work_end',$member_id)";
        	$data = Yii::$app->db->createCommand($sql)->execute();
		    $datas = Array(
					'status'=>	200,
					'msg'	=>	'Landing success!',
					'data'	=>	Array(
								'content'	=> "success"
								)
					);
		}
			echo json_encode($datas);
}
    //信息完善页面四
    public function basic3(){
     	$basic2 = $_REQUEST['basic2'];
		if(empty($basic2)){
			$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "error"
							)
			);
		}else{
		    $e_name       = $_REQUEST['schoolName'];
		    $e_xueli      = $_REQUEST['degree'];
		    $e_jineng     = $_REQUEST['yourMajor'];
		    $e_time_end   = $_REQUEST['schoolEnd'];
		    $member_id    = $_REQUEST['iid'];
    	
		    //数据添加
		    $sql = "insert into edcucation_experience(e_name,e_xueli,e_jineng,e_time_end,member_id)values('$e_name','$e_xueli','$e_jineng','$e_time_end',$member_id)";
        	$data = Yii::$app->db->createCommand($sql)->execute();
		    $datas = Array(
					'status'=>	200,
					'msg'	=>	'Landing success!',
					'data'	=>	Array(
								'content'	=> "success"
								)
					);
		}
		echo json_encode($datas);
	}


	//最后保存
	public function basic(){    
	    $basic3 = $_REQUEST['basic3'];
		if(empty($basic3)){
			$datas = Array(
				'status'=>	100,
				'msg'	=>	'Landing failure!',
				'data'	=>	Array(
								'code'	=>	100028,
								'content'	=> "error"
							)
			);
		}else{   
	        //数据修改
	        $self   = $_REQUEST['self'];
		    $member_id    = $_REQUEST['iid'];
		    $sql = "update resume set self='$self' where member_id=$member_id";
       		$data = Yii::$app->db->createCommand($sql)->execute();
   		}
   		echo json_encode($datas);
    }

	/*
     * 查看用户简历
     */
    public function actionGetresume(){
        
        if(empty($_GET['resume_id']) || !isset($_GET['resume_id'])){
            $data['status'] = 404;
            $data['msg'] = '简历不得为空!请选择简历!';
            exit(json_encode($data));
        }
        $r_id = $_GET['resume_id'];
        $arr = Yii::$app->db->createCommand('SELECT * FROM resume where resume_id='.$r_id)->queryOne();
        if(!$arr){
            $data['status'] = 404;
            $data['msg'] = '您查询的简历不存在!请确认后再进行查询!';
        }else{
            $data['status'] = 200;
            $data['datas']  = $arr;
        }
        exit(json_encode($data));
    }
    
    /*
     * 用户修改简历 
     */
    public function actionEditresume(){
        
        if(!isset($_REQUEST['resume_id']) || empty($_REQUEST['resume_id'])){
            $data['status'] = 404;
            $data['msg'] = '简历不得为空!请选择简历!';
            exit(json_encode($data));
        }
        $str = '';
        if(isset($_REQUEST['sex']) && !empty($_REQUEST['sex'])){
            $str = "sex='".$_REQUEST['sex']."',";
        }
        if(isset($_REQUEST['education']) && !empty($_REQUEST['education'])){
            $str .= "education='".$_REQUEST['education']."',";
        }
        if(isset($_REQUEST['work_years']) && !empty($_REQUEST['work_years'])){
            $str .= "work_years='".$_REQUEST['work_years']."',";
        }
        if(isset($_REQUEST['now_status']) && !empty($_REQUEST['now_status'])){
            $str .= "now_status='".$_REQUEST['now_status']."',";
        }
        if(isset($_REQUEST['hope_city']) && !empty($_REQUEST['hope_city'])){
            $str .= "hope_city='".$_REQUEST['hope_city']."',";
        }
        if(isset($_REQUEST['hope_work_type']) && !empty($_REQUEST['hope_work_type'])){
            $str .= "hope_work_type='".$_REQUEST['hope_work_type']."',";
        }
        if(isset($_REQUEST['hope_position']) && !empty($_REQUEST['hope_position'])){
            $str .= "hope_position='".$_REQUEST['hope_position']."',";
        }
        if(isset($_REQUEST['hope_salary']) && !empty($_REQUEST['hope_salary'])){
            $str .= "hope_salary='".$_REQUEST['hope_salary']."',";
        }
        if(isset($_REQUEST['self_desc']) && !empty($_REQUEST['self_desc'])){
            $str .= "self_desc='".$_REQUEST['self_desc']."',";
        }
        if(isset($_REQUEST['birthday']) && !empty($_REQUEST['birthday'])){
            $str .= "birthday='".$_REQUEST['birthday']."',";
        }
        if(isset($_REQUEST['age']) && !empty($_REQUEST['age'])){
            $str .= "age='".$_REQUEST['age']."',";
        }
        if(isset($_REQUEST['city']) && !empty($_REQUEST['city'])){
            $str .= "city='".$_REQUEST['city']."',";
        }
        if($str == ''){
            $data['status'] = 202;
            $data['msg'] = '未修改任何内容!';
            exit(json_encode($data));
        }
        $str = substr($str, 0 ,-1);
        $r_id = $_REQUEST['resume_id'];
        $arr = Yii::$app->db->createCommand('SELECT * FROM resume where resume_id='.$r_id)->queryOne();
        if(!$arr){
            $data['status'] = 404;
            $data['msg'] = '您编辑的简历不存在!请确认后再进行编辑!';
            exit(json_encode($data));
        }
        $sql = 'update resume set '.$str.' where resume_id='.$r_id;
        $sql = str_replace('','',$sql);
        $res = Yii::$app->db->createCommand($sql)->execute();
        if($res){
            $data['status'] = 200;
            $data['msg'] = '编辑简历成功!';
                 
        }else{
            $data['status'] = 404;
            $data['msg'] = '编辑简历失败!';
        }
        exit(json_encode($data));
    }

	/** 
	 * 验证是否是手机号
	 * @access  public
	 * @param   string      $member_phone      需要验证的手机号
	 * @return bool
	 */
   function actionIs_telephone($member_phone){
	   $chars = "/^13[0-9]{1}[0-9]{8}$|15[0-9]{1}[0-9]{8}$|18[0-9]{1}[0-9]{8}$/";
	   if (preg_match($chars, $member_phone)){
		   return true;
	   }
	}

	public function lib_replace_end_tag($str){
		
		if (empty($str)) return false;
		$str = htmlspecialchars($str);
		$str = str_replace('/', "", $str);
		$str = str_replace("", "", $str);
		$str = str_replace("&gt", "", $str);
		$str = str_replace("&lt", "", $str);
		$str = str_replace("<SCRIPT>", "", $str);
		$str = str_replace("</SCRIPT>", "", $str);
		$str = str_replace("<script>", "", $str);
		$str = str_replace("</script>", "", $str);
		$str = str_replace("select", "select", $str);
		$str = str_replace("join", "join", $str);
		$str = str_replace("union", "union", $str);
		$str = str_replace("where", "where", $str);
		$str = str_replace("insert", "insert", $str);
		$str = str_replace("delete", "delete", $str);
		$str = str_replace("update", "update", $str);
		$str = str_replace("like", "like", $str);
		$str = str_replace("drop", "drop", $str);
		$str = str_replace("create", "create", $str);
		$str = str_replace("modify", "modify", $str);
		$str = str_replace("rename", "rename", $str);
		$str = str_replace("alter", "alter", $str);
		$str = str_replace("cast", "cast", $str);
		$str = str_replace("&", "&", $str);
		$str = str_replace(">", ">", $str);
		$str = str_replace("<", "<", $str);
		$str = str_replace(" ", chr(32), $str);
		$str = str_replace(" ", chr(9), $str);
		$str = str_replace("    ", chr(9), $str);
		$str = str_replace("&", chr(34), $str);
		$str = str_replace("'", chr(39), $str);
		$str = str_replace("<br />", chr(13), $str);
		$str = str_replace("''", "'", $str);
		$str = str_replace("css", "'", $str);
		$str = str_replace("CSS", "'", $str);
		return $str;
	}
}
