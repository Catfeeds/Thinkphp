<?php
return array(
	//'配置项'=>'配置值'
    'RES_TYPE_VIDEO' => 1,  // 全局视频类型资源
    'RES_TYPE_APP' => 2,    // 全局应用类型资源
    'RES_TYPE_BOOK' => 3,   // 全局书类型资源
    'MOB_REC_PER_PAGE' => 10,
	'TIME_BUCKET' => 6,//时间段
	'DATE_FORM' => '2014-05-11',//开始时间段
	'DATE_TO' => '2020-03-23',//结束时间段
	'POP_NUM' => '10000',//游戏人气最高数
	'RECOMMEND_DEFAULT_SORT' => 'creatime',//推荐默认排序
	'RELATED_RECOMMEND' => 7,//详情页面的相关推荐数目(游戏)

	'APP_VERSION'   => 'xxx.xxx.xxx',//app当前版本
	'APP_URL'       => 'http://xxx.xxx.xxx',//APP路径
	'APP_UPDATE'    => '1',//是否强制更新，0为不强制更新，1为必须更新
	'MALL_URL'      => 'http://gw.pinet.co', //商城路径
	'WIFI_URL'      => 'http://download.pinet.co/indexbak.html',//一键上网地址

		'HM_PLAY' => array(
        'url' => 'http://hm.play.cn/api/v1/data_sync/get_data',
        'caller' => 'egame_hm_pinet',
        'signKey' => 'd496ab763b48815da5370560f5d03422',
        'encrypt' => 'base64'
    ),
    'MMS_SERVER'=> 'http://192.168.11.110/plife/videos',
    'GetSysSetting' => array(
        'adinterval' =>10, //广告刷新时间
       // array('showusername' =>0) ,//0，不显示用户名；1，显示用户名
       // array('showphoneNo' =>1) , //0，不显示手机号；1，显示手机号
       'showad' =>1       //0，视频播放不显示广告；1，视频播放显示广告
    ),
    'video_columns'=>array(
        array(
            'name'=>'movie',
            'label'=>'电影',
            'icon'=>'',
            'banner'=>''
        ),
        array(
            'name'=>'drama',
            'label'=>'电视剧',
            'icon'=>'',
            'banner'=>''
        )
    ),
    'app_columns'=>array(
        array(
            'name'=>'relax',
            'label'=>'益智休闲',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/relax.png'
        ),
        array(
            'name'=>'checkpoint',
            'label'=>'动作冒险',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/checkpoint.png'
        ),
        array(
            'name'=>'chess',
            'label'=>'棋牌麻将',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/chess.png'
        ),
        array(
            'name'=>'shot',
            'label'=>'射击飞行',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/shot.png'
        ),
        array(
            'name'=>'manage',
            'label'=>'模拟经营',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/manage.png'
        ),
        array(
            'name'=>'role',
            'label'=>'角色扮演',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/role.png'
        ),
        array(
            'name'=>'sports',
            'label'=>'体育竞技',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/sports.png'
        ),
        array(
            'name'=>'racing',
            'label'=>'跑酷赛车',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/racing.png'
        ),
        array(
            'name'=>'td',
            'label'=>'策略塔防',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/td.png'
        ),
        array(
            'name'=>'other',
            'label'=>'其他游戏',
            'icon'=>'',
            'banner'=>'http://api.pinet.co/application/static/img/other.png'
        )
    ),
    'sign_score_3' => 7,
    'sign_score_7' => 10,
    'sign_score_15' => 15,
	'sign_score_max'=> 20,
	'task_score_uploadimg' => 20,
	'task_score_name' =>10,
	'task_score_birthday' =>10,
	'task_socre_fenxiang' =>10,
	'sign_score_one' => 5,

	'sign_3' => '1048',
	'sign_7' => '1049',
	'sign_15'=> '1050',
	'sign_max' => '1051',
	'task_uploadimg' => '1052',
	'task_name' => '1053',
	'task_birthday' => '1054',

	'task_rc' => '46',
	'task_xs' => '47',
	'task_yy' => '48',
	'task_qd' => '1047',
	'task_fx' => '1046',

	'BANNER_TOP' => 'BANNER_TOP',
	'BANNER_MIDDLE' =>'BANNER_MIDDLE',
	'BANNER_BOTTOM' => 'BANNER_BOTTOM',
	'BANNER_SCREEN' => 'BANNER_SCREEN',
);