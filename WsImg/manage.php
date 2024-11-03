<?php
include 'header.php';
include 'menu.php';

use Typecho\Cookie;
use Typecho\Db;
use Typecho\Request;
use Typecho\Widget;
use Utils\Helper;

if (!defined('__TYPECHO_ROOT_DIR__')) {
    exit;
}
$db = Db::get();
$request = Request::getInstance();
$action = $request->get('action', '');
$action2 = $request->get('action2', '');
if ($action == 'delete' || $action2 == 'delete') {
    $options = Helper::options();
    foreach ($request->get('imglist', []) as $v) {
        if(!Widget::widget('Widget_User')->pass('administrator', true)) {
            $delete = $db->delete('table.ws_image_list')->where('id = ?', $v)->where('uid = ?', Cookie::get('__typecho_uid'));
        }else{
            $delete = $db->delete('table.ws_image_list')->where('id = ?', $v);
        }
        $db->query($delete);
    }
}
$pages = $request->get('page', 1);
$limit = 10;
$offset = ($pages - 1) * 10;
if(!Widget::widget('Widget_User')->pass('administrator', true)) {
    $query = $db->select()->from('table.ws_image_list')->where('uid = ?', Cookie::get('__typecho_uid'))->order('id', Db::SORT_DESC)->offset($offset)->limit($limit);
    $count_S = $db->select('COUNT(*)')->where('uid = ?', Cookie::get('__typecho_uid'))->from('table.ws_image_list');
} else{
    $query = $db->select()->from('table.ws_image_list')->order('id', Db::SORT_DESC)->offset($offset)->limit($limit);
    $count_S = $db->select('COUNT(*)')->from('table.ws_image_list');
}
$rs = $db->fetchAll($query);
$count = $db->fetchAll($count_S);
$t = 'COUNT(*)';
$count = $count[0][$t];
$all_pages = (int)($count / 10) + 1;
$query = $db->select()->from('table.users');
$users = $db->fetchAll($query);
?>
    <div class="main">
        <div class="body container">
            <?php include 'page-title.php'; ?>
            <div class="row typecho-page-main manage-metas">
                <div class="col-mb-12" role="main">
                    <!-- typecho-list-operate -->
                    <div class="typecho-list-operate clearfix">
                        <form method="get">
                            <div class="operate">
                                <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all"/></label>
                                <div class="btn-group btn-drop">
                                    <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                    <ul class="dropdown-menu">
                                        <li><a lang="<?php _e('仅可删除本地记录，无法删除图片，你确认要删除吗??'); ?>" href="#" onclick="submitF(event)"><?php _e('删除'); ?></a></li>
                                    </ul>
                                </div>
                            </div>
                        </form>
                    </div><!-- end .typecho-list-operate -->
                    <form id="manage-gallery" method="post">
                        <input type="hidden" id="action" name="action" class="button action" value="-1"/>
                        <div class="typecho-table-wrap">
                            <table class="typecho-list-table widefat striped users">
                                <thead>
                                <tr>
                                    <th scope="col" id='cb' class='manage-column column-typecho check-column'></th>
                                    <th scope="col" class='manage-column'>图片</th>
                                    <th scope="col" class='manage-column'>名称</th>
                                    <th scope="col" class='manage-column kit-hidden-mb'>尺寸</th>
                                    <th scope="col" class='manage-column kit-hidden-mb'>大小</th>
                                    <th scope="col" class='manage-column kit-hidden-mb'>所属用户</th>
                                    <th scope="col" class='manage-column kit-hidden-mb'>上传时间</th>
                                </tr>
                                </thead>
                                <tbody id="the-list" data-typecho-lists='list:user'>
                                <?php if (count($rs) > 0): ?>
                                    <?php foreach ($rs as $res) { ?>
                                        <tr>
                                            <td scope='row' class='check-column'>
                                                <input type="checkbox" name="imglist[]" id="user_1" class="administrator" value="<?php echo htmlspecialchars($res['id']); ?>"/>
                                            </td>
                                            <td>
                                                <img alt='' src='<?php echo htmlspecialchars($res['url']); ?>'
                                                     class='avatar avatar-32 photo' width='100'/>
                                                <a href="<?php echo htmlspecialchars($res['url']); ?>"
                                                   title="<?php echo htmlspecialchars($res['url']); ?>" target="_blank"
                                                   rel="noopener noreferrer"><i class="i-exlink"></i></a>
                                            </td>
                                            <td>
                                                <?php echo htmlspecialchars($res['name']); ?>
                                            </td>
                                            <td class="kit-hidden-mb">
                                                <?php echo htmlspecialchars($res['width']); ?>
                                                * <?php echo htmlspecialchars($res['height']); ?>
                                            </td>
                                            <td class="kit-hidden-mb">
                                                <?php $size = htmlspecialchars($res['size']);
                                                echo $size > 1048576 ? round($size / 1048576, 2) . 'mb' : round($size / 1024, 2) . 'kb'; ?>
                                            </td>
                                            <td class="kit-hidden-mb">
                                                <?php
                                                foreach ($users as $user) {
                                                    if($user['uid'] == $res['uid']) {
                                                        echo $user['name'];
                                                        break;
                                                    }
                                                }
                                                ?>
                                            </td>
                                            <td class="kit-hidden-mb">
                                                <?php echo date('Y-m-d H:i:s', $res['time']); ?>
                                            </td>
                                        </tr>
                                    <?php } ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7"><h6 class="typecho-list-table-title"><?php _e('没有任何内容'); ?></h6>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </form>
                    <div class="typecho-list-operate clearfix">
                        <form method="get">
                            <div class="operate">
                                <label><i class="sr-only"><?php _e('全选'); ?></i><input type="checkbox" class="typecho-table-select-all"/></label>
                                <div class="btn-group btn-drop">
                                    <button class="btn dropdown-toggle btn-s" type="button"><i class="sr-only"><?php _e('操作'); ?></i><?php _e('选中项'); ?> <i class="i-caret-down"></i></button>
                                    <ul class="dropdown-menu">
                                        <li><a lang="<?php _e('仅可删除本地记录，无法删除图片，你确认要删除吗?'); ?>" href="#" onclick="submitF(event)"><?php _e('删除'); ?></a></li>
                                    </ul>
                                </div>
                            </div>
                            <?php if ($all_pages != 1) { ?>
                                <div class="" style="float: right">
                                    <button class="btn" type="button" onclick="window.location.href='<?php if ($pages != 1) { ?>?panel=WsImg%2Fmanage.php&page=<?php echo($pages - 1); ?><?php } ?>';" <?php if ($pages == 1) { ?>disabled<?php } ?>>上一页
                                    </button>
                                    <button type="button" onclick="window.location.href='<?php if ($pages != $all_pages) { ?>?panel=WsImg%2Fmanage.php&page=<?php echo($pages + 1); ?><?php } ?>';" class="btn" <?php if ($pages == $all_pages) { ?>disabled<?php } ?>>下一页
                                    </button>
                                    <br class="clear"/>
                                </div>
                            <?php } ?>
                        </form>
                    </div><!-- end .typecho-list-operate -->
                    <br class="clear"/>
                </div>
            </div>
        </div>
    </div>
<?php
include 'copyright.php';
include 'common-js.php';
include 'table-js.php';
?>
    <script>
        function submitF(e) {
            let form_ = $("#manage-gallery");
            let action = $("#action")
            action.val('delete')
            form_.submit();
            return false;
        }
    </script>
<?php include 'footer.php'; ?>