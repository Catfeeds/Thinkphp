<?php
namespace Admin\Controller;
use Think\Controller;
class AdminuserController extends Controller {
    public function chgpasswd(){
        if(IS_POST){
            if(I('post.passwordnew') != I('post.passwordnew2')){
                $this->error('两次输入的密码不一样，请重新输入！');
            }else{
                $uid = session('adminid');
                $Admin = M('Admin');
                $admininfo = $Admin->getByUserid($uid);

                if( $admininfo['password'] ==  TransPassUseSalt(I('post.password'), $admininfo['salt'])){
                    $data = array(
                        'password'=>TransPassUseSalt(I('post.passwordnew'), $admininfo['salt'])
                    );
                    $Admin->where(array('uid'=>$uid))->save($data);
                    $this->error('恭喜您，修改密码成功！');
                }else{
                    $this->error('原始密码有误，请重新输入！');
                }
            }
        }else{
            $this->display();
        }

    }
    public function login(){
        if(IS_POST){
            $Admin = M('Admin');
            $admininfo = $Admin->getByUsername(I('post.user'));
            if($admininfo != null){
                if((int)$admininfo['status'] == 1){
                    $this->errmsg  = '账户被冻结无法登陆！';
                }else{
                    $tpass = TransPassUseSalt(I('post.pass'), $admininfo['salt']);
                    if($tpass == $admininfo['password']){
                        session('expire',300);
                        session('name', $admininfo['nickname']);
                        session('username', $admininfo['username']);
                        session('adminid', $admininfo['uid']);
                        session('issuper', (int)$admininfo['issuper']);
	                    session('img',$admininfo['logo']);

                        if($admininfo['privgid']){
                            $Admingroup = M('Admingroup');
                            $privinfo = $Admingroup->getById($admininfo['privgid']);
                            $spriv = explode(',',$privinfo['priv']);
                            $this->initmenu($spriv);
                            session('privs', $spriv);
                        }else if(session('issuper')){
                            $this->initmenu(array());
                        }
                        $this->redirect('Index/index',0);
                    }else{
                        $this->errmsg  = '账户名或密码错误！';
                    }
                }
            }else{
                $this->errmsg  = '你不是管理员吧？';
            }
        }

        $this->display();
    }


    private function initmenu($privarr){
	    //$allmenu = array('1_1_0','1_2_0','1_3_0','1_4_0','1_5_0','2_0','2_1_0','2_2_0','3_0','4_1_0','4_2_0','5_0','6_0','9_1_0','9_2_0','9_3_0','7_0','8_0','21_21_21');

	    $allmenu = array('1_0','1_1_0','1_1_1','1_1_2','1_1_3','1_1_4','1_1_5','1_1_6','1_1_7','1_2_0','1_2_1','1_2_2','1_2_3','1_2_4','1_2_5','1_2_6','1_2_7','1_2_8','1_3_0','1_3_1','1_3_2','1_3_3','1_3_4','1_3_5','1_3_6','1_3_7','1_3_8','2_0','2_1_0','2_1_1','2_1_2','2_1_3','2_1_4','2_1_5','2_1_6','2_1_7','3_0','3_1_0','3_1_1','3_1_2','3_1_3','3_1_4','3_1_5','3_2_0','3_2_1','3_2_2','3_2_3','3_2_4','4_0','4_1_0','4_1_1','4_1_2','4_1_3','4_1_4','4_1_5','4_2_0','4_2_1','4_2_2','4_2_3','4_2_4','4_3_0','4_3_1','4_3_2','4_3_3','4_3_4','5_0','5_1_0','5_1_1','5_1_2','5_1_3','6_0','6_1_0','6_1_1','6_1_2','7_0','7_1_0','7_1_1','7_1_2','7_1_3','8_0','8_1_0','8_1_1','8_1_2','8_1_3','8_1_4','9_0','9_1_0','9_1_1','9_1_2','9_1_3','9_1_4','9_1_5','9_2_0','9_2_1','9_2_2','9_2_3','9_2_4','9_2_5','9_3_0','9_3_1','9_3_2','9_3_3','9_3_4','9_3_5','9_3_6','9_4_0','9_4_1','9_4_2','9_4_3','9_4_4','9_4_5','10_0','10_1_0','10_1_1','10_1_2','10_1_3','10_1_4','10_2_0','10_2_1','10_2_2','11_0','11_1_0','11_1_1','11_1_2','11_1_3','11_1_4','11_2_0','11_2_1','11_2_2','11_2_3','11_2_4','11_3_0','11_3_1','11_3_2','11_3_3','11_3_4','11_4_0','11_4_1','11_4_2','11_4_3','12_0','12_1_0','12_1_1','12_1_2','12_1_3','12_1_4','13_0','13_1_0','13_1_1','13_1_2','13_1_3');


	    $truemenu = array();
        if(session('issuper')){
            $truemenu = $allmenu;
        }else{
            foreach($allmenu as $m){
                if (in_array($m, $privarr)) {
                    $truemenu[] = $m;
                }
            }
        }
        session('menupriv',$truemenu);
        return true;
    }

    public function logout(){
        session('[destroy]');
        $this->redirect('Adminuser/login',0);
    }
}