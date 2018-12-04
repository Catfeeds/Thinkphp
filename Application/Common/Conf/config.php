<?php
return array(
	//'配置项'=>'配置值'
		'DB_TYPE'   => 'mysql',
		'DB_HOST'   => 'localhost',
		'DB_NAME'   => 'plife',
		'DB_USER'   => 'root',
		'DB_PWD'    => 'mysql',
		'DB_PORT'   => 3306,
		'DB_PREFIX' => 'pl_',
		'URL_CASE_INSENSITIVE' =>true,
		//'SHOW_PAGE_TRACE' =>true,
		'SITE_NAME'	=>'Pinet',
		'ITEM_IMG_MAXSIZE' => 4194304,
		'ITEM_FILE_SIZE' => 52428800,
		'ITEM_IMG_PATH' => 'Upload/Img/',
		'ITEM_FILE_PATH' => 'Upload/File/',
		'ITEM_PRODUCT_PATH' => 'Upload/Product/',
		'ADMIN_REC_PER_PAGE'=>50,
		'AUTOLOAD_NAMESPACE' => array(
				'CommLib' => COMMON_PATH.'CommLib',

		),

		'LOAD_EXT_CONFIG'		=> 'route',

		'ADMINISTRATION_SECTION' =>'派尔科技',

		//'RESOURCE_URL'      => 'http://wx.pinet.cc/plife2/',//资源路径
		'RESOURCE_URL'      => WEB_ROOT,//资源路径

		'CUSTOMER_UNIQUE_INDEX' =>'unit1',
		'RES_TYPE_VIDEO' => 1,  // 全局视频类型资源
		'RES_TYPE_APP' => 2,    // 全局应用类型资源
		'RES_TYPE_BOOK' => 3,   // 全局书类型资源

		'AREA_CATEGORY' => '57',  //区域主类ID
		'GAMES_CATEGORY'    => '70',//游戏主类ID
		'APP_CATEGORY'      => '71',//应用主类ID
		'MOVIE_CATEGORY'    => '65',//电影主类ID
		'BOOK_CATEGORY'     => '32',//图书主类ID
		'TV_CATEGORY'       => '66',//电视剧主类ID
	//'TMPL_EXCEPTION_FILE'   =>  './Application/Admin/View/Public/404.html',
	//'ERROR_PAGE'=>'./Application/Admin/View/Public/404.html',
);

