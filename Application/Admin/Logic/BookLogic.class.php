<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Admin\Logic;


class BookLogic extends \Think\Model{
    public function __construct(){
        $this->Book = M('Book');
        $this->Bookparam = M('Bookparam');
        $this->Readrecord = M('Readrecord');
    }
    private $Book;

    public function getBookTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
	    $mycond['_string'] = 'isdel is null';
        //$num = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_bookparam.isdel is null')->count();
       // $num = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_book.isdel is null')->count();
	    $num = $this->Book->where($mycond)->count();
        return $num;
    }

	/*
    public function getBookList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_book.isdel is null and pid = 0')->page($pstr)->order('pl_bookparam.id asc')->select();
        //var_dump($data);
        return $data;
    }*/
	//获取图书列表
	public function getBookList($cond=array(),$p){
		$mycond = array();
		if(is_array($cond) && count($cond)>0){
			$mycond = $cond;
		}
		$mycond['_string'] = 'isdel is null';
		$pages = $p.','.C('ADMIN_REC_PER_PAGE');
		$data = $this->Book->where($mycond)->page($pages)->order('creatime desc')->select();
		return $data;
	}

	//获取图书详情
    public function getBookById($id){
        if($id){
            $data = $this->Book->getById($id);
            return $data;
        }else{
            return false;
        }
    }


    public function getBookParamById($id){
        if($id){
            $data = $this->Book->where('id='.$id)->find();
            return $data;
        }else{
            return false;
        }
    }


    public function getBookParamByBookId($id){
        if($id){
            $data = $this->Bookparam->where('bookid = '.$id.' and pid =0')->find();
            return $data;
        }else{
            return false;
        }
    }

    public function getChapterTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_bookparam.isdel is null and pid != 0')->count();
        return $num;
    }

    public function getChapterList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_bookparam.isdel is null and pid != 0')->page($pstr)->order('pl_bookparam.sort asc')->select();
        return $data;
    }

    public function getChapterTotalById($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
	    $mycond['_string'] = 'isdel is null';

	    //$num = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_bookparam.isdel is null and pid != 0')->count();
	    $num = $this->Bookparam->where($mycond)->count();
        return $num;
    }

    public function getChapterListById($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
	    $mycond['_string'] = 'isdel is null';
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
       // $data = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_bookparam.isdel is null and pid != 0')->page($pstr)->order('pl_bookparam.sort asc')->select();
        $data = $this->Bookparam->where($mycond)->page($pstr)->order('sort')->select();
	    return $data;
    }

    public function getChapterParamById($id){
        if($id){
            $data = $this->Bookparam->where('id = '.$id.'')->find();
            return $data;
        }else{
            return false;
        }
    }

    public function getRecordTotal($cond = array()){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $num = $this->Readrecord->join('pl_bookparam on pl_readrecord.paramid = pl_bookparam.id')
            ->join('pl_book on pl_readrecord.bookid = pl_book.id')
            ->where($mycond)->where('pl_book.isdel is null')->order('pl_bookparam.id asc')->count();
        return $num;
    }

    public function getRecordList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Readrecord->join('pl_bookparam on pl_readrecord.paramid = pl_bookparam.id')
            ->join('pl_book on pl_readrecord.bookid = pl_book.id')
            ->where($mycond)->where('pl_book.isdel is null')->page($pstr)->order('pl_bookparam.id asc')->select();
        return $data;
    }

    public function getRecordById($id){
        if($id){
            $data = $this->Readrecord->where('paramid = '.$id.'')->find();
            return $data;
        }else{
            return false;
        }
    }

}
