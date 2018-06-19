<?php
/**
 * @author long
 * @date(2016-08-15)
 * @version 1.0 [<description>] 
 */
class  UserAdmin extends MY_Controller{
	function __construct()
	{
		parent::__construct();
        // 加载model
        $this->load->model('Manager');
        //$this->setModel([]);
	}

	 public function getCaptcha(){
            $this->load->library('captcha');

//   		$code = $this->captcha->getCaptcha();
//   		$this->session->set_userdata('code', $code);
//  		 $this->captcha->showImg();
            $code = $this->captcha->captcha(90,40);
            $this->session->set_userdata('code', $code);
            //$this->captcha->showImg();

        }

	function islogin(){
		if (!($this->session->has_userdata('manager_id')))
        {
            $this->load->view('admin/login');
		}else{
 			redirect('admin/ActionIndex/home');
		}
	}

	function login(){
		$username = $this->input->post('username',true);
		$pass = $this->input->post('password',true);
		$code = $this->input->post('code',true);

		if(is_null($code)){
			echo json_encode(['errno'=>0,'msg'=>'验证码不能为空']);
			return false;
		}

		if(is_null($username)){
			echo json_encode(['errno'=>0,'msg'=>'用户名不能为空']);
			return false;
		}

		if(is_null($pass)){

			echo json_encode(['errno'=>0,'msg'=>'密码不能为空']);
			return false;
		}

		if(strtolower($this->session->userdata ( 'code' )) != strtolower($code)){
                echo json_encode(['errno'=>9,'msg'=>'验证码错误']);
				//清掉验证码
				$this->session->set_userdata('code', null);
				return false;
			}//*/

		$rs = $this->Manager->readByAnd(['condition'=>['name'=>$username,'status'=>3],'select'=>'id,passwd,nickname,salt,roleid']);
		if($rs['rows'] == 1){
			$input_pass = md5($pass.$rs['data']['salt']);
			//var_dump($input_pass);
			if($input_pass == $rs['data']['passwd']){
				$this->session->set_userdata('manager_id', $rs['data']['id']);
                $this->session->set_userdata('nickname', $rs['data']['nickname']);
                $this->session->set_userdata('role', $rs['data']['roleid']);
                echo json_encode(['errno'=>1,'msg'=>'登录成功']);
				return false;

			}else{				
				echo json_encode(['errno'=>0,'msg'=>'用户名或者密码错误']);
				return false;
			}
		}else{
			echo json_encode(['errno'=>0,'msg'=>'用户名不存在']);
				return false;
		}

	}

	public function logout()
	{
		//$this->isLogin();
		$this->session->sess_destroy();
		redirect('admin/ActionIndex/home');
	}

	function editUser(){
		
		$nickname = $this->input->post('nickname',true);

		$newpass = $this->input->post('newpass',true);

		$secnewpass = $this->input->post('secnewpass',true);

		if(is_null($newpass) || $newpass == ''){
			echo json_encode(['errno'=>0,'msg'=>'密码不能为空']);
			return false;
		}

		if($newpass != $secnewpass){
			echo json_encode(['errno'=>0,'msg'=>'密码不一致']);
			return false;
		}

		if($nickname == '' || is_null($nickname)){
			echo json_encode(['errno'=>0,'msg'=>'昵称不可以为空']);
			return false;
		}
		
		$salt = md5(rand(0,99999));

		$pass = md5($newpass.$salt);

		if($this->Manager->update(['id'=>$this->session->manager_id],['nickname'=>$nickname,'salt'=>$salt,'passwd'=>$pass])){	
			echo json_encode(['errno'=>1]);
			$this->session->set_userdata('nickname', $nickname);
		}else{
			echo json_encode(['errno'=>0,'msg'=>'网络异常!!']);
		}


	}
}