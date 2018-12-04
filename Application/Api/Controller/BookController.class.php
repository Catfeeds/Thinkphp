<?php
namespace Api\Controller;
use Think\Controller;
class BookController extends Controller
{
    public function __construct(){
        parent::__construct();
        $this->BookLogic =  D('Book','Logic');
        $this->readrecordLogic =  D('Readrecord','Logic');
        $this->categoryLogic =  D('Category','Logic');
        $this->Book=  M('Book');
        $this->Readrecord=  M('Readrecord');
        $this->Bookparam=  M('Bookparam');
    }

    private $BookLogic ;
    private $readrecordLogic ;
    private $Book ;
    private $Readrecord ;
    private $Bookparam;

    /**
     * 图书获取接口
     * param: 父类id: pid:0
     * @return json : data
     */
	/*
    public function GetBookList()
    {
        $where['pid'] =I('post.pid')?I('post.pid'):0;
        $data = $this->bookLogic->getBookList($where);
        $this->ajaxReturn($data);
    }
	*/

    /**
     * 用户浏览图书记录接口
     * @return json : data
     */
    public function GetReadRecordList()
    {
        $userid = I('post.userid');
//        $userid = '1001';
        $where['pl_readrecord.uid'] = $userid;
        $where['pl_bookparam.pid'] = '0';
        $data = $this->BookLogic->getReadRecordList($where);
        $this->ajaxReturn($data);
    }

	/**
	 * 图书查询（含搜索）
	 * @param int pages:当前页数（默认为1）
	 * @param int rowcount:每页返回记录数
	 * @param string keywords :搜素关键字
	 * @param int id:图书ID
	 * @param int categoryid:所属分类ID
	 * @return json :data
	 */
	public function getBookList(){
		$params = array();
		$pages = I('post.pages','','int')?I('post.pages','','int'):1;

		$id =  I('post.id',null,'int');
		if(isset($id)){
			$params['id'] = $id;
		}

		$keywords = trim(I('post.keywords',null,'string'));
		if(isset($keywords)){
			$params['name'] = array('like','%'.$keywords.'%');
		}

		$rowcount = I('post.rowcount','','int')?I('post.rowcount','','int'):C('MOB_REC_PER_PAGE');

		$categoryid = I('post.categoryid',null,'int');
		if(isset($categoryid)){
			$params['categoryid'] = $categoryid;
		}

		$data = $this->BookLogic->getBookList($params,$pages,$rowcount);
		$this->ajaxReturn($data);
	}
}