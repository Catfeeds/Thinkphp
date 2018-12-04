<?php
namespace Admin\Controller;
use Think\Controller;
class CustomerController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->CustomerLogic =  D('Customer','Logic');
        $this->Customer =  M('Customer');
    }
    private $Customer ;
    private $CustomerLogic ;


    private function checkPriv($priv){
        $adminid = session('adminid');
        if(empty($adminid)) $this->redirect('Adminuser/login',0);
        if(!session('issuper')){
            if(!empty($priv) && !in_array($priv,session('privs'))) $this->error('您没有此权限!.');
        }
        $this->assign('adname', session('name'));
    }


    public function customermgr(){
        $this->checkPriv('12_1_0');
        $p = getCurPage();
        $res = $this->CustomerLogic->getCustomerList(array(),$p);
        $this->data = $res;
        $this->total = $this->CustomerLogic->getCustomerTotal();
        $show = constructAdminPage($this->total);
        $this->assign('page',$show);
        $this->display();
    }

	public function customerdatamgr(){
		$this->checkPriv('12_1_0');
		$p = getCurPage();
		$res = $this->CustomerLogic->getCustomerList(array(),$p);
		$this->data = $res;
		$this->total = $this->CustomerLogic->getCustomerTotal();
		$show = constructAdminPage($this->total);
		$this->assign('page',$show);
		$this->display();
	}

    public function addcustomer(){
        $this->checkPriv('12_1_1');
        $this->assign('act','add');
        $this->assign('errcode','0');
        if(I('post.act')=='add'){
            $newdata = array();
            $newdata['name'] = I('post.name');
            $newdata['address'] = I('post.address');
            $newdata['contact'] = I('post.contact');
            $newdata['contactinfo'] = I('post.contactinfo');
	        $newdata['cloudname'] = I('post.cloudname');
	        $newdata['cloudip'] = I('post.cloudip');
	        $newdata['clouddns'] = I('post.clouddns');
	        $newdata['loginname'] = I('post.loginname');
	      //  $newdata['loginpwd'] = I('post.loginpwd');

	        $newdata['uniqueindex'] =  I('post.uniqueindex','','int');

	        $newdata['creatime'] = date('Y-m-d H:i:s');
            $ret = $this->Customer->add($newdata);
            if($ret){
                $this->redirect('Customer/customermgr');
            }else{
                $this->error('插入数据错误');
            }
        }else{
            $this->display("Customer/customeredit");
        }
    }

    public function editcustomer(){
        $this->checkPriv('12_1_2');
        $this->assign('act','edit');
        $this->assign('errcode','0');
        if(I('post.act')=='edit'){
            $newdata = array();
            $id = I('post.id','','int');
	        $newdata['name'] = I('post.name');
	        $newdata['address'] = I('post.address');
	        $newdata['contact'] = I('post.contact');
	        $newdata['contactinfo'] = I('post.contactinfo');
	        $newdata['cloudname'] = I('post.cloudname');
	        $newdata['cloudip'] = I('post.cloudip');
	        $newdata['clouddns'] = I('post.clouddns');
	        $newdata['loginname'] = I('post.loginname');
	      //  $newdata['loginpwd'] = I('post.loginpwd');

	        $newdata['uniqueindex'] =  I('post.uniqueindex','','int');

            $ret = $this->Customer->where(array('id'=>$id))->save($newdata);
            if($ret !==false){
                $this->redirect('Customer/customermgr');
            }else{
                $this->assign('errcode','1');  // 修改失败
                $this->display('Customer/customeredit');
            }
        }else{
            $id = I('get.id','','int');
            if($id !== false){
                $this->data = $this->Customer->getById($id);
                $this->display("Customer/customeredit");
            }else{
                $this->error('没有该记录');
            }
        }
    }

	public function viewcustomer(){
		$id = I('post.id','','int');

			$data= $this->Customer->getById($id);
			$this->ajaxReturn($data);

	}




	/**
	 * 重置客户初始密码
	 */
	public function resetCustomer(){
		$this->checkPriv('12_1_4');
		$id =  I('get.id','','int');
		if($id){
			$Admin = M('Admin');
			$admin = $Admin->getByUsername(I('get.username'));
			if($admin){
				$tpass = TransPassUseSalt(C('INITIAL_PASSWORD'), $admin['salt']);
				$result = $Admin->where(array('username'=>$admin['username']))->save(array('password'=>$tpass));
				if($result){
					$from = I('server.HTTP_REFERER');
					redirect($from);
				}else{
					$this->error('操作失败，请稍后再试');
				}
			}else{
				$this->error('没有找到相关管理用户');
			}
		}else{
			$this->error('该记录不存在');
		}
	}

    public function delcustomer(){
        $this->checkPriv('12_1_3');
        $id = I('get.id','','int');
        if($id){
            $data['isdel']= date("Y-m-d H:i:s");;
            $this->Customer->where('id='.$id)->save($data);
            $from = I('server.HTTP_REFERER');
            redirect($from);
        }else{
            $this->error('该记录不存在');
        }
    }


    public function dayreport(){
        $this->display();
    }
    public function monthreport(){
        $this->display();
    }
    public function quarterreport(){
        $this->display();
    }
    public function dataupdate(){
        $this->display();
    }
}