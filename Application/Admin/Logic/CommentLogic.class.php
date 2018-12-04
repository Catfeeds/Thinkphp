<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class CommentLogic extends \Think\Model{
    public function __construct(){
        $this->Comment = M('Comment');
	    $this->Category = M('Category');
    }
    private $Comment;
	private $Category;

    public function getCommentTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Comment->where($mycond)->where('isdel is null')->count();
        return $num;
    }

    public function getCommentList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Comment->where($mycond)->where('isdel is null')->page($pstr)->order('creatime asc')->select();
	    $category = $this->Category->field('id,name')->select();
	    foreach($data as $key=>$value){
		    foreach($category as $key1=>$value1){
			    if($value1['id'] == $value['type']){
				    $data[$key]['typename'] = $value1['name'];
			    }
		    }
	    }
        return $data;
    }

    public function getCommentById($id){
        if($id){
            $data = $this->Comment->getById($id);
            return $data;
        }else{
            return false;
        }
    }
}
