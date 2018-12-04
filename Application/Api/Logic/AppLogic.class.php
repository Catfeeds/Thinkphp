<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


class AppLogic extends \Think\Model{
    public function __construct(){
        $this->App = M('App');
	    $this->Video = M('Video');
    }
    private $App;
	private $Video;

    public function getAppList($cond){
		if($cond['apptype'] != 1){
			$data = $this->App->field('cover imgurl,id,name,cornerurl')->where($cond)->order(C('RECOMMEND_DEFAULT_SORT').' desc')->select();
		}else{
			unset($cond['apptype']);
			$data = $this->Video->field('imgs imgurl,id,name,cornerurl')->where($cond)->order(C('RECOMMEND_DEFAULT_SORT').' desc')->select();
		}
        return $data;
    }

    // 单独获取应用或游戏的单条记录
    public function getAppsById($id){
        if($id){
            $data = $this->App->getById($id);
            return $data;
        }else{
            return false;
        }
    }
}