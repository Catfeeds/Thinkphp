<?php
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');//'Asia/Shanghai'
$PRIFIX =  'pl_';//定义表前缀
$caller = 'egame_hm_pinet';
$signKey = 'd496ab763b48815da5370560f5d03422';
$apptype = '70';
$provider = '97';//渠道来源
$url = 'http://hm.play.cn/api/v1/data_sync/get_data';
$CATEGORY = array('22'=>'益智休闲','23'=>'动作冒险','24'=>'棋牌麻将','25'=>'设计飞行','26'=>'模拟经营','27'=>'角色扮演','28'=>'体育竞技','29'=>'酷跑赛车','30'=>'策略塔防','31'=>'其他游戏');

$datefrom = '20000101000000';
//$datefrom = date('YmdHis',strtotime(date('Ymd',strtotime("-1 days")))+1);
$dateto = date('YmdHis');
//$dateto = date('YmdHis',strtotime(date('Ymd')));
$sequence =  date('YmdHis');
$pagesize = 100;
//$localurl = '/Users/lmjean/sites/2222/';
$localurl = '/var/pinet/game/';//本地保存文件路径

//$localurl = '/mnt/usb/packages/';//本地保存文件路径

	$list =  mosaicHeader();//第一次获取获取数据，来获得需要更新的总数目

	if ($list['state']['code'] == 200) {
		$result = json_decode(base64_decode($list['data']), true);

		$totalcount = $result['total'];
		$dataarr = $result['list'];


		if($totalcount == 0 || count($dataarr) <1){
			exit;
		}

		$page = ceil($totalcount/$pagesize);

		$pdo = new PDO("mysql:host=localhost;dbname=plife", "root", "mysql");
		$shezhi = $pdo->exec('set names utf8');

		$writefile = fopen("./aigame.sh","w+");//打开文件准备写入shell
		$filestr = "#!/bin/sh\n";

		for($i=1;$i<=$page;$i++){
			$list = mosaicHeader($i);//分页获取数据
			$result = json_decode(base64_decode($list['data']), true);
			$dataarr = $result['list'];


			if(count($dataarr) >0){
				foreach ($dataarr as $key => $value) {
					$value['game_name'] = str_replace("（","(",$value['game_name']);
					$value['game_name'] = str_replace("）",")",$value['game_name']);
					$value['game_name'] = str_replace("：",":",$value['game_name']);
					$value['game_name'] = str_replace(" ","",$value['game_name']);

					if($value['game_file']['channel_id'] == '20032414'){
						$filderurl = $localurl . $value['game_name'];
						if (!is_dir($filderurl)) {
							mkdir($filderurl);
						}

						$imgs = array();
						$cover = null;
						foreach ($value['game_images'] as $key1 => $value1) {
							if ($value1['image_type'] == '200') {
								$filestr .= "rm -rf '$localurl". $value['game_name'] ."/icon.jpg'\n";
								$filestr .= "wget -c -O '$localurl" . $value['game_name'] . "/icon.jpg' \"" . $value1['image_url'] . "\"\n";
							}
							if ($value1['image_type'] == '400') {
								$cover = "packages/".$value['game_name']."/cover.jpg";
								$filestr .= "rm -rf '$localurl". $value['game_name'] ."/cover.jpg'\n";
								$filestr .= "wget -c -O '$localurl" . $value['game_name'] . "/cover.jpg' \"" . $value1['image_url'] . "\"\n";
							}
							if ($value1['image_type'] == '203') {
								$imgsnum = count($imgs);
								$imgs[] = "packages/" . $value['game_name'] . "/imgs" . $imgsnum . ".jpg";
								$filestr .= "rm -rf '$localurl". $value['game_name'] ."/imgs$imgsnum.jpg'\n";
								$filestr .= "wget -c -O '$localurl" . $value['game_name'] . "/imgs$imgsnum.jpg" ."' \"" . $value1['image_url'] . "\"\n";
							}
						}
						$fileapkname = 'gamefile.apk';
						$filestr .= "rm -rf '$localurl". $value['game_name'] ."/$fileapkname'\n";
						$filestr .= "wget -c -O '$localurl" . $value['game_name'] . "/$fileapkname' \"" . $value['game_file']['file_url'] . "\"\n";


						if (count($imgs) > 5) {
							$imgs = array_slice($imgs, 0, 5);
						}
						$imgs = json_encode($imgs);
						$imgs = $pdo->quote($imgs);
						//$imgs = str_replace('"','\"',json_encode($imgs));
						//$imgs = str_replace('\\','\\',$imgs);
						if($value['game_tag'] == 10002){
							$tags = $CATEGORY['30'];
							$tagsid = 30;
						}else if($value['game_tag'] == 1003){
							$tags = $CATEGORY['27'];
							$tagsid = 27;
						}else if($value['game_tag'] == 1004){
							$tags = $CATEGORY['25'];
							$tagsid = 25;
						}else if($value['game_tag'] == 1005){
							$tags = $CATEGORY['23'];
							$tagsid = 23;
						}else if($value['game_tag'] == 1006){
							$tags = $CATEGORY['24'];
							$tagsid = 24;
						}else if($value['game_tag'] == 1007){
							$tags = $CATEGORY['22'];
							$tagsid = 22;
						}else if($value['game_tag'] == 1009){
							$tags = $CATEGORY['28'];
							$tagsid = 28;
						}else if($value['game_tag'] == 1011){
							$tags = $CATEGORY['31'];
							$tagsid = 31;
						}else if($value['game_tag'] == 1013){
							$tags = $CATEGORY['26'];
							$tagsid = 26;
						}else if($value['game_tag'] == 1014){
							$tags = $CATEGORY['29'];
							$tagsid = 29;
						}

						$size = round($value['game_file']['file_size']/(1024*1024)).'M';
						$filepath = "packages/".$value['game_name']."/".$fileapkname;
						$icon = "packages/".$value['game_name']."/icon.jpg";
						//$cover = "packages/".$value['game_name']."/cover.jpg";
						if($value['online_status'] == 1){
							$status = 1;
						}else{
							$status = 2;
						}

						$search = $pdo->query("select id from $PRIFIX"."app where name=\"".$value['game_name']."\"");
						$result = $search->fetch(PDO::FETCH_NUM);
						if($result){
							$str = "updatetxt='派生活',apptype='$apptype',icon='$icon',pkg='".$value['game_file']['package_name']."',imgs=$imgs,version='".$value['game_file']['version_name']."',versioncode='".$value['game_file']['version_code']
							."',size='".$size."',intro='".$value['introduction']."',tags='$tags',filepath='$filepath',cover='$cover',tagsid='$tagsid',provider='$provider',
							status='$status' where name='".$value['game_name']."'";
							$insertsql = $pdo->exec("update $PRIFIX" ."app set $str");
						}else{
							$str = "\"".$value['game_name']."\",$apptype,\"$icon\",\"派生活\",\"".$value['game_file']['package_name']."\",".$imgs.",\"".$value['game_file']['version_name']."\",\"".$value['game_file']['version_code']
									."\",\"".$size."\",\"".$value['introduction']."\",\"$tags\",\"$filepath\",\"$cover\",$tagsid,$provider,$status";
							$insertsql = $pdo->exec("insert into $PRIFIX" . "app(name,apptype,icon,updatetxt,pkg,imgs,version,versioncode,size,intro,tags,filepath,cover,tagsid,provider,status) values($str)");//exec() fetch()
						}
					}

				}
				fwrite($writefile,$filestr);
				$filestr = null; //将写入文件的字符串置空
			}

		}

		fclose($writefile);
		$pdo =  null;

		$logfile = fopen('./aigamessynchlog.log','a+');
		$logstring = date('Y-m-d H:i:s').' 自动同步'."    成功\r\n";
		fwrite($logfile,$logstring);
		fclose($logfile);

	}


