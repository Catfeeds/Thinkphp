<?php
	namespace Api\Controller;
	use Think\Controller;

	class ETouchController extends Controller{
		public function connToInternet(){
			$data = array();
			$data['rst'] = '0';
			$data['msg'] = '现在只是测试，待实现';
			$this->ajaxReturn($data);
		}

		public function test(){
			echo('<script src="http://ajax.aspnetcdn.com/ajax/jQuery/jquery-1.8.0.js" type="text/javascript"></script>');
			echo('<script type="text/javascript" src="http://protal.ikuai8.com/sdk/ikAuth.js"></script>');


			echo("<script type='text/javascript'>");
			echo("
				$(document).ready(function(){
					ikLogin();
					$.post('http://localhost/plife/index.php/Api/ETouch/test1',{name:'卧室'},function(data,status){
						document.write('dd');
						alert(status+'11111'+data);
					});


				});
			");

			echo("</script>");
		}

		public function test1(){
			$a = I('post.name');
			var_dump($a);
			var_dump('xx');
			exit;
		}

		public function test2(){
			ignore_user_abort();
			set_time_limit(0);
			$inte = 3;
			for($i=1;$i<10;$i++) {
				echo(date('H:i:s'));
				sleep($inte);
			}
		}

	}