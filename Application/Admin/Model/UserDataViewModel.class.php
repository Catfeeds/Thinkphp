<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class UserDataViewModel extends ViewModel {
	public $viewFields = array(
			'User'=>array('uid','status','lastlogindate','lastloginip','username','nickname','password','totalscore','pointtoexpire','expiredpoint','email','phone','sex','logo','creatime','birthday','salt','shownickname','showphone','belongid','localid','_type'=>'LEFT'),
			'Uoperation'=>array('userid','operation','creatime'=>'ucreatime','factory','tags','_on'=>'User.localid=Uoperation.userid and User.belongid=Uoperation.factory'),

	);
}
?>