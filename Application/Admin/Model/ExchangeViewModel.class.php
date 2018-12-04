<?php
namespace Admin\Model;
use Think\Model\ViewModel;
class ExchangeViewModel extends ViewModel {
	public $viewFields = array(
			'UserProduct'=>array('id','userid','productid','exchangetimes','status','addressid','_type'=>'LEFT','_as'=>'p'),
			'UserAddress'=>array('username','phone','province','city','district','postcode','address','_on'=>'p.addressid=UserAddress.id'),
			'Product' =>array('productname','_on'=>'p.productid=Product.id'),
	);
}
?>