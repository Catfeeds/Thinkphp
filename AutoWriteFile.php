<?php
date_default_timezone_set('PRC');//'Asia/Shanghai'
header("Content-type: text/html; charset=utf-8");
$PRIFIX =  'pl_';//定义表前缀
$PAGENUM = 3000; //定义每次读取写入数据的个数
$fileprifix = 'xxx';



$dirname = date('Ym',strtotime('-1 day'));
$dirurl = dirname(__FILE__);
/*
clearstatcache();
if(!is_dir($dirurl.'/'.$dirname)) {
	mkdir($dirurl.'/'.$dirname);
}*/
	$pdo = new PDO("mysql:host=127.0.0.1;dbname=plife","root","mysql");
	$shezhi = $pdo->exec("SET names utf8");

	$date_from = date('Y-m-d H:i:s',strtotime(date('Y-m-d',strtotime('-1 day'))));
	$date_to = date('Y-m-d H:i:s',strtotime(date('Y-m-d')));

	//提取用户操作(boperation)
	$totalcountsql = $pdo->query("select count(*) from $PRIFIX"."boperation where creatime >= '$date_from' and creatime < '$date_to'  ");//exec() fetch()
	$totalcount = $totalcountsql->fetch();
	//$filename = date('Ymd',strtotime('-1 day'));
	$filename = date('Ymd');
	//$file = fopen($dirurl.'/'.$dirname."/$fileprifix$filename.txt","a+");
	$file = fopen($dirurl."/$fileprifix$filename.txt","a+");
	$str = null;
	$resulttrue = false;
	if($totalcount[0] >0){
		if($totalcount[0] <= $PAGENUM){
			$result = $pdo->query("select userid,operation,creatime,objid,subobjid,factory,linktype,public,tags,isdel,videonum,videolength from $PRIFIX"."boperation where creatime >= '$date_from' and creatime < '$date_to' limit 0,$totalcount[0] ");
			$row = $result->fetchAll(PDO::FETCH_NUM);
			foreach($row as $key=>$value){
				$str = $str.implode('||',$value)."\r\n";
			}
			$resulttrue = true;
			$str = substr_replace($str,'',-2);
			fwrite($file,$str);
			$str = null;
		}else{
			$num = ceil($totalcount[0]/$PAGENUM);
			for($i=1;$i<$num;$i++){
				$start = ($i-1)*$PAGENUM;
				$result = $pdo->query("select userid,operation,creatime,objid,subobjid,factory,linktype,public,tags,isdel,videonum,videolength from $PRIFIX"."boperation where creatime >= '$date_from' and creatime < '$date_to' limit $start,$PAGENUM");
				$row = $result->fetchAll(PDO::FETCH_NUM);
				foreach($row as $key=>$value){
					$str = $str.implode('||',$value)."\r\n";
				}
				fwrite($file,$str);
				$str = null;
			}
			$start = ($num-1)*$PAGENUM;
			$surplus = $totalcount[0]-$start;
			$result = $pdo->query("select userid,operation,creatime,objid,subobjid,factory,linktype,public,tags,isdel,videonum,videolength from $PRIFIX"."boperation where creatime >= '$date_from' and creatime < '$date_to' limit $start,$surplus");
			$row = $result->fetchAll(PDO::FETCH_NUM);
			foreach($row as $key=>$value){
				$str = $str.implode('||',$value)."\r\n";
			}
			$resulttrue = true;
			$str = substr_replace($str,'',-2);
			fwrite($file,$str);
			$str = null;

		}
	}

	//设置表分隔
	//$str = "____________________\r\n";

	//提取用户信息
	$totalcountsql = $pdo->query("select count(*) from $PRIFIX"."user where creatime >= '$date_from' and creatime < '$date_to'  ");//exec() fetch()
	$totalcount = $totalcountsql->fetch();

	if($totalcount[0] > 0){
		if($resulttrue){
			$str = "\r\n";
		}
		if($totalcount[0] <= $PAGENUM){
			$result = $pdo->query("select logo,status,lastlogindate,lastloginip,username,nickname,password,totalscore,pointtoexpire,expiredpoint,email,phone,sex,creatime,birthday,salt,shownickname,showphone,belongid,localid from $PRIFIX"."user where creatime >= '$date_from' and creatime < '$date_to' limit 0,$totalcount[0] ");
			$row = $result->fetchAll(PDO::FETCH_NUM);
			foreach($row as $key=>$value){
				$str = $str.implode('||',$value)."\r\n";
			}
			$str = substr_replace($str,'',-2);
			fwrite($file,$str);
			$str = null;
		}else{
			$num = ceil($totalcount[0]/$PAGENUM);
			for($i=1;$i<$num;$i++){
				$start = ($i-1)*$PAGENUM;
				$result = $pdo->query("select logo,status,lastlogindate,lastloginip,username,nickname,password,totalscore,pointtoexpire,expiredpoint,email,phone,sex,creatime,birthday,salt,shownickname,showphone,belongid,localid from $PRIFIX"."user where creatime >= '$date_from' and creatime < '$date_to' limit $start,$PAGENUM");
				$row = $result->fetchAll(PDO::FETCH_NUM);
				foreach($row as $key=>$value){
					$str = $str.implode('||',$value)."\r\n";
				}
				fwrite($file,$str);
				$str = null;
			}
			$start = ($num-1)*$PAGENUM;
			$surplus = $totalcount[0]-$start;
			$result = $pdo->query("select logo,status,lastlogindate,lastloginip,username,nickname,password,totalscore,pointtoexpire,expiredpoint,email,phone,sex,creatime,birthday,salt,shownickname,showphone,belongid,localid from $PRIFIX"."user where creatime >= '$date_from' and creatime < '$date_to' limit $start,$surplus");
			$row = $result->fetchAll(PDO::FETCH_NUM);
			foreach($row as $key=>$value){
				$str = $str.implode('||',$value)."\r\n";
			}
			$str = substr_replace($str,'',-2);
			fwrite($file,$str);
			$str = null;
		}
	}//else{
		//$str = substr_replace($str,'',-2);
		//fwrite($file,$str);
	//}

