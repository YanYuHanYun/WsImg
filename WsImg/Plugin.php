<?php

namespace TypechoPlugin\WsImg;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Utils\Helper;
use Typecho\Widget;
use Typecho\Db;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}

define('WsImg', Helper::options()->pluginUrl . '/WsImg/');

/**
 * 微商相册图床插件
 *
 *
 * @package WsImg
 * @author 烟雨寒云
 * @version 1.0
 * @link https://www.yyhy.me/
 */
class Plugin implements PluginInterface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        $db = Db::get();
        $getAdapterName = $db->getAdapterName();
        if (!preg_match('/^M|m?ysql$/', $getAdapterName)) {
            throw new Typecho_Plugin_Exception(_t('对不起，使用了不支持的数据库，无法使用此功能，仅支持MySql数据库。'));
        }
        $charset_collate = '';
        if (!empty($db->charset)) {
            $charset_collate = "DEFAULT CHARACTER SET {$db->charset}";
        }
        if (!empty($db->collate)) {
            $charset_collate .= " COLLATE {$db->collate}";
        }
        $prefix = $db->getPrefix();
        $table_name = $prefix . 'ws_image_list';
        $sql = 'SHOW TABLES LIKE "' . $table_name . '"';
        $checkTabel = $db->query($sql);
        $row = $checkTabel->fetchAll();
        if (count($row) != 1) {
            $sql = "CREATE TABLE `$table_name` (
                id int NOT NULL AUTO_INCREMENT PRIMARY KEY,
                width int NOT NULL,
                height int NOT NULL,
                size int NOT NULL,
                uid int NOT NULL,
                time int NOT NULL,
                name varchar(255) NOT NULL,
                url varchar(255) NOT NULL
	        ) $charset_collate;";
            $db->query($sql);
        }
        \Typecho\Plugin::factory('admin/header.php')->header = __CLASS__ . '::css';
        \Typecho\Plugin::factory('admin/write-post.php')->bottom = __CLASS__ . '::script';
        \Typecho\Plugin::factory('admin/write-page.php')->bottom = __CLASS__ . '::script';
        Helper::addPanel(3, 'WsImg/manage.php', 'WsImg图床', '管理WsImg图床', 'contributor');
        Helper::addAction('ws_ajax', '\\TypechoPlugin\\WsImg\\Action');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
        $db = Db::get();
        $prefix = $db->getPrefix();
        $table_name = $prefix . 'ws_image_list';
        $db->query("DROP TABLE `$table_name`;");
        Helper::removePanel(3, 'WsImg/manage.php');
        Helper::removeAction('ws_ajax');
    }

    public static function css($header)
    {
        if (Widget::widget('Widget_User')->hasLogin()) {
            $header = $header . '<link rel="stylesheet" href="' . WsImg . 'css/main.css' . '" type="text/css"/>';
        }
        return $header;
    }

    public static function script($post)
    {
        $option = Helper::options()->plugin('WsImg');
        if (!$option->content) {
            return;
        }
        $html = <<<EOF
<section class="ws-img">
    <label class="typecho-label">微商相册图床</label>
    <div class="admin-upload-img" style="margin-top: 10px;">
        <label for="admin-img-file" class="btn btn-xs" style="padding-top:4px;">图片上传</label>
        <div id="img-data">
            <div id="img-list"></div>
            <div id="more"></div>
        </div>
    </div>
    <form><input id="admin-img-file" type="file" accept="image/*" multiple="multiple"></form>
</section>
EOF;
        if (Widget::widget('Widget_User')->hasLogin()) {
            echo '<script>$("#text").parent().append(`' . $html . '`);</script>';
            echo '<script>AJAX_URL="' . Helper::security()->getIndex('/action/ws_ajax') . '";</script>';
            echo '<script src="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/layer/3.5.1/layer.min.js"></script>';
            echo '<script src="' . WsImg . 'js/main.js' . '"></script>';
        }
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
        $token = new Text('token', null, '', _t('微商相册Token <button type="button" id="login_btn" onclick="ws_img_login($(this))" class="btn btn-xs">点击登录</button><div style="display:none;margin-top:10px;" id="ws_img_login"><input id="phone" type="text" class="text" style="width:70%" placeholder="请输入微商相册手机号"><button style="width:30%" type="button" id="send" onclick="send_sms()" class="btn primary">获取验证码</button><input id="code" type="text" class="text" style="margin-top:10px;" placeholder="请输入验证码"><button style="margin-top:10px;" type="button" class="btn primary" onclick="login()">登录</button></div><script src="/admin/js/jquery.js?v=1.2.1" type="text/javascript"></script><script src="https://lf26-cdn-tos.bytecdntp.com/cdn/expire-1-M/layer/3.5.1/layer.min.js"></script><script>AJAX_URL="' . Helper::security()->getIndex('/action/ws_ajax') . '";</script><script src="https://turing.captcha.qcloud.com/TCaptcha.js"></script><script src="' . WsImg . 'js/login.js' . '"></script>'), _t('点击登录可以快速便捷获取Token'));
        $form->addInput($token);
        $content = new Radio('content', array(
            1 => _t('启用'),
            0 => _t('关闭'),
        ), 1, _t('后台文章/页面编辑启用图片上传'), _t('勾选后在后台文章/页面编辑处自动添加图片上传按钮'));
        $form->addInput($content);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Form $form)
    {
    }
}
