<?php
// 初始化回调函数 callback_init
function callback_init() {
    // 设置一个定时任务（cron job）
    // 第一个参数为任务名称 'sign_httpmsg'
    // 第二个参数为任务执行的文件路径 'plugins/tieba_sign_httpmsg/send.php'
    // 后面三个参数分别表示：分钟、小时和天的执行时间
    // 这里设置为每天的凌晨 0 点执行
    cron::set('sign_httpmsg', 'plugins/tieba_sign_httpmsg/send.php', '0', '0', '0');
}

// 移除回调函数 callback_remove
function callback_remove() {
    // 删除之前设置的定时任务
    cron::del('sign_httpmsg');
}
?>