/*
 * 发送curl请求数据
 */
function http_post_json($url, $jsonStr){
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonStr);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
					'Content-Type: application/json; charset=utf-8',
				//'Content-Length: ' . strlen($jsonStr)
			)
	);
	$response = curl_exec($ch);
	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
	return array($httpCode, $response);
}

/*
 * 拼接请求消息头
 */
function mosaicHeader($page=1){
	global $url,$datefrom,$dateto,$sequence,$pagesize,$caller,$signKey;
	$data = array();
	$data['date_from'] = $datefrom;
	$data['date_to'] = $dateto;
	$data['sync_type'] = 1;
	$data['sync_entity'] = 'game';
	//$data['channel_id'] = null;
	$data['page_size'] = $pagesize;
	$data['page_num'] = $page;
	ksort($data);
	$content = $caller;
	foreach ($data as $key => $value) {
		$content .= $key . '=' . $value;
	}
	$content .= $signKey;
	$sign = strtolower(md5($content));


	$content1['sequence'] = $sequence;
	$content1['client'] = array('caller' => $caller, 'ex' => null);
	$content1['data'] = $data;
	$content1['sign'] = $sign;
	$content1['encrypt'] = 'base64';
	$jsonStr = json_encode($content1);

	list($returnCode, $returnContent) = http_post_json($url, $jsonStr);
	$list = json_decode($returnContent, true);
	return $list;
}



function unicodeDecode($data){
	function replace_unicode_escape_sequence($match) {
		return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
	}

	$rs = preg_replace_callback('/\\\\u([0-9a-f]{4})/i', 'replace_unicode_escape_sequence', $data);

	return $rs;
}








