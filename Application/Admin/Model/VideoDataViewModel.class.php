<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class VideoDataViewModel extends ViewModel {
	public $viewFields = array(
			'Boperation'=>array('objid','factory','operation','creatime','linktype','_as'=>'p'),
			'Video'=>array('id','name','category','categoryid','videotype','_on'=>'p.objid=Video.id'),
			'Category'=>array('name'=>'categoryname','_on'=>'Video.videotype=Category.id')
	);
}
?>