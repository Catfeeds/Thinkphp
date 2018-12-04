<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 16/05/07
 * Time: 上午11:02
 */
namespace Admin\Model;
use Think\Model\ViewModel;
class ClouduserViewModel extends ViewModel {
	public $viewFields = array(
		'User'=>array('*','_type'=>'LEFT'),
		'Customer'=>array('id'=>'facid','name'=>'facname','_on'=>'Customer.uniqueindex=User.belongid'),
	
	); 
}
?>