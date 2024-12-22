<?php
// 检查系统权限，如果未定义 SYSTEM_ROOT，则终止脚本
if (!defined('SYSTEM_ROOT')) { die('Insufficient Permissions'); }

// 声明全局变量 $m，通常用于数据库操作
global $m;

function cron_sign_httpmsg() {
    global $m;
    $currentHourMinute = date("H:i");
    $today = date("Y-m-d");

    $query = $m->query("SELECT * FROM `".DB_NAME."`.`".DB_PREFIX."users`");
    while ($fetch = $m->fetch_array($query)) {
        $name = $fetch['name'];
        $id = $fetch['id'];

        // 获取通知参数设置
        $httpmsgEnable = option::uget('tieba_httpmsg_enable', $id);
        $httpMsgurl = option::uget('tieba_httpmsg_url', $id);
        $httpmsgTime = option::uget('tieba_httpmsg_time', $id);

        if ($httpmsgEnable == 0 || empty($httpMsgurl) || empty($httpmsgTime)) {
            continue; // 未开启通知或参数错误，跳过此用户
        }

        $lastNotificationDate = option::uget('tieba_last_notification_date', $id);
        if ($today == $lastNotificationDate || $currentHourMinute != $httpmsgTime) {
            continue; // 今天已进行过通知或当前时间不匹配，跳过此用户
        }

        // 初始化计数器
        $totalCount = 0;
        $successCount = 0;
        $failureCount = 0;

        // 初始化通知内容
        $notificationContent = "";
        $query2 = $m->query("SELECT * FROM `".DB_NAME."`.`".DB_PREFIX."tieba` WHERE `uid` = $id");
        while ($tiebaInfo = $m->fetch_array($query2)) {
            $totalCount++; // 统计总数
            $tiebaName = $tiebaInfo['tieba'];
            $isSuccess = $tiebaInfo['status'] == 0;
            $statusText = $isSuccess ? '签到成功' : '签到失败';

            // 更新成功和失败计数
            if ($isSuccess) {
                $successCount++;
            } else {
                $failureCount++;
                $failureNotificationContent .= "$tiebaName: $statusText \n"; // 使用段落标签，只显示失败列表
            }
        }

        // 添加统计信息到通知第一行
        $notificationContent = "总数: $totalCount | ".
            "签到成功数: $successCount | ".
            "签到失败数: $failureCount" . "\n".
            "用户名: $name" . "\n";

        // 只有在有失败的签到时才添加失败列表
        if ($failureCount > 0) {
            $notificationContent .= "签到失败列表: \n $failureNotificationContent" . "\n";
        }

        $notificationContent .= "\n推送时间: $today $currentHourMinute";

        // 发送通知
        sendHttpMsgNotification($notificationContent, $httpMsgurl);

        // 更新最后通知日期
        option::uset('tieba_last_notification_date', $today, $id);
    }
    return sprintf(
        '通知发送成功！\n内容: %s\n用户名: %s\n用户ID: %s\n推送状态: %s\n推送时间: %s\n',
        $notificationContent,
        $name,
        $id,
        $httpmsgEnable ? '已开启' : '未开启',
        $httpmsgTime
    );

}

function sendHttpMsgNotification($content, $httpMsgurl) {
    // 构建请求内容
    $data = json_encode([
        'title' => '贴吧云签到通知',
        'text' => $content
    ]);

    // 设置 HTTP 请求的选项
    $options = [
        'http' => [
            'header' => "Content-Type: application/json",
            'method' => 'POST',
            'content' => $data
        ]
    ];
    $context = stream_context_create($options);
    // 发送请求并获取响应
    $response = file_get_contents($httpMsgurl, false, $context);
}
?>
