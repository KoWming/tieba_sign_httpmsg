<?php
// 检查系统权限，如果未定义 SYSTEM_ROOT，则终止脚本
if (!defined('SYSTEM_ROOT')) { die('Insufficient Permissions'); }

/*
Plugin Name: 自定义消息推送
Version: 1.8
Plugin URL: https://github.com/KoWming/tieba_sign_pushplus
Description: 贴吧云签到每日签到完成情况自定义消息推送插件
Author: KoWming
Author URL: https://github.com/KoWming
*/

// 定义设置页面的函数 tieba_sign_httpmsg_setting
function tieba_sign_httpmsg_setting() {
    global $m; // 引用全局变量 $m
    ?>
    <tr><td>开启自定义消息推送</td>
    <td>
        <!-- 设置通知开关，使用单选按钮 -->
        <input type="radio" name="tieba_httpmsg_enable" value="1" <?php if (option::uget('tieba_httpmsg_enable') == 1) { echo 'checked'; } ?> > 是&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
        <input type="radio" name="tieba_httpmsg_enable" value="0" <?php if (option::uget('tieba_httpmsg_enable') != 1) { echo 'checked'; } ?> > 否
    </td>
    </tr>
	<tr><td>消息推送地址</td>
	<td>
		<input type="text" class="form-control" name="tieba_httpmsg_url" value="<?php echo option::uget('tieba_httpmsg_url'); ?>" >
	</td>
    <tr><td>推送时间</td>
    <td>
        <!-- 输入框用于设置推送时间 -->
        <input type="time" name="tieba_httpmsg_time" value="<?php echo option::uget('tieba_httpmsg_time'); ?>">
    </td>
    </tr>
    <?php
}

// 定义设置保存函数 tieba_sign_httpmsg_set
function tieba_sign_httpmsg_set() {
    global $PostArray; // 引用全局变量 $PostArray
    if (!empty($PostArray)) {
        // 将需要保存的设置项添加到 $PostArray 中
        $PostArray[] = 'tieba_httpmsg_enable';
        $PostArray[] = 'tieba_httpmsg_url';
        $PostArray[] = 'tieba_httpmsg_time';
    }
}

// 注册保存设置和展示设置的钩子
addAction('set_save1', 'tieba_sign_httpmsg_set'); // 保存设置时调用 tieba_sign_httpmsg_set
addAction('set_2', 'tieba_sign_httpmsg_setting'); // 显示设置页面时调用 tieba_sign_httpmsg_setting
?>
