<?php
namespace Vendor\yunpian;
header("Content-Type:text/html;charset=utf-8");
 class YunPian{
	private $ch;
	private $apikey = '18ac0757d41974c55555c3bbb9c56360';
	private $test = '【派生活】欢迎您注册免费的派生活。您的验证码为：#code# （为了保证账号安全，请勿向他人泄露。如非本人操作，请忽略)';


	public function __construct(){
		$this->ch = curl_init();
		/* 设置验证方式 */
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
		/* 设置返回结果为流 */
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
		/* 设置超时时间*/
		curl_setopt($this->ch, CURLOPT_TIMEOUT, 10);
		/* 设置通信方式 */
		curl_setopt($this->ch, CURLOPT_POST, 1);
		curl_setopt($this->ch, CURLOPT_SSL_VERIFYPEER, false);
	}

	public function sendSMS($code,$mobile){
		/*// 取得用户信息
		$json_data = $this->get_user($this->ch,$this->apikey);
		$array = json_decode($json_data,true);
		echo '<pre>';print_r($array);
		*/
		// 发送短信
		$test = str_replace('#code#',$code,$this->test);
		$data=array('text'=>$test,'apikey'=>$this->apikey,'mobile'=>$mobile);
		$json_data = $this->send($this->ch,$data);
		$array = json_decode($json_data,true);
		return  $array;
		//echo '<pre>';print_r($test);
		//echo '<pre>';print_r($array);

		/*// 发送模板短信
		// 需要对value进行编码
		$data=array('tpl_id'=>'1','tpl_value'=>('#code#').'='.urlencode('1234').'&'.urlencode('#company#').'='.urlencode('欢乐行'),'apikey'=>$apikey,'mobile'=>$mobile);
		print_r ($data);
		$json_data = tpl_send($ch,$data);
		$array = json_decode($json_data,true);
		echo '<pre>';print_r($array);
		// 发送语音验证码
		$data=array('code'=>'9876','apikey'=>$apikey,'mobile'=>$mobile);
		$json_data =voice_send($ch,$data);
		$array = json_decode($json_data,true);
		echo '<pre>';print_r($array);
		*/
		curl_close($this->ch);
	}

	//获得账户
	function get_user($ch,$apikey){
		curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/user/get.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(array('apikey' => $apikey)));
		return curl_exec($ch);
	}
	function send($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/single_send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}
	function tpl_send($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'https://sms.yunpian.com/v2/sms/tpl_single_send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}
	function voice_send($ch,$data){
		curl_setopt ($ch, CURLOPT_URL, 'http://voice.yunpian.com/v2/voice/send.json');
		curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
		return curl_exec($ch);
	}

}


