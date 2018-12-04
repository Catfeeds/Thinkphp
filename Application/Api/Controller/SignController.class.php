<?php
namespace Api\Controller;
use Admin\Logic\SignLogic;
use Think\Controller;
header("Content-Type: text/html; charset=utf-8");

class SignController extends Controller {

    private $SignLogic;
    private $UserSign;
    private $User;
	private $UserLogic;

    public function __construct(){
        parent::__construct();
        $this->SignLogic = D('Sign','Logic');
        $this->UserSign = M('User_sign');
        $this->User = M('User');
	    $this->UserLogic = D('User','Logic');
    }

    public function SignIn(){
        $userid = I('post.userid',null,'int');
	    if(!isset($userid)){
		    $data['rst'] = '-1';
		    $data['msg'] = '参数传递错误';
		    $this->ajaxReturn($data);
	    }
//        $userid = 1001;
        $re = $this->SignLogic->getSignRecordByUid(array('userid'=>$userid));
        $date1 = date('Y-m-d',time());
        $date2 = date('Y-m-d',strtotime($re[0]['signtimes']));
        $stiffdays = floor((strtotime($date1)-strtotime($date2))/3600/24);

        $data = array();
        if($stiffdays < 1){
	        $lianxuday =$re[0]['days'];
	        $rst = '-1';
            $msg = "今天已签到，请明天再来(本月连续签到".$lianxuday."天)";
        }else{
            $sign_data = array(
                'userid'=>$userid,
                'signtimes'=>date('Y-m-d H-i-s',time())
            );

	        $todaynum =  date('d');
            if($re == null || $stiffdays > 1 || $todaynum == '01'){
                $days = 1;
                $score = C('sign_score_one');
                $msg = '本月连续签到1天';
            }elseif($stiffdays == 1){
                $days = $re[0]['days']+1;
	            /*
                if($days > 0 and $days <= 5){
                    $score = C('sign_score_one');
                }elseif($days > 5 and $days <= 10){
                    $score = C('sign_score_five');
                }elseif($days > 10){
                    $score = C('sign_score_ten');
                }*/
	            $score = C('sign_score_one');
                $msg = '恭喜您，本月连续签到'.$days.'天，';
	            $mday =  date('Y-m-01');
	            $maxday = date('d',strtotime("$mday +1 month -1 day "));
	            if($days == 3){
					$this->SignLogic->saveSingTask($userid,C('sign_3'),C('sign_score_3'));
	            }else if($days == 7){
		            $this->SignLogic->saveSingTask($userid,C('sign_7'),C('sign_score_7'));
	            }else if($days == 15){
		            $this->SignLogic->saveSingTask($userid,C('sign_15'),C('sign_score_15'));
	            }else if($days == $maxday){
		            $this->SignLogic->saveSingTask($userid,C('sign_max'),C('sign_score_max'));
	            }
            }
	        $rst = '0';
            $sign_data['score'] = $score;
            $sign_data['days'] = $days;
            $signid = $this->UserSign->add($sign_data);

            if($signid > 0){
                $ad = $this->User->where('uid='.$userid)->find();
                $this->User->where('uid='.$userid)->save(array('totalscore'=>$ad['totalscore']+$score));
                $data['id'] = $signid;
            }else{
                $msg = 'error';
            }
            $data['score'] = $score;
            $data['totalscore'] = $ad['totalscore']+$score;
        }
	    $data['rst'] = $rst;
        $data['msg'] = $msg;
	    //$this->UserLogic->saveuOperation($userid,4);//保存签到记录
        $this->ajaxReturn($data);
    }

    public function getSignRecord(){
        $userid = I('post.userid');
//        $userid = 1001;
        $data = $this->SignLogic->getSignRecord($userid);
        if($data){
            $data['rst'] = 0;
            $data['msg'] = 'Get info successes';
        }else{
            $data['rst'] = -1;
            $data['msg'] = 'Get info failed';
        }
        $this->ajaxReturn($data);
    }

	/*//我的积分
	public function myCredit(){
		$userid = I('post.userid',null,'int');
		if($userid){
			$ad = $this->User->where('uid='.$userid)->find();
			if($ad){
				$data['totalscore'] = $ad['totalscore'];
				$data['tomorrowscore'] = 5;//明日可获取积分 ，站定写死，带任务完善
			}else{
				$data['rst'] = -1;
				$data['msg'] = '系统异常，请稍后尝试';
			}
		}else{
			$data['rst'] = -1;
			$data['msg'] = '传递参数有误';
		}
		$this->ajaxReturn($data);
	}*/

}