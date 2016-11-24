# wx-api 微信支付、微信红包等的集成API
# jssdk
~~~~
    $jssdk = new \limx\tools\wx\JsSdk(config('sg_appid'), config('sg_appsecret'));
    /** @var 设置TOKEN缓存地址 token_path */
    $jssdk->setTokenPath('cache/' . Date('Ymd') . '/token.php');
    /** @var 设置票据缓存地址 ticket_path */
    $jssdk->setTicketPath('cache/' . Date('Ymd') . '/ticket.php');
    /** @var 获得签名数据包 $signPackage */
    $signPackage = $jssdk->GetSignPackage();
    $this->assign('signPackage', $signPackage);
    return $this->fetch('jssdk');
~~~~

# 发送红包
~~~
    $data['mch_id'] = $config['mchid'];
    $data['wxappid'] = $config['appid'];
    $data['send_name'] = 'PHP DOG';
    $data['re_openid'] = '';
    $data['total_amount'] = 100;

    $key = $config['key']; // 商户后台设置的
    $data = \limx\tools\wx\pay\RedPack::getData($data, $key);
    dump($data);

    $res = \limx\tools\wx\pay\RedPack::sendRedPack($data, $config);
    dump($res);

    if ($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS') {
        dump('红包发送成功！');
    }
~~~

# 模版消息
~~~
    $api = new \limx\tools\wx\TempMsg($appid, $appsec);
    /** @var 设置TOKEN缓存地址 token_path */
    $api->setTokenPath('cache/' . Date('Ymd') . '/token.php');
    $openid = config('sg_test_openid');
    $tem_id = 'S3c3oQNp1N44WrVbopW3gtch234l7bzPjnvqDzRea44uiJexiE';
    $url = 'http://demo.tp5.lmx0536.cn/';
    $data['first']['value'] = '测试信息';
    $data['orderMoneySum']['value'] = '1';
    $data['orderProductName']['value'] = '测试';
    $data['Remark']['value'] = '点击查看详情';
    /** @var 发送模版消息 $res */
    $res = $api->send($openid, $tem_id, $url, $data);
    dump($res);
~~~

# APP 后端签名
~~~
    // 使用微信官方SDK生成订单 这里直接下载即可
    $input = new \WxPayUnifiedOrder();  
    $input->SetBody("test");
    $input->SetAttach("test");
    $input->SetOut_trade_no(\WxPayConfig::MCHID . date("YmdHis"));
    $input->SetTotal_fee("1");
    $input->SetTime_start(date("YmdHis"));
    $input->SetTime_expire(date("YmdHis", time() + 600));
    $input->SetGoods_tag("test");
    $input->SetNotify_url("http://demo.tp5.lmx0536.cn/index/tools.wx_helper/notify");
    $input->SetTrade_type("APP");
    dump($input);

    // 生成订单
    $res = \WxPayApi::unifiedOrder($input);
    dump($res);

    //根据订单吊起支付
    $res = \limx\tools\wx\pay\AppPay::getPayData($res, $config['key']);
    dump($res);// 此数据交给APP吊起支付
~~~

# 微信授权 获取信息
~~~
    $api = new \limx\tools\wx\OAuth($appid, $appsec);
    $api->code = $code;// 微信官方回调回来后 会携带code
    $url = request()->instance()->url(true);//当前的URL
    $api->setRedirectUrl($url);
    $res = $api->getUserInfo();
    dump($res);
~~~

# 微信JsApiPay支付
 在pay/lib/WxPayConfig中维护公众号信息。
~~~
/** 微信支付 S */
use limx\tools\wx\pay\JsApiPay;
use limx\tools\wx\pay\data\WxPayUnifiedOrder;
use limx\tools\wx\pay\lib\WxPayApi;
/** 微信支付 E */

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody("test");
$input->SetAttach("test");
$input->SetOut_trade_no(date("YmdHis"));
$input->SetTotal_fee("1");
$input->SetTime_start(date("YmdHis"));
//$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("http://paysdk.weixin.qq.com/example/notify.php");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);
$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
$this->view->jsApiParameters = $jsApiParameters;
return $this->view->render('test/wx', 'pay');
~~~

