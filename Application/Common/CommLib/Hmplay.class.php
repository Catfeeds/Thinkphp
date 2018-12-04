<?php
namespace CommLib;

class Hmplay {

	private $url;
	private $caller;
	private $signKey;
	private $encrypt;

	function __construct($url = '', $caller = '', $signKey = '', $encrypt = 'base64'){
		$this->url = $url;	
		$this->caller = $caller;	
		$this->signKey = $signKey;	
		$this->encrypt = $encrypt;	
		if(empty($this->url) || empty($this->caller)  || empty($this->signKey) ){
			exit('参数不全，请提供url，caller，signKey三个参数');
		}
		
	}

	function sign($params = array()){
		$strParam = $this->formatRequestParams($params);		
		return md5($strParam);
	}


	function formatRequestParams($params){
		if(empty($params)){
			exit('请配置请求参数');
		}
		ksort($params);
		$str = '';
		foreach($params as $key => $value){
			$str .= $key.'='.$value;
		}
		return $this->caller .$str. $this->signKey;
	} 

	function http_request($params = '', $method = 'post'){
		$curl = curl_init();
		if(stripos($this->url,"https://")!==FALSE){
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($curl, CURLOPT_SSLVERSION, 1);
		}
		curl_setopt($curl, CURLOPT_URL, $this->url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1 );
		if ($method == 'post') {
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($params)));
			curl_setopt($curl, CURLOPT_POST, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
		}
		$result = curl_exec($curl);
		//$error = curl_error($curl);
		curl_close($curl);
		return $result;
	}
}
