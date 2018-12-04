<?php
date_default_timezone_set('PRC');//'Asia/Shanghai'
header("Content-type: text/html; charset=utf-8");
$PRIFIX =  'pl_';//定义表前缀
$INSERTNUM = 3000;//每次插入数据库的数目
$dirprifix = 'datastatistics';



//$dirname =  date('Ym',strtotime('-1 day'));
$dirname =  date('Ym');
//$filename = date('Ymd',strtotime('-1 day'));
$filename = date('Ymd');

$filearr = get_files('./xxx');

//$fileurl = './201607/20160705.txt';//"./$dirname/$filename.txt";//dirname(__FILE__);

$pdo = new PDO("mysql:host=127.0.0.1;dbname=plife","root","mysql");
$shezhi = $pdo->exec("SET names utf8");
$logfile = fopen('./autoreadfilelog.log','a+');

foreach($filearr as $key=>$value){
	clearstatcache();
	if(!file_exists($value)) {
		exit;
	}

	$file =  fopen($value,'r');
	$num = 0;//boperation计数
	$num1 = 0;//user计数
	$num2 = 0;
	$str = null;//存bopertion
	$str1 = null;//存user
	$str2 = null;
	while(!feof($file)){
		$arr =  explode('||',trim(fgets($file)));
		if(count($arr) == 12){
			$num +=1;
			$str =  $str."('$arr[0]'";
			for($i=1;$i<count($arr);$i++){
				$str = $str.",'$arr[$i]'";
			}
			$str =  $str.'),';

			if($num == $INSERTNUM){
				$str = substr_replace($str,'',-1);
				$insertsql = $pdo->exec("insert into $PRIFIX"."boperation(userid,operation,creatime,objid,subobjid,factory,linktype,public,tags,isdel,videonum,videolength) values$str");//exec() fetch()
				$str = null;
				$num = 0;
			}
		}else if(count($arr) == 20){
			$num1 +=1;
			$str1 =  $str1."('$arr[0]'";
			for($i=1;$i<count($arr);$i++){
				$str1 = $str1.",'$arr[$i]'";
			}
			$str1 =  $str1.'),';
			if($num1 == $INSERTNUM){
				$str1 = substr_replace($str1,'',-1);
				$insertsql = $pdo->exec("insert into $PRIFIX"."user(logo,status,lastlogindate,lastloginip,username,nickname,password,totalscore,pointtoexpire,expiredpoint,email,phone,sex,creatime,birthday,salt,shownickname,showphone,belongid,localid) values$str1");//exec() fetch()
				$str1 = null;
				$num1 = 0;
			}
		}else{
			$num2 +=1;
			$str2 =  $str2."('$arr[0]'";
			for($i=1;$i<count($arr);$i++){
				$str2 = $str2.",'$arr[$i]'";
			}
			$str2 =  $str2.'),';
			if($num2 == $INSERTNUM){
				$str2 = substr_replace($str2,'',-1);
				$insertsql = $pdo->exec("insert into $PRIFIX"."uoperation(userid,operation,creatime,factory,tags) values$str2");//exec() fetch()
				$str2 = null;
				$num2 = 0;
			}
		}

	}

	if(strlen($str)>1){
		$str = substr_replace($str,'',-1);
		$insertsql = $pdo->exec("insert into $PRIFIX"."boperation(userid,operation,creatime,objid,subobjid,factory,linktype,public,tags,isdel,videonum,videolength) values$str");//exec() fetch()
		$str = null;
		$num = 0;
	}
	if(strlen($str1)>1){
		$str1 = substr_replace($str1,'',-1);
		$insertsql = $pdo->exec("insert into $PRIFIX"."user(logo,status,lastlogindate,lastloginip,username,nickname,password,totalscore,pointtoexpire,expiredpoint,email,phone,sex,creatime,birthday,salt,shownickname,showphone,belongid,localid) values$str1");//exec() fetch()
		$str1 = null;
		$num1 = 0;
	}
	if(strlen($str2)>1){
		$str2 = substr_replace($str2,'',-1);
		$insertsql = $pdo->exec("insert into $PRIFIX"."uoperation(userid,operation,creatime,factory,tags) values$str2");//exec() fetch()
		$str2 = null;
		$num2 = 0;
	}

	fclose($file);

	$datafilename = strripos($value,'/')+1;
	$logstring = date('Y-m-d H:i:s').' 读取    '.substr($value,$datafilename)."    成功\r\n";
	fwrite($logfile,$logstring);

}

$pdo =  null;
fwrite($logfile,"\r\n");
fclose($logfile);



function get_files($dir){
	$files = array();
	if(is_dir($dir)){
		if($dh = opendir($dir)){
			while (($file = readdir($dh)) !== false) {
				if(!($file == '.' || $file == '..')){
					$file = $dir.'/'.$file;
					if(is_dir($file) && $file != './.' && $file != './..'){
						$files = array_merge($files, get_files($file));
					} else if(is_file($file)){
						//$fullpath = $_SERVER['HTTP_REFERER'];
						//$fullpath = str_replace(basename($fullpath),"",$fullpath);
						$fullpath = substr($file,2);
						$files[] = './'.$fullpath;
					}
				}
			}
		}
	}
	return $files;
}