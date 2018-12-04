<?php
namespace Api\Model;
use Think\Model\ViewModel;
class EProductViewModel extends ViewModel{
	public $viewFields = array(
		'UserProduct' => array('id','userid','productid','addressid','status','exchangetimes','_type'=>'LEFT'),
		'Product' => array('productname','logopath'=>'img','price','totalnums','lastnums','score'=>'point','categoryid','_on'=>'UserProduct.productid=Product.id','_type'=>'LEFT'),
		'UserAddress' => array('phone','username','province','city','district','postcode','address','_on'=>'UserProduct.addressid=UserAddress.id'),
	);
}