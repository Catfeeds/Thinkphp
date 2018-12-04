<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class ProductLogic extends \Think\Model{

    private $Product;
    private $Userproduct;
    private $User;
	private $Model;
	private $UserAddress;
	private $EProductViewModel;

    public function __construct(){
        $this->Product = M('Product');
        $this->Userproduct = M('User_product');
        $this->User = M('User');
	    $this->Model = M();
	    $this->UserAddress = M('UserAddress');
	    $this->EProductViewModel = D('EProductView');
    }

    public function getProductListByCategoryId($cond=array(),$userid){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $result = $this->Product->join('left join pl_category on pl_category.id = pl_product.categoryid')->field('pl_category.name as categoryname,pl_product.*')->where($mycond)->select();
        foreach($result as $key => $row){
            if($this->Userproduct->where(array('userid'=>$userid,'productid'=>$row['id']))->count() > 0){
                $result[$key]['status'] = 1;
            }else{
                $result[$key]['status'] = 0;
            }
        }
        return $result;
    }

    public function getProductList($userid){
        $result[0] = $this->getProductListByCategoryId(array('categoryid'=>50),$userid);
        $result[1] = $this->getProductListByCategoryId(array('categoryid'=>51),$userid);
        return $result;
    }

	/**
    public function getProductDetail($productid){
        $data = $this->Product->where(array('id'=>$productid))->find();
        return $data;
    }
	 * */

	/*
    public function exchangeProductByScore($userid,$productid){
        $adddata = array(
            'userid' => $userid,
            'productid' => $productid,
            'exchangetimes' => date('Y-m-d H:m:s',time())
        );
        $insertid = $this->Userproduct->add($adddata);
        $result = array();
        if($insertid){
            $totalscore = $this->User->where('uid = '.$userid)->getField('totalscore');
            $productscore = $this->Product->where('id = '.$productid)->getField('score');
            $savedata = array('totalscore'=>$totalscore-$productscore);
            $updateid = $this->User->where('uid = '.$userid)->save($savedata);
            if($updateid){
                $result['msg'] = '001';
                $result['totalscore'] = $totalscore-$productscore;
                $result['userproductid'] = $insertid;
            }else{
                $result['msg'] = '002';
            }
        }
        return $result;
    }
	*/

   //public function searchProductByString($userid,$searchstring){
    //    $con['productname'] = array('like','%'.$searchstring.'%');
     //   $result = $this->getProductListByCategoryId($con,$userid);
     //   return $result;
   // }

	/**
	 *获取商城商品（含搜索）
	 * 参数：
	 * @param parama array
	 * @return array data
	 */
	public function getPointProducts($params,$pages,$rowcount,$number){
		if($rowcount>0 && $rowcount<C('MOB_REC_PER_PAGE')){
			$page = $pages.','.$rowcount;
		}else{
			$rowcount = C('MOB_REC_PER_PAGE');
			$page =  $pages.','.C('MOB_REC_PER_PAGE');
		}

		if($number<$rowcount){
			//返回数据小于每页数据数量
			$data = $this->Product->where($params)
					->field('id productid,productname,score point,imagepath imgurl,content productshortdesc,logopath logourl,categoryid')->limit(0,$number)->order('creatime desc')->select();
		}else if($number%$rowcount  == 0){
			$data = $this->Product->where($params)
					->field('id productid,productname,score point,imagepath imgurl,content productshortdesc,logopath logourl,categoryid')->page($page)->order('creatime desc')->select();
		}else{
			$nowpage = ceil($number/$rowcount);
			if($pages == $nowpage){
				$data = $this->Product->where($params)
						->field('id productid,productname,score point,imagepath imgurl,content productshortdesc,logopath logourl,categoryid')->limit(($pages-1)*$rowcount,$number%$rowcount)->order('creatime desc')->select();
			}else{
				$data = $this->Product->where($params)
						->field('id productid,productname,score point,imagepath imgurl,content productshortdesc,logopath logourl,categoryid')->page($page)->order('creatime desc')->select();
			}
		}
		$totalcount = $this->Product->where($params)->count();

		return array_merge(array('totalcount'=>$totalcount),array('list'=>$data));
	}

	/**
	 * 通过商品ID 查询商品
	 * @param int id
	 * @return array data
	 */
	public function getProductById($id){
		$params['id'] =  $id;
		$params['isonline'] = '0';//上架状态
		$data = $this->Product->where('id='.$id)
				->field('id productid,productname,logopath logourl,score point,imagepath imgurl,categoryid,totalnums,content productshortdesc,price,lastnums amount,contentdesc productlongdesc')->select();
		return $data;
	}

	/**
	 * 积分兑换
	 * 参数：
	 * @param params array
	 * @return null
	 */
	public function exchangePoint($params=array(),$userscore=0,$productnum=0,$productscore=0){
		//更改用户积分
		$this->User->where('uid='.$params['userid'])->save(array('totalscore'=>$userscore-$productscore));
		//更改产品数量
		$this->Product->where('id='.$params['productid'])->save(array('lastnums'=>$productnum-1));
		//保存兑换记录
		$params['exchangetimes'] = date('Y-m-d H:i:s');
		$data = $this->Userproduct->add($params);
		return $data;
	}

	/**
	 *积分兑换记录
	 * @param array params
	 * @return array data
	 */
	public function exchangeRecord($params,$pages,$rowcount){
		//每页返回记录数小于0或者大于系统设置值
		if($rowcount>0 && $rowcount<=C('MOB_REC_PER_PAGE')){
			$curpage = $pages.','.$rowcount;
		}else{
			$curpage = $pages.','.C('MOB_REC_PER_PAGE');
		}

		//$params['_string'] = 'u.productid=p.id';

		$data = $this->EProductViewModel->where($params)->field('id,userid,totalnums,lastnums,productid,status,unix_timestamp(exchangetimes) exchnagetimes,productname,img,price,point,categoryid,phone,username,province,city,district,postcode,address')->order('exchangetimes desc')->page($curpage)->select();

		//$data = $this->Model->table('__USER_PRODUCT__ u,__PRODUCT__ p')->field('u.id,u.productid,p.productname,p.logopath img,p.score point,u.exchangetimes transadate,u.status,p.id productid,u.addressid')
		//		->order('u.exchangetimes desc')->where($params)->page($curpage)->select();

		//$totalcount = $this->Model->table('__USER_PRODUCT__ u,__PRODUCT__ p')->where($params)->count();
		$totalcount = $this->EProductViewModel->where($params)->count();
		return array_merge(array('totalcount'=>$totalcount),array('list'=>$data));
	}

	/*
	 * 热门积分兑换产品
	 * @param int pages:当前分页
	 * @param int rowcount:每页返回记录数
	 * @return array data
	 */
	public function getHotProducts($pages,$rowcount){
		//每页返回记录数小于0或者大于系统设置值
		if($rowcount>0 && $rowcount<=C('MOB_REC_PER_PAGE')){
			$curpage = $pages.','.$rowcount;
		}else{
			$curpage = $pages.','.C('MOB_REC_PER_PAGE');
		}

		$params['_string'] = 'u.productid=p.id and p.isonline=0';

		$data = $this->Model->table('__USER_PRODUCT__ u,__PRODUCT__ p')->field('u.id,u.productid,p.productname,p.logopath img,p.score point,u.exchangetimes transadate,count(u.productid) num')
				->order('num desc')->where($params)->group('u.productid')->page($curpage)->select();

		$totalcount = $this->Model->table('__USER_PRODUCT__ u,__PRODUCT__ p')->field('u.id')->where($params)->group('u.productid')->select();

		return array_merge(array('totalcount'=>count($totalcount)),array('list'=>$data));

	}

	/**
	 * 保存收货地址
	 */
	public function addAddress($conds,$recordid){
		$result = $this->UserAddress->data($conds)->add();
		if($result){
			$result1 =  $this->Userproduct->where(array('id'=>$recordid))->data(array('addressid'=>$result))->save();
			if($result1){
				return true;
			}else{
				return false;
			}
		}else{
			return false;
		}
	}

	/**
	 * 获取收货地址
	*/
	public function getAddress($id){
		$data =  $this->UserAddress->where(array('id'=>$id))->find();
		return $data;
	}

}