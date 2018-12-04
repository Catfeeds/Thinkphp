<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class VideoLogic extends \Think\Model{
    public function __construct(){
        $this->Video = M('Video');
        $this->App = M('App');
        $this->videoModel = M('Video');
	    $this->Model = M();
	    $this->Category = M('Category');
    }
    private $Video;
    private $App;
	private $videoOrder = array(1=>'creatime',2=>'viewtimes');
	private $Model;
	private $Category;

    public function getimgById($id){
        if($id){
            $cond['id'] = $id;
            $data = $this->Video->field('cover')->where($cond)->select();
            return $data;
        }else{
            return false;
        }
    }
    /**
     * 分页带获取视频列表
     * 参数：
     * @param array $condition : （可选）搜索条件
     * @param int $page : （可选）分页，默认为1 （不需要额外定义列表的数量）
     * @param int $sort: （可选）排序依据，默认为 1:最新，2: 最热
     * @param int $limit : （可选）每次获取的记录条数，默认系统分配（也不可超过系统分配限制数）
     * @return json : list
     **/
    public function getVideos($condition=array(), $page=1, $sort=1, $limit=0){
        $mycond = array();
        if(is_array($condition) && count($condition)>0){
            $mycond = $condition;
        }
        $mycond['status'] = '1';

        if($limit>0 && $limit<C('MOB_REC_PER_PAGE')){
            $curpage = $page.','.$limit;
        }else{
            $curpage = $page.','.C('MOB_REC_PER_PAGE');
        }
        if (array_key_exists($sort, $this->videoOrder)){
            $order = $this->videoOrder[$sort];}
        else{
            $order = $this->videoOrder[1];
        }

	    /*
	      $data =  $this->videoModel
			    ->field('id,uuid,name,cover,videotype,duratime,director,actors,setnum,years,country,category,intro,imgs,provider,isrecommend,viewtimes,filepath,creatime')
			    ->where($mycond)->page($curpage)->order("$order desc")->select();
	    $data = array('list'=>$data);
	    $totalcount = array('totalcount'=>$this->videoModel->where($mycond)->count());
	    $category = array('category'=>$this->videoModel->where($mycond)->field('category,count(category) num')->group('category')->order('num desc')->select());
	    $data = array_merge($totalcount,$data,$category);
	     */

	    $data =  $this->videoModel
		       ->field('id,uuid,name,cover,videotype,duratime,director,actors,setnum,years,country,categoryid,category,intro,imgs,provider,isrecommend,viewtimes,filepath,creatime,cornerurl')
		       ->where($mycond)->page($curpage)->order("$order desc")->select();
	    $data = array('list'=>$data);
	    $totalcount = array('totalcount'=>$this->videoModel->where($mycond)->count());
	    $category = array('category'=>$this->videoModel->where($mycond)->field('category,count(category) num')->group('category')->order('num desc')->select());

	    $videotype = $this->videoModel->where($mycond)->field('videotype,count(videotype) num')->group('videotype')->order('num desc')->select();
	    foreach($videotype as $key=>$value){
			$cate =  $this->Category->where('pid=1')->field('id,name')->select();
		    foreach($cate as $key1=>$value1){
			    if($value['videotype'] == $value1['id']){
				    $videotype[$key]['videotypename'] =$value1['name'];
			    }
		    }
	    }
	    $videotype = array('videotype'=>$videotype);
	    $data = array_merge($totalcount,$data,$category,$videotype);
	    return $data;
    }

	/**
	 * 分页带获取推荐视频列表
	 * 参数：
	 * @param array $condition : （可选）搜索条件
	 * @param int $page : （可选）分页，默认为1 （不需要额外定义列表的数量）
	 * @param int $limit : （可选）每次获取的记录条数，默认系统分配（也不可超过系统分配限制数）
	 * @param int $promotenumber (必含)推荐视频个数
	 * @return json : list
	 **/
	public function getRecomVideos($condition=array(),$promotenumber,$page=1,$limit=0){
		$mycond = array();
		if(is_array($condition) && count($condition)>0){
			$mycond = $condition;
		}
		$mycond['status'] = '1';
		$mycond['DATEDIFF(now(),creatime)'] = array('elt',C('TIME_BUCKET')*30);

		if($limit>0 && $limit<C('MOB_REC_PER_PAGE')){
			$curpage = $page.','.$limit;
		}else{
			$limit = C('MOB_REC_PER_PAGE');
			$curpage = $page.','.C('MOB_REC_PER_PAGE');
		}

		if($promotenumber<$limit){
			//返回数据小于每页数据数量
			return $this->videoModel->where($mycond)->limit(0,$promotenumber)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
		}else if($promotenumber%$limit  == 0){
			return $this->videoModel->where($mycond)->page($curpage)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
		}else{
			$nowpage = ceil($promotenumber/$limit);
			if($page == $nowpage){
				return $this->videoModel->where($mycond)->limit(($page-1)*$limit,$promotenumber%$limit)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
			}else{
				return $this->videoModel->where($mycond)->page($curpage)->order(C('RECOMMEND_DEFAULT_SORT')." desc")->select();
			}
		}

	}

	/**
	 * 详情页面相关推荐
	 * @param $params
	 * @return array data
	 */
	/*
	public function relateRecommend($params){
		$videotype = $params['videotype'];
		$categoryid = $params['categoryid'];
		$data = $this->Model->query("select * from __PREFIX__video where videotype=$videotype and categoryid='$categoryid' and isdel is null and status=1 order by rand() limit 0,".C('RELATED_RECOMMEND'));
		return $data;
	}*/
	public function relateRecommend($id){
		$resource = $this->Video->where('id='.$id.' and isdel is null and status=1')->field('id,videotype,categoryid')->find();
		if($resource){
			$videotype = $resource['videotype'];
			$categoryid = $resource['categoryid'];
			$data = $this->Model->query("select * from __PREFIX__video where videotype='$videotype' and categoryid='$categoryid' and isdel is null and status=1 order by rand() limit 0,".C('RELATED_RECOMMEND'));
			foreach($data as $key=>$value){
				if($resource['id'] == $value['id']){
					unset($data[$key]);
				}
			}
			if(count($data) == C('RELATED_RECOMMEND')){
				unset($data[count($data)-1]);
			}
			$data1 = array();
			foreach($data as $key=>$value){
				$data1[] = $value;
			}
			return $data1;
		}else{
			return array('rst'=>'-1','msg'=>'不存在相关资源');
		}

	}

    function changePlayCount($id = ''){
        $this->videoModel->where(array('id' => $id))->setInc('viewtimes', 1);
        return $this->videoModel->where(array('id' => $id))->getField('viewtimes');
    }
}
