<?php
require_once "jssdk.php";
/**
 *
 * 微信APi接口类
 * @author limx
 *
 */
class WxApi extends JSSDK {

	public $code = "";
	public $state = "";
	public $redirect_uri = "";

	/**
	 * [WxGetUserInfo 获取用户授权基本信息]
	 * @author limx
	 * @return array|mixed
	 */
	public function WxGetUserInfo() {
		// 获取AccessToken
		$accessToken = $this -> getOAuthAccessToken();
		//获取基本信息
		$url = "https://api.weixin.qq.com/sns/userinfo?access_token=" . $accessToken["access_token"] . "&openid=" . $accessToken["openid"] . "&lang=zh_CN";
		
		//$res = $this -> httpGet($url);
		
		$res = json_decode($this -> httpGet($url));
		if (is_object($res)) {
			$res = (array)$res;
		}

		return $res;

	}

	private function getOAuthAccessToken() {

		if ($this -> code == "") {
			$this -> getCode();
			exit ;
		}
		$url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=$this->appId&secret=$this->appSecret&code=$this->code&grant_type=authorization_code";
		$res = json_decode($this -> httpGet($url));
		if (is_object($res)) {
			$res = (array)$res;
		}
		return $res;
	}

	private function getCode() {

		$url = "https://open.weixin.qq.com/connect/oauth2/authorize?appid=$this->appId&redirect_uri=$this->redirect_uri&response_type=code&scope=snsapi_userinfo&state=$this->state#wechat_redirect";
		//echo $url;exit;
		Header("Location: $url");
	}


	/**
	 * [sendTemMsg 发送模版消息]
	 * @author limx
	 * @param $openid
	 * @param $tem_id
	 * @param $url
	 * @param $d
	 * @return mixed
	 */
	public function sendTemMsg($openid,$tem_id,$url,$d){

		$acc_token=$this->lfnGetAccessToken();
		$data['touser']=$openid;
		$data['template_id']=$tem_id;
		$data['url']=$url;
		$data['data']=$d;
		//return http_build_query($data);

		$res = $this->lfnCurlPostJson('https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$acc_token,$data);
		$res = json_decode($res);
		return $res;

	}

}
