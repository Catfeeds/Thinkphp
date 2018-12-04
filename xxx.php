<?php
header("Content-type: text/html; charset=utf-8");
date_default_timezone_set('PRC');//'Asia/Shanghai'

echo(exec('wget -c -O "/Users/lmjean/sites/2222/开心消消乐/imgs1.jpg" "http://cdn.hm.play.cn/f/pkg/ph/view/000/002/833/c747b5c7h2b3b393.jpg" 2>&1',$arr,$result));
echo($result);
var_dump($arr);


