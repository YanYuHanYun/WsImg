<?php

namespace TypechoPlugin\WsImg;


use Typecho\Widget;
use GuzzleHttp\Client;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
include 'vendor/autoload.php';

class Ajax extends Widget
{
    public static function token()
    {
        if (!Widget::widget('Widget_User')->hasLogin()) return;
        $client = new Client();
        $option = Helper::options()->plugin('WsImg');
        $token = $option->token;
        try {
            $response = $client->request('POST', 'https://www.wsxcme.com/common/api/v3/oss/qiniu/image/getToken', [
                'headers' => [
                    'Content-type' => 'application/json',
                    'Cookie' => 'token=' . $token . ';'
                ],
                'verify' => false,
                'timeout' => 10
            ]);
            $res = $response->getBody()->getContents();
            $res = json_decode($res, true);
            if ($res['errcode'] == 0) die(json_encode([
                'code' => 1,
                'msg' => 'success',
                'data' => $res['result']
            ]));
            die(json_encode([
                'code' => -1,
                'msg' => $res['errmsg']
            ]));
        } catch (\Exception $e) {
            die(json_encode([
                'code' => -1,
                'msg' => $e->getMessage()
            ]));
        }
    }
}