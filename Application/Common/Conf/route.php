<?php
/**
 * 网站路由配置
 * @package YiCms
 * @author YiCms
 **/
return array(
	/*路由设置*/
	'URL_ROUTER_ON'   => true,              // 开启路由
	'URL_ROUTE_RULES' => array(             // 定义路由规则
		'Api/PromotedVideos' => 'Api/Video/videos',      // 视频信息 推荐
		'Api/GetVideoInfo'   => 'Api/Video/videos',      // 视频收缩
		'Api/PromotedApps'   => 'Api/Games/games',       // 游戏推荐
		'Api/GetApps'        => 'Api/Games/games',       // 游戏APP搜索
		'Api/GetPopApps'     => 'Api/Games/getPopApps',  // 游戏人气最高
		//'Api/GetLastMonthHotApps'   => 'Api/Games/getMonthPopApps',         //上月精品游戏推荐
		'Api/GetDNSAddress'  => 'Api/IndexFunction/getDNSAddress',          //DNS接口服务
		'Api/SaveOperation'  => 'Api/Record/savebOperation',               //用户操作记录
		'Api/SearchOperation'=> 'Api/Record/searchOperation',               //查询用户操作
		'Api/SignIn'         => 'Api/Sign/SignIn',                          //用户签到
		'Api/GetTasks'       => 'Api/Task/GetTasks',                        //获取任务列表
		'Api/GetUserPoint'   => 'Api/Product/GetUserPoint',                 //获取用户积分
		'Api/GetPointProducts'  =>  'Api/Product/getPointProducts',         //获取商城产品（含搜索）
		'Api/GetProductdetail'  =>  'Api/Product/GetProductDetail',         //获取商品详情
		'Api/ExchangePoint'  => 'Api/Product/exchangePoint',                //积分兑换
		'Api/GetModelDesc'   => 'Api/IndexFunction/GetModelDesc',           //首页模块文字信息获取
		'Api/GetBookList'    => 'Api/Book/getBookList',                     //图书查询（含搜素）
		'Api/CheckVersion'   => 'Api/IndexFunction/checkVersion',           //版本检查
		'Api/GetNotices'     => 'Api/IndexFunction/getNotices',             //系统消息
		'Api/CheckNotices'   => 'Api/IndexFunction/checkNotices',           //系统消息检查
		'Api/CollectComments'=> 'Api/IndexFunction/collectComments',        //意见收集
		'Api/SaveAppComment' => 'Api/Games/saveAppComment',                 //游戏评论保存
		'Api/GetAppComments' => 'Api/Games/getAppComments',                 //游戏评论获取
		'Api/GetSlideShow'   => 'Api/Ads/getappimgs',                       //幻灯片信息
		'Api/GetImageUrl'    => 'Api/Ads/getimg',                           //图片信息
		'Api/GetKeywords'    => 'Api/Record/getKeywords',                   //关键字查询（获取记录）
		'Api/UpdateUserInfo' => 'Api/User/updateUserInfo',                  //修改账户信息
		'Api/GetUserInfo'    => 'Api/User/getUserInfo',                     //账户信息获取
		'Api/ChangePassword' => 'Api/User/changePassword',                  //修改密码
		'Api/GetArticleList' => 'Api/Post/getArticleList',                  //文章列表接口
		'Api/GetSysSetting'  => 'Api/Config/getSysSetting',                 //获取系统设定
		'Api/GetStatement'   => 'Api/IndexFunction/getStatement',           //免责和声明
		'Api/SendVerifyCode' => 'Api/User/sendVerifyCode',                  //发送短信验证码
		'Api/VerifyCode'     => 'Api/User/verifyCode',                      //验证码验证
		'Api/RegistUser'     => 'Api/User/register',                        //用户注册
		'Api/Login'          => 'Api/User/login',                           //用户登入
		'Api/ConnToInternet' => 'Api/ETouch/connToInternet',                //一键上网接口
		'Api/RelatedApps'    => 'Api/Games/relateRecommend',                //游戏APP相关推荐
		'Api/RelatedVideos'  => 'Api/Video/relateRecommend',                //视频相关推荐
		'Api/RandomApps'     => 'Api/Games/randomApps',                //随机应用
		'Api/DownloadStatistic'  => 'Api/Games/downloadStatistic',          //下载统计接口（周榜/月榜）
		'Api/GetPointTrans'  => 'Api/Product/exchangeRecord',               //产品兑换记录
		'Api/GetHotProducts' => 'Api/Product/getHotProducts',               //热门积分商品兑换记录
		'Api/SavePrivateSetting' => 'Api/User/updateUserInfo',              //隐私设置接口
		'Api/UploadUserImg'  => 'Api/User/uploadImg',                       //上传个人图像
		'Api/ExitLogin'      => 'Api/User/exitLogin',                       //退出登入
		'Api/GetMall'        => 'Api/IndexFunction/getMall',                //购物商城
		'Api/LinkWiFi'       => 'Api/IndexFunction/linkWiFi',               //一键上网
		'Api/CheckAppVersion'   => 'Api/Games/checkVersion',                //检查应用更新
		'Api/GameLiveRoom'   => 'Api/Video/gameLiveRoom',                   //获取游戏直播房间详情信息
		'Api/GameLiveType'   => 'Api/Video/gameLiveType',                   //获取游戏直播的所有游戏分类
		'Api/GameLiveList'   => 'Api/Video/gameLiveList',                   //获取直播房间列表
		'Api/DelOperation'   => 'Api/Record/delOperation',                  //删除用户操作记录
		'Api/GetBanner'      => 'Api/Ads/getBanner',                        //获取广告
		//'Api/MyCredit'        => 'Api/Sign/myCredit',                       //我的积分
		'Api/UserAddress'    => 'Api/Product/addAddress',                   //兑换积分产品收货地址
		'Api/GetPCD'         => 'Api/Address/getPCD',                       //获取省市区
		'Api/GetAddress'     => 'Api/Product/getAddress',                   //获取收货地址
		'Api/GetLiveVideo'   => 'Api/Ads/GetLiveVideo',                     //获取第三方直播地址
		'Api/ForgetPassword' => 'Api/User/forgetPassword',                  //忘记密码





		'video/columns'      => 'Api/Video/columns',     // 视频列表
		'video/category'     => 'Api/Video/category',    // 视频分类
		'video/areas'        => 'Api/Video/areas',       // 视频区域
		'video/years'        => 'Api/Video/years',       // 视频所在年份
		'video/movieBanner'  => 'Api/Video/movieBanner', // 电影banner
		'video/dramaBanner'  => 'Api/Video/dramaBanner', // 电视剧banner
		'video/play'         => 'Api/Video/play',        // 视频播放记录
	//	'api/games'          => 'Api/Games/games',       // 获取游戏信息
		'games/category'     => 'Api/Games/category',    // 游戏分类
		'games/banner'       => 'Api/Games/banner',      // 游戏banner
		'games/download'     => 'Api/Games/download',    // 游戏下载统计
		'games/hm'           => 'Api/Games/igames'       // 爱游戏资料同步
	)
);
