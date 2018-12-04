<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class BannerLogic extends \Think\Model{
    public function __construct(){
        $this->Banner = M('Banner');
	    $this->Model = M();
    }
    private $Banner;
	private $Model;

    public function getBannerList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Banner->where($mycond)->where('isdel is null')->page($pstr)->order('sort asc')->select();
        return $data;
    }

    public function getBanners($condition=array(), $num = 5){
        $condition['status'] = '1';
        return $this->bannerModel->limit($num)->where($condition)->select();
    }

	public function getBanner($position=9,$type=1){
		$weeknum = date('w');

		if($position ==  9){
			$data1 = $this->Banner->query("select bb.id,bb.name,bb.type,bb.img,bb.content,bb.resourcetype,bb.url,bb.model,bb.param,bb.character,bb.platform,aa.position,aa.title from __BANNER__ bb
				join (select id,position,title from __POSITION__ where position='BANNER_TOP' and isdel is null) as aa on bb.pid=aa.id where bb.isdel is null and bb.status=1 and bb.platform='$type' and bb.start_time<now() and bb.end_time>now() group by rand() limit 3");
			$data2 = $this->Banner->query("select bb.id,bb.name,bb.type,bb.img,bb.content,bb.resourcetype,bb.url,bb.model,bb.param,bb.character,bb.platform,aa.position,aa.title from __BANNER__ bb
				join (select id,position,title from __POSITION__ where position='BANNER_MIDDLE' and isdel is null) as aa on bb.pid=aa.id where bb.isdel is null and bb.status=1 and bb.platform='$type' and bb.start_time<now() and bb.end_time>now() group by rand() limit 3");
			$data3 = $this->Banner->query("select bb.id,bb.name,bb.type,bb.img,bb.content,bb.resourcetype,bb.url,bb.model,bb.param,bb.character,bb.platform,aa.position,aa.title from __BANNER__ bb
				join (select id,position,title from __POSITION__ where position='BANNER_BOTTOM' and isdel is null) as aa on bb.pid=aa.id where bb.isdel is null and bb.status=1 and bb.platform='$type' and bb.start_time<now() and bb.end_time>now() group by rand() limit 3");
			$data4 = $this->Banner->query("select bb.id,bb.name,bb.type,bb.img,bb.content,bb.resourcetype,bb.url,bb.model,bb.param,bb.character,bb.platform,aa.position,aa.title from __BANNER__ bb
				join (select id,position,title from __POSITION__ where position='BANNER_SCREEN' and isdel is null) as aa on bb.pid=aa.id where bb.isdel is null and bb.status=1 and bb.platform='$type' and bb.start_time<now() and bb.end_time>now()  limit $weeknum,1");

			return array_merge(array('BANNER_TOP'=>$data1),array('BANNER_MIDDLE'=>$data2),array('BANNER_BOTTOM'=>$data3),array('BANNER_SCREEN'=>$data4));

		}else{
			if($position == 1){
				$position = C('BANNER_TOP');
			}else if($position == 2){
				$position = C('BANNER_MIDDLE');
			}else if($position == 3){
				$position = C('BANNER_BOTTOM');
			}else{
				$position = C('BANNER_SCREEN');
			}
			if($position != C('BANNER_SCREEN')){
				$data = $this->Model->query("select bb.id,bb.name,bb.type,bb.img,bb.content,bb.resourcetype,bb.url,bb.model,bb.param,bb.character,bb.platform,aa.position,aa.title from __BANNER__ bb
				join (select id,position,title from __POSITION__ where position='$position' and isdel is null) as aa on bb.pid=aa.id where bb.isdel is null and bb.status=1 and bb.platform='$type' and bb.start_time<now() and bb.end_time>now() group by rand() limit 3");
			}else{
				$data = $this->Banner->query("select bb.id,bb.name,bb.type,bb.img,bb.content,bb.resourcetype,bb.url,bb.model,bb.param,bb.character,bb.platform,aa.position,aa.title from __BANNER__ bb
				join (select id,position,title from __POSITION__ where position='$position' and isdel is null) as aa on bb.pid=aa.id where bb.isdel is null and bb.status=1 and bb.platform='$type' and bb.start_time<now() and bb.end_time>now()  limit $weeknum,1");

			}

			return $data;
		}

	}
}