//提取用户操作（uoperation）
$totalcountsql = $pdo->query("select count(*) from $PRIFIX"."uoperation where creatime >= '$date_from' and creatime < '$date_to'  ");//exec() fetch()
$totalcount = $totalcountsql->fetch();

if($totalcount[0] > 0){
	if($resulttrue){
		$str = "\r\n";
	}
	if($totalcount[0] <= $PAGENUM){
		$result = $pdo->query("select userid,operation,creatime,factory,tags from $PRIFIX"."uoperation where creatime >= '$date_from' and creatime < '$date_to' limit 0,$totalcount[0] ");
		$row = $result->fetchAll(PDO::FETCH_NUM);
		foreach($row as $key=>$value){
			$str = $str.implode('||',$value)."\r\n";
		}
		$str = substr_replace($str,'',-2);
		fwrite($file,$str);
		$str = null;
	}else{
		$num = ceil($totalcount[0]/$PAGENUM);
		for($i=1;$i<$num;$i++){
			$start = ($i-1)*$PAGENUM;
			$result = $pdo->query("select userid,operation,creatime,factory,tags from $PRIFIX"."uoperation where creatime >= '$date_from' and creatime < '$date_to' limit $start,$PAGENUM");
			$row = $result->fetchAll(PDO::FETCH_NUM);
			foreach($row as $key=>$value){
				$str = $str.implode('||',$value)."\r\n";
			}
			fwrite($file,$str);
			$str = null;
		}
		$start = ($num-1)*$PAGENUM;
		$surplus = $totalcount[0]-$start;
		$result = $pdo->query("select userid,operation,creatime,factory,tags from $PRIFIX"."uoperation where creatime >= '$date_from' and creatime < '$date_to' limit $start,$surplus");
		$row = $result->fetchAll(PDO::FETCH_NUM);
		foreach($row as $key=>$value){
			$str = $str.implode('||',$value)."\r\n";
		}
		$str = substr_replace($str,'',-2);
		fwrite($file,$str);
		$str = null;
	}
}



fclose($file);
$pdo =  null;


