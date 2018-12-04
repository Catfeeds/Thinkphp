<?php
/**
 * Created by PhpStorm.
 * User: IceLight
 * Date: 15/11/20
 * Time: 上午9:02
 */

namespace Api\Logic;


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
        $num = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_bookparam.isdel is null')->count();
        return $num;
    }

   /* public function getBookList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }
        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where($mycond)->where('pl_book.isdel is null')->page($pstr)->order('pl_bookparam.id asc')->select();
        return $data;
    }
   */

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
            $data = $this->Book->join('pl_bookparam on pl_book.id = pl_bookparam.bookid')->where('id is '.$id.'')->where('pl_book.isdel is null')->find();
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

    public function getReadRecordList($cond=array(), $p){
        $mycond = array();
        if(is_array($cond) && count($cond)>0){
            $mycond = $cond;
        }

        $pstr = $p.','.C('ADMIN_REC_PER_PAGE');
        $data = $this->Readrecord->field('pl_readrecord.id,pl_readrecord.chapter,pl_readrecord.uid,pl_bookparam.name as bookname')
		        ->join('pl_bookparam on pl_readrecord.bookid = pl_bookparam.bookid')
            ->where($mycond)->where('pl_readrecord.isdel is null')->page($pstr)->order('pl_bookparam.id asc')->select();
        return $data;
    }


	/**
	 *图书查询（含搜素）
	 * 带完善
	 */
	/*
	public function getBookList(){
		$params['_string'] = 'bk.id = bkp.bookid and bk.isdel is null';
		//获取所有上架书籍
		$data = $this->Book->table('__BOOK__ bk,__BOOKPARAM__ bkp')->field('bk.id bkid,bkp.name bkname,bkp.img bkimg,bkp.id,bkp.chaptercontent bkshortdesc')
				->where($params)->group('bk.id')->select();

		foreach($data as $key=>$values){
			$data[$key]['drama'] = $this->Bookparam->field('id dramaid,chapteraddress url,chapter,chaptertitle')
					->where('pid='.$values['id'])->order('id')->select();
		}
		return $data;
	}*/

	/**
	 *图书查询（含搜素）
	 * @param int pages：当前页数
	 * @param int rowcount :每页返回记录数
	 * @return array data
	 */
	public function getBookList($params,$pages,$rowcount){
		if($rowcount>0 && $rowcount<C('MOB_REC_PER_PAGE')){
			$page = $pages.','.$rowcount;
		}else{
			$page = $pages.','.C('MOB_REC_PER_PAGE');
		}

		$data = $this->Book->where($params)->order('creatime desc')->page($page)->select();
		foreach ($data as $key=>$value) {
			$data[$key]['drama'] = $this->Bookparam->field('id dramaid,chaptertitle,chapter,chapteraddress url')
					->where('bookid='.$value['id'])->order('sort')->select();
		}
		$totalcount = $this->Book->where($params)->count();
		return array_merge(array('totalcount'=>$totalcount),array('list'=>$data));


	}

}
