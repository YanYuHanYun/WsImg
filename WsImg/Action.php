<?php

namespace TypechoPlugin\WsImg;

use Typecho\Common;
use Typecho\Http\Client;
use Typecho\Cookie;
use Widget\ActionInterface;
use Widget\Base\Contents;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

class Action extends Contents implements ActionInterface
{
    public function action()
    {
        if (!$this->request->isAjax()) {
            $this->response->goBack();
        }
        if (!$this->user->pass('contributor', true)) {
            $this->response->setStatus(403);
        }
        $this->security->protect();
        $client = Client::get();
        $do = $this->request->get('do', '');
        if ($do == 'send') {
            if(!$this->user->pass('administrator', true)) $this->response->throwJson([
                'code' => -1,
                'msg' => '权限不足'
            ]);
            $phone = $this->request->get('phone', '');
            if ($phone == '') $this->response->throwJson([
                'code' => -1,
                'msg' => '手机号不可为空'
            ]);
            $ticket = $this->request->get('ticket', '');
            $randstr = $this->request->get('randstr', '');
            if ($ticket == '' || $randstr == '') $this->response->throwJson([
                'code' => -1,
                'msg' => '请完成验证'
            ]);
            try {
                $client->setJson([
                    'phoneNumber' => $phone,
                    'ticket' => $ticket,
                    'randstr' => $randstr
                ])->send('https://www.wsxcme.com/identity/api/v3/login/phone/getVerfCode');
                $res = $client->getResponseBody();
                $res = json_decode($res, true);
                if ($res['errcode'] == 0) $this->response->throwJson([
                    'code' => 1,
                    'msg' => 'success'
                ]);
                $this->response->throwJson([
                    'code' => -1,
                    'msg' => $res['errmsg']
                ]);
            } catch (\Exception $e) {
                $this->response->throwJson([
                    'code' => -1,
                    'msg' => $e->getMessage()
                ]);
            }
        }
        if ($do == 'login') {
            if(!$this->user->pass('administrator', true)) $this->response->throwJson([
                'code' => -1,
                'msg' => '权限不足'
            ]);
            $phone = $this->request->get('phone', '');
            if ($phone == '') $this->response->throwJson([
                'code' => -1,
                'msg' => '手机号不可为空'
            ]);
            $code = $this->request->get('code', '');
            if ($code == '') $this->response->throwJson([
                'code' => -1,
                'msg' => '验证码不可为空'
            ]);
            try {
                $client->setJson([
                    'phoneNumber' => $phone,
                    'verfCode' => $code
                ])->send('https://www.wsxcme.com/identity/api/v3/login/phone/loginByVerfCode');
                $res = $client->getResponseBody();
                $res = json_decode($res, true);
                if ($res['errcode'] == 0) $this->response->throwJson([
                    'code' => 1,
                    'msg' => 'success',
                    'data' => $res['result']
                ]);
                $this->response->throwJson([
                    'code' => -1,
                    'msg' => $res['errmsg']
                ]);
            } catch (\Exception $e) {
                $this->response->throwJson([
                    'code' => -1,
                    'msg' => $e->getMessage()
                ]);
            }
        }
        if ($do == 'init') {
            $option = $this->options->plugin('WsImg');
            $token = $option->token;
            if ($token == '') $this->response->throwJson([
                'code' => -1,
                'msg' => '插件Token未配置'
            ]);
            try {
                $client->setHeader('Content-Type', 'application/json')->setCookie('token', $token)->send('https://www.wsxcme.com/common/api/v3/oss/qiniu/image/getToken');
                $res = $client->getResponseBody();
                $res = json_decode($res, true);
                if ($res['errcode'] == 0) $this->response->throwJson([
                    'code' => 1,
                    'msg' => 'success',
                    'data' => $res['result']
                ]);
                $this->response->throwJson([
                    'code' => -1,
                    'msg' => $res['errmsg']
                ]);
            } catch (\Exception $e) {
                $this->response->throwJson([
                    'code' => -1,
                    'msg' => $e->getMessage()
                ]);
            }
        }
        if ($do == 'record') {
            $data['name'] = Common::stripTags($this->request->get('name', ''));
            $data['width'] = Common::stripTags($this->request->get('width', ''));
            $data['height'] = Common::stripTags($this->request->get('height', ''));
            $data['size'] = Common::stripTags($this->request->get('size', ''));
            $data['url'] = Common::safeUrl($this->request->get('url', ''));
            foreach ($data as $k => $v) {
                if ($v == '') $this->response->throwJson([
                    'code' => -1,
                    'msg' => 'fail'
                ]);
            }
            $data['uid'] = Cookie::get('__typecho_uid');
            $data['time'] = time();
            $insert = $this->db->insert('table.ws_image_list')->rows($data);
            $row = $this->db->query($insert);
            if ($row) $this->response->throwJson([
                'code' => 1,
                'msg' => 'success'
            ]);
            $this->response->throwJson([
                'code' => -1,
                'msg' => 'fail'
            ]);
        }
        if ($do == 'list') {
            $page = $this->request->get('page', 1);
            $limit = 10;
            $offset = ($page - 1) * 10;
            if (!$this->user->pass('administrator', true)) {
                $query = $this->db->select('url,name')->where('uid = ?', Cookie::get('__typecho_uid'))->from('table.ws_image_list')->order('id', $this->db::SORT_DESC)->offset($offset)->limit($limit);
            } else {
                $query = $this->db->select('url,name')->from('table.ws_image_list')->order('id', $this->db::SORT_DESC)->offset($offset)->limit($limit);
            }
            $data = $this->db->fetchAll($query);
            $this->response->throwJson([
                'code' => 1,
                'msg' => 'success',
                'data' => $data
            ]);
        }
        $this->response->goBack();
    }
}