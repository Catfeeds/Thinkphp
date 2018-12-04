<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class ProductLogic extends \Think\Model{

    private $Product;
    private $Userproduct;
	private $Exchange;

    public function __construct(){
        $this->Product = M('Product');
        $this->Userproduct = M('User_product');
	    $this->Exchange = D('ExchangeView');
    }

	/*
    public function getProductList(){
        $data = $this->Product->join('left join pl_category on pl_category.id = pl_product.categoryid')->field('pl_category.name as categoryname,pl_product.*')->select();
        return $data;
    }
	*/

	//分页获取积分商品
	public function getProductList($p){
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$data = $this->Product->table('__PRODUCT__ p,__CATEGORY__ c')->field('p.id,c.name,p.productname,p.score,p.price,p.totalnums,p.lastnums,p.isonline')
				->where('p.categoryid = c.id')->order('creatime desc')->page($pages)->select();
		return $data;
	}

    public function getProductTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Product->where($mycond)->count();
        return $num;
    }

    public function getUserProductList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Userproduct->join('left join pl_admin on pl_admin.uid = pl_user_product.userid')->join('left join pl_product on pl_product.id = pl_user_product.productid')->field('pl_admin.username,pl_product.productname,pl_user_product.*')->where($mycond)->page($pstr)->select();
        return $data;
    }

    public function getUserProductTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Userproduct->where($mycond)->count();
        return $num;
    }

	public function exchangeList($p){
		$pages =  $p.','.C('ADMIN_REC_PER_PAGE');
		$data = $this->Exchange->page($pages)->order('exchangetimes desc')->select();
		return $data;

	}

	public function exchangeTotal(){
		$total = $this->Exchange->count();
		return $total;
	}

	public function changeUserProduce($id){
		$data = $this->Userproduct->where(array('id'=>$id))->save(array('status'=>1));
		return $data;
	}

	public function productkeywords($p,$keywords){
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$wh['p.productname'] =  array('like',"%$keywords%");
		$wh['_string'] = 'p.categoryid = c.id';
		$to['productname'] = array('like',"%$keywords%");
		$total =  $this->Product->where($to)->count();
		$data = $this->Product->table('__PRODUCT__ p,__CATEGORY__ c')->field('p.id,c.name,p.productname,p.score,p.price,p.totalnums,p.lastnums,p.isonline')
				->where($wh)->where($wh)->order('creatime desc')->page($pages)->select();
		return array('list'=>$data,'total'=>$total);
	}


}