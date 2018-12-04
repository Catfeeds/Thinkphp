<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class AppCateDateViewModel extends ViewModel {
	public $viewFields = array(
			'Boperation'=>array('objid','factory','operation','creatime','linktype','_type'=>'LEFT','_as'=>'p'),
			'App'=>array('id','tags','tagsid','_on'=>'p.objid=App.id')
	);
}
?>