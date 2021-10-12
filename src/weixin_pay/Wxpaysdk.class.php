<?php

namespace shiyunSdk\wechatPay;

/**
 * App专属微信支付类
 */

use shiyunSdk\wechatSdk\common\TraitBaseHelper;
use shiyunSdk\wechatSdk\common\TraitBaseXml;

class Wxpaysdk
{

    use TraitBaseXml, TraitBaseHelper;
    const UFORDER_URL = 'https://api.mch.weixin.qq.com/pay/unifiedorder'; // 获取预支付URL,prepayid.
    private $mchid; // 微信支付商户号
    private $mchkey; // 微信支付商户KEY
    private $openid; // 微信支付用户号
    private $appid;
    private $appsecret;
    private $out_trade_no;
    private $total_fee; // 总金额
    private $notify_url; // ND地址
    private $trade_type; // JSAPI
    private $curl_timeout;
    // 动态参数
    private $parameters;
    // 非必填参数，商户可根据实际情况选填
    // $unifiedOrder->setParameter("sub_mch_id","XXXX");//子商户号
    // $unifiedOrder->setParameter("device_info","XXXX");//设备号
    // $unifiedOrder->setParameter("attach","XXXX");//附加数据
    // $unifiedOrder->setParameter("time_start","XXXX");//交易起始时间
    // $unifiedOrder->setParameter("time_expire","XXXX");//交易结束时间
    // $unifiedOrder->setParameter("goods_tag","XXXX");//商品标记
    // $unifiedOrder->setParameter("openid","XXXX");//用户标识
    // $unifiedOrder->setParameter("product_id","XXXX");//商品ID

    // 微信CURL响应
    public $response; // 微信返回的响应
    public $result; // 返回参数，类型为关联数组

    //
    private $prepay_id; // 获取prepay_id
    public function __construct($options)
    {
        $this->mchid = isset($options['mchid']) ? $options['mchid'] : '';
        $this->mchkey = isset($options['mchkey']) ? $options['mchkey'] : '';
        $this->openid = isset($options['openid']) ? $options['openid'] : '';
        $this->_appID = isset($options['appid']) ? $options['appid'] : '';
        $this->appsecret = isset($options['appsecret']) ? $options['appsecret'] : '';
        // $this->access_token = isset($options['access_token'])?$options['access_token']:'';
        $this->out_trade_no = isset($options['out_trade_no']) ? $options['out_trade_no'] : '';
        $this->total_fee = isset($options['total_fee']) ? $options['total_fee'] : '';
        $this->notify_url = isset($options['notify_url']) ? $options['notify_url'] : '';
        $this->trade_type = isset($options['trade_type']) ? $options['trade_type'] : 'JSAPI';
        $this->curl_timeout = isset($options['curl_timeout']) ? $options['curl_timeout'] : '30';
    }

    /**
     * 获取prepay_id
     */
    function getPrepayId()
    {
        $url = 'https://api.mch.weixin.qq.com/pay/unifiedorder';
        $this->postXml($url); // 追入获取链接
        $this->result = $this->xmlToArray($this->response);
        $prepay_id = $this->result["prepay_id"];
        return $prepay_id;
    }

    /**
     * 	作用：设置JSAPI_prepay_id
     */
    function setPrepayId($prepayId)
    {
        $this->prepay_id = $prepayId;
    }

    /**
     * 	作用：设置jsapi的参数
     */
    public function getJSAPI()
    {
        // $jsApiObj["appId"] = $this->_appID;
        // $jsApiObj["appId"]='wx59ac86047a622526';
        // $timeStamp = time();
        // $jsApiObj["timeStamp"] = "$timeStamp";
        // $jsApiObj["nonceStr"] = $this->createNoncestr();
        // $jsApiObj["package"] = "prepay_id=$this->prepay_id";
        // $jsApiObj["signType"] = "MD5";
        // $jsApiObj["paySign"] = $this->getSign($jsApiObj);
        // return $jsApiObj;
        // $this->parameters = json_encode($jsApiObj);
        // return $this->parameters;
        $wOpt['appId'] = $this->_appID;
        $timeStamp = time();
        $wOpt['timeStamp'] = "$timeStamp";
        $wOpt['nonceStr'] = $this->createNoncestr(8);
        $wOpt['package'] = 'prepay_id=' . $this->prepay_id;
        $wOpt['signType'] = 'MD5';
        ksort($wOpt, SORT_STRING);
        foreach ($wOpt as $key => $v) {
            $string .= "{$key}={$v}&";
        }
        $string .= "key=" . $this->mchkey;
        echo $string . "<br><br>";
        $wOpt['paySign'] = strtoupper(md5($string));
        echo $wOpt['paySign'];
        return $wOpt;
    }
    /**
     * 	作用：设置请求参数
     */
    function setParameter($parameter, $parameterValue)
    {
        $this->parameters[$this->trimString($parameter)] = $this->trimString($parameterValue);
    }

    /**
     * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml()
    {
        $this->parameters["appid"] = $this->_appID; // 公众账号ID
        $this->parameters["mch_id"] = $this->mchid; // 商户号
        $this->parameters["nonce_str"] = $this->createNoncestr(); // 随机字符串
        $this->parameters["spbill_create_ip"] = $_SERVER['REMOTE_ADDR']; // 终端ip
        $this->parameters["sign"] = $this->getSign($this->parameters); // 签名
        return $this->arrayToXml($this->parameters);
    }
    /**
     * 	作用：post请求xml
     */
    function postXml($url)
    {
        $xml = $this->createXml();
        // dump($xml);
        $this->response = $this->postXmlCurl($xml, $url, $this->curl_timeout);
        // dump($this->response);
        return $this->response;
    }

    /**
     * 	作用：以post方式提交xml到对应的接口url
     */
    public function postXmlCurl($xml, $url, $second = 30)
    {
        // 初始化curl
        $ch = curl_init();
        // 设置超时
        curl_setopt($ch, CURLOP_TIMEOUT, $second);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        curl_close($ch);
        // 返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }
    /**
     * 	作用：使用证书，以post方式提交xml到对应的接口url
     */
    function postXmlSSLCurl($xml, $url, $second = 30)
    {
        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // 设置证书
        // 使用证书：cert 与 key 分别属于两个.pem文件
        // 默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLCERT, WxPayConf_pub::SSLCERT_PATH);
        // 默认格式为PEM，可以注释
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
        curl_setopt($ch, CURLOPT_SSLKEY, WxPayConf_pub::SSLKEY_PATH);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        $data = curl_exec($ch);
        // 返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }
}