
<?php
//header("Content-type:application/vnd.ms-excel");
//header("Content-Disposition:attachment;filename=Export_test.xls");
//$tab="\t"; $br="\n";
//$head="编号".$tab."备注".$br;
////输出内容如下：
//echo $head.$br;
//echo  "test321318312".$tab;
//echo  "string1";
//echo  $br;
//
//echo  "330181199006061234".$tab;  //直接输出会被Excel识别为数字类型
//echo  "number";
//echo  $br;
//
//echo  "=\"330181199006061234\"".$tab;  //原样输出需要处理
//echo  "string2";
//echo  $br;
//?>
<?php
header("Content-type:text/html;charset=utf-8");
$url = 'http://127.0.0.1/plife/index.php/Api/Login';
//$url = 'http://wx.pinet.cc/plife2/index.php/Api/User/modifyuserinfo';
//print_r(urlencode($url));die;
//print_r(urlencode('http%253A%252F%252Flocalhost%252F%257Emorganzhao%252Fmobile%252Findex.php%253Fm%253Ddefault%2526c%253Duser%2526a%253Dorder_detail%2526order_id%253D217'));die;
//$requestUrl = 'http://www.baidu.com:80/s?ie=utf-8&bs=php+post+%E8%AE%BF%E9%97%AE%E5%9C%B0%E5%9D%80&f=3&rsv_bp=1&wd=php+parse_url&rsv_sug3=5&rsv_sug1=5&rsv_sug4=176&oq=php+parse&rsp=1&rsv_sug2=0&inputT=5095&=ss&iii=777.html';
//$urlArr=parse_url($requestUrl);
//parse_str($urlArr['query'], $strArr);
//$strArr['iii']      = 'iii';
//$strArr['jjj']      = 'jjj';
//$strArr['kkk']      = 'kkk';
//$url    = $urlArr['scheme'].'://';
//
//if($urlArr['port']!==null)                  // port
//{
//	$url    .= ':'.$urlArr['port'];
//}
//$url    .= $urlArr['path'];
//$url    .= '?'.http_build_query($strArr);
//$t1=microtime(true);
//$a=array();//你的数组
//$res=array();//结果
//$i=0;
//for($i=0;$i<5;$i++){
//	$res[]=$i;
//}
//$t2=microtime(true);
//$t3=$t2-$t1;
//$cmd=stripslashes('shutdown -l');
//exec($cmd,$out);
//print_r($cmd);die;
//print_r($urlArr);die;
//highlight_file(date('Y-m-d H:i:s',strtotime('-1 year',time())));die;

//$post_data['uuid']       = '2237d945-68cc-471a-8564-6d1356ff594a';
//$post_data['uuid']       = '0010990d-df65-43d4-83a8-a6b61114ff35';
//$post_data['cate']       = '动作';
//$post_data['area']       = '全部';


//$post_data['type'] = '3';
//$post_data['userid']       = '1002';
//$post_data['operation']       = '2';
//$post_data['objid']       = '25';
//$post_data['subobjid']       = '11';
//$post_data['videotime']  =   '22222';
//$post_data['promotenumber']     = '3';
//$post_data['producttypeid'] = '50' ;
//$post_data['userid'] = '1001';
//$post_data['number'] = '1000';
//$post_data['pages'] = '1';
//$post_data['category'] = '纸牌';
$post_data['phone'] = '15955114247';
//$post_data['password'] = '1111qqqqq';
//$post_data['userid'] = '1001';
//$post_data['pages'] = '2';
//$post_data['rowcount'] = '2';
//$post_data['categoryid'] = '33';
//$post_data['rowcount'] = '2';
//$post_data['videocategory'] = '1';
//$post_data['type'] = '1';
//$post_data['position'] = '4';
///$post_data['objid'] = '25';
//$post_data['type'] = 'sf';
//$post_data['videotime'] = '2015-05-01';
//$post_data['id'] ='7';
$post_data['password'] = '123456qqq';
//$post_data['password'] = '66798';
//$post_data['operation']       = '1';
//$post_data['type']       = '1';
//$post_data['productid']       = '1011';
//$post_data['keywords']        = '苹果';
//$post_data['token']       = 'TUy5SOPiSvU4qb4zfLHGFBaEGb0gW+1sp6uaa9eyPtD6y9oiNB/uhfFtYd4XEqHfcttvfsnZIQeHNTedpQw/ZehTSn4cFWXstwNfqWaC7Totfz1Dxzvx/ilX2PcDsdKppTU1iqZMQHI3959LVXpwOFqo7AaXVqj/oPKu/lbdCz8=';
//$post_data['email']       = 'morgan.zhao@pinet.co';
//$post_data['mobile']       = '13656227964';
////$post_data['f']      = '1';
//$post_data['t'] = '2';
//$post_data['y'] = '2015';
//$post_data['id'] = '218';


//$post_data['cate'] ='动作';
//$post_data['ys'] ='1';
//$post_data = array();
$res = request_post($url, $post_data);
print_r($res);
function request_post($url = '', $post_data = array()) {
	if (empty($url) || empty($post_data)) {
		return false;
	}

	$o = "";
	foreach ( $post_data as $k => $v ) 
	{ 
		$o.= "$k=" . urlencode( $v ). "&" ;
	}
	$post_data = substr($o,0,-1);

	$postUrl = $url;
	$curlPost = $post_data;
	$ch = curl_init();//初始化curl
	curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
	curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
	curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
	curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
	$data = curl_exec($ch);//运行curl
	curl_close($ch);

	return $data;
}

