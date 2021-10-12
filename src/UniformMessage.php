<?php

namespace shiyunSdk\wechatSdk;

/**
 * TODO 统一服务消息
 * @Author sxd
 * @Date 2019-08-06 10:02
 */
class UniformMessage extends WechatCommon
{

    /**
     * 发送统一服务消息 （不可用）
     * 官方文档https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/uniform-message/uniformMessage.send.html#method-http
     * @description
     * @example
     * @author LittleMo
     * @since 2021-01-29
     * @version 2021-01-29
     * @param array $config
     * @param array $template
     * @param string $touser
     * @param string $type
     * @return void
     */
    public function send($config = array(), $template = array(), $touser = '', $type = 'weapp_template_msg')
    {
        dump($config);
        dump($template);
        dump($touser);
        dump($type);
        //         属性	类型	默认值	必填	说明
        // access_token	string		是	接口调用凭证
        // touser	string		是	用户openid，可以是小程序的openid，也可以是mp_template_msg.appid对应的公众号的openid
        // weapp_template_msg	Object		否	小程序模板消息相关的信息，可以参考小程序模板消息接口; 有此节点则优先发送小程序模板消息
        // mp_template_msg	Object		是	公众号模板消息相关的信息，可以参考公众号模板消息接口；有此节点并且没有weapp_template_msg节点时，发送公众号模板消息

        $data = array();
        $access_token = $this->wxAccessToken($config['appid'], $config['appsecret']);
        $data['touser'] = $touser;

        if ($type == 'weapp_template_msg') {
            $data['weapp_template_msg'] = [
                'template_id' => $template['template_id'],
                'page' => $template['page'],
                'form_id' => $template['form_id'],
                'data' => array(),
            ];

            foreach ($template['data'] as $key => $val) {
                $data['weapp_template_msg']['data']['keyword' . ($key + 1)] = array(
                    "value" => $val
                );
            }
            $data['weapp_template_msg']['emphasis_keyword'] = $template['emphasis_keyword'] ?? '';
        } else if ($type == 'mp_template_msg') {
        }


        // dd(json_encode($data[']));
        \CTOCODE_Logger::debug($data, 'UniformMessage' . date("Y-m-d"));
        // $url =  "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/uniform_send?access_token={$access_token}";
        $url =  "https://api.weixin.qq.com/cgi-bin/message/subscribe/send?access_token={$access_token}";
        $result = $this->wxHttpsRequest($url, json_encode($data['weapp_template_msg']));
        $jsoninfo = json_decode($result, true);
        \CTOCODE_Logger::debug($jsoninfo, 'xcxTemplateMessage' . date("Y-m-d"));
        return $jsoninfo;
    }

    /**
     *  发送小程序订阅消息
     * 官方文档 https://developers.weixin.qq.com/miniprogram/dev/api-backend/open-api/subscribe-message/subscribeMessage.send.html
     * @description
     * @example
     * @author LittleMo
     * @since 2021-01-29
     * @version 2021-01-29
     * @param array $config
     * @param array $template
     * @param string $touser
     * @return void
     */
    public function sendXcxSubscribe($config = array(), $template = array(), $touser = '')
    {

        //         属性	类型	默认值	必填	说明
        // access_token	string		是	接口调用凭证
        // touser	string		是	用户openid，可以是小程序的openid，也可以是mp_template_msg.appid对应的公众号的openid
        // weapp_template_msg	Object		否	小程序模板消息相关的信息，可以参考小程序模板消息接口; 有此节点则优先发送小程序模板消息
        // mp_template_msg	Object		是	公众号模板消息相关的信息，可以参考公众号模板消息接口；有此节点并且没有weapp_template_msg节点时，发送公众号模板消息

        $data = array();
        $access_token = $this->wxAccessToken($config['appid'], $config['appsecret']);
        $data['touser'] = $touser;

        $data['template_id'] = $template['template_id'];
        $data['page'] = $template['page'];
        $data['form_id'] = $template['form_id'];
        $data['data'] = array();

        foreach ($template['data'] as $key => $val) {
            $data['data'][$key] = array(
                "value" => $val
            );
        }
        $data['emphasis_keyword'] = $template['emphasis_keyword'] ?? '';



        // dd(json_encode($data));
        \CTOCODE_Logger::debug($data, 'UniformMessage' . date("Y-m-d"));
        $url = self::URL_API_PREFIX . "/message/subscribe/send?access_token={$access_token}";
        $result = $this->wxHttpsRequest($url, json_encode($data));
        $jsoninfo = json_decode($result, true);
        \CTOCODE_Logger::debug($jsoninfo, 'xcxTemplateMessage/sendXcxSubscribe' . date("Y-m-d"));
        return $jsoninfo;
    }
}
