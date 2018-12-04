<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class UserLoginViewModel extends ViewModel {
	public $viewFields = array(
			'User'=>array('nickname','phone','_type'=>'RIGHT'),
			'Uoperation'=>array('userid','operation','creatime','factory','tags','_on'=>'User.localid=Uoperation.userid and User.belongid=Uoperation.factory'),

	);
}
?>