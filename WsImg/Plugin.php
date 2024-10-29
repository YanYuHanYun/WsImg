<?php

namespace TypechoPlugin\WsImg;

use Typecho\Plugin\PluginInterface;
use Typecho\Widget\Helper\Form;
use Typecho\Widget\Helper\Form\Element\Text;
use Typecho\Widget\Helper\Form\Element\Radio;
use Utils\Helper;
use Typecho\Widget;

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
        \Typecho\Plugin::factory('admin/header.php')->header = __CLASS__ . '::css';
        \Typecho\Plugin::factory('admin/write-post.php')->bottom = __CLASS__ . '::script';
        Helper::addRoute('WsImg', '/WsImg/init', '\\TypechoPlugin\\WsImg\\Ajax', 'token');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
        Helper::removeRoute('WsImg');
    }

    public static function css($header)
    {
        if (Widget::widget('Widget_User')->hasLogin()) {
            $header = $header . '<link rel="stylesheet" href="' . WsImg . 'css/main.css' . '" type="text/css"/><link href="//cdn.jsdelivr.net/npm/layui-layer@1.0.9/layer.min.css" rel="stylesheet">';
        }
        return $header;
    }

    public static function script($post)
    {
        $option = Helper::options()->plugin('WsImg');
        if (!$option->content) {
            return;
        }
        $html = '<div class="admin-upload-img"><label class="ui_button" for="admin-img-file">图片上传</label><form><input id="admin-img-file" type="file" multiple="multiple"></form><div id="img-list"></div></div>';
        if (Widget::widget('Widget_User')->hasLogin()) {
            echo '<script>SITE_URL="' . Helper::options()->index . '";</script>';
            echo '<script src="//cdn.jsdelivr.net/npm/layui-layer@1.0.9/dist/layer.min.js"></script>';
            echo '<script src="' . WsImg . 'js/content.js' . '"></script>';
            ?>
            <script>
                let WsImgHtml = '<?php echo $html;?>'
                $("#text").parent().append(WsImgHtml);
            </script>
            <?php
        }
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Form $form)
    {
        $token = new Text('token', null, '', _t('微商相册Token <a href="https://mmpwl.cn/WsImgLogin/" target="_blank">点击获取</a>'));
        $form->addInput($token);
        $Content_ = new Radio('content', array(
            1 => _t('启用'),
            0 => _t('关闭'),
        ), 1, _t('后台文章编辑启用图片上传'), _t('勾选后在后台文章编辑处自动添加图片上传按钮'));
        $form->addInput($Content_);
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
