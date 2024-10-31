<?php
// 检查系统权限，如果未定义 SYSTEM_ROOT，则终止脚本
if (!defined('SYSTEM_ROOT')) { die('Insufficient Permissions'); }

// 声明全局变量 $m，通常用于数据库操作
global $m;

function cron_sign_pushplus() {
    global $m;
    $currentHourMinute = date("H:i");
    $today = date("Y-m-d");

    $query = $m->query("SELECT * FROM `".DB_NAME."`.`".DB_PREFIX."users`");
    while ($fetch = $m->fetch_array($query)) {
        $name = $fetch['name'];
        $id = $fetch['id'];

        // 获取通知参数设置
        $pushplusEnable = option::uget('tieba_pushplus_enable', $id);
        $pushplusToken = option::uget('tieba_pushplus_token', $id);
        $pushplusTime = option::uget('tieba_pushplus_time', $id);

        if ($pushplusEnable == 0 || empty($pushplusToken) || empty($pushplusTime)) {
            continue; // 未开启通知或参数错误，跳过此用户
        }

        $lastNotificationDate = option::uget('tieba_last_notification_date', $id);
        if ($today == $lastNotificationDate || $currentHourMinute != $pushplusTime) {
            //continue; // 今天已进行过通知或当前时间不匹配，跳过此用户
        }

        // 初始化计数器
        $totalCount = 0;
        $successCount = 0;
        $failureCount = 0;

        $query2 = $m->query("SELECT * FROM `".DB_NAME."`.`".DB_PREFIX."tieba` WHERE `uid` = $id");
        while ($tiebaInfo = $m->fetch_array($query2)) {
            $totalCount++; // 统计总数
            $tiebaName = $tiebaInfo['tieba'];
            $isSuccess = $tiebaInfo['status'] == 0;
            $statusColor = $isSuccess ? '#B7D6C1' : '#E0A4A1';
            $statusText = $isSuccess ? '签到成功' : '签到失败';

            // 更新成功和失败计数
            if ($isSuccess) {
                $successCount++;
            } else {
                $failureCount++;
            }

            $notificationContent .= "<p>$tiebaName: <span style='color: $statusColor;'>$statusText</span></p>"; // 使用段落标签
        }

        // 添加统计信息到通知第一行
        $notificationContent = "<h2>总数: <span style='color: #B7D6C1;'>$totalCount</span> | " .
            "签到成功数: <span style='color: #B7D6C1;'>$successCount</span> | " .
            "签到失败数: <span style='color: #E0A4A1;'>$failureCount</span></h2>" .
            "<h2>用户名: $name</h2>" .
            "<h3>贴吧列表:</h3>" . $notificationContent;

        // 发送通知
        sendPushPlusNotification($pushplusToken, $notificationContent);
        // 更新最后通知日期
        option::uset('tieba_last_notification_date', $today, $id);
    }
    return sprintf(
        '通知发送成功！\n内容: %s\n用户名: %s\n用户ID: %s\n推送状态: %s\n推送时间: %s\n',
        $notificationContent,
        $name,
        $id,
        $pushplusEnable ? '已开启' : '未开启',
        $pushplusTime
    );


}

function sendPushPlusNotification($token, $content) {
    // 构建请求内容
    $data = json_encode([
        'token' => $token,
        'title' => '贴吧云签到通知',
        'content' => $content
    ]);

    // 设置 HTTP 请求的选项
    $options = [
        'http' => [
            'header' => "Content-Type: application/json; charset=utf-8",
            'method' => 'POST',
            'content' => $data
        ]
    ];
    $context = stream_context_create($options);
    file_get_contents('http://www.pushplus.plus/send/', false, $context);
}
?>
