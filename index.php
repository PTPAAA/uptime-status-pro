<?php
$apiKey = '写你的API密钥';
$cacheFile = 'api_cache.json';
$cacheTime = 300; // 缓存时间，秒

// 检查缓存文件是否过期
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    // 读缓存
    $responseData = json_decode(file_get_contents($cacheFile), true);
} else {
        // 过期
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, 'https://api.uptimerobot.com/v2/getMonitors');
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(['api_key' => $apiKey, 'format' => 'json', 'custom_uptime_ratios' => '30']));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $headers = [
        'Content-Type: application/x-www-form-urlencoded',
        'Cache-Control: no-cache'
    ];

    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    curl_close($ch);

    // 保存缓存
    $responseData = json_decode($response, true);
    if ($responseData['stat'] === 'ok') {
        file_put_contents($cacheFile, $response);
    }
}

// 检查错误
if (isset($responseData['stat']) && $responseData['stat'] === 'ok') {
    // 输出监控项和状态
    $monitors = $responseData['monitors'];
} else {
    $error = $responseData['error']['message'];
}
?>

<!DOCTYPE html>
<html lang="zh-CN">
<head>
<meta charset="UTF-8">
<link rel="icon" href="https://cdn.axe.ink/favicon.ico" type="image/x-icon">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>UPTIME-状态监控</title>
<style>
body {
  font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
  background-color: #f4f4f4;
  margin: 0;
  padding: 20px;
  color: #333;
}
.monitor-list {
  list-style: none;
  padding: 0;
}
.monitor-list li {
  padding: 15px;
  background-color: #fff;
  margin-bottom: 10px;
  border-left: 5px solid #FFA500; /* 橙色 */
  border-radius: 5px;
  box-shadow: 0 2px 5px rgba(0,0,0,0.1);
  transition: all 0.3s ease;
}
.monitor-list li:hover {
  transform: translateY(-5px);
  box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}
.status {
  display: inline-block;
  padding: 2px 5px;
  border-radius: 3px;
  color: #fff;
}
.online {
  background-color: #4CAF50; /* 在线显示 */
}
.offline {
  background-color: #F44336; /* 离线的 */
}
@media (max-width: 600px) {
  body {
    padding: 10px;
  }
  .monitor-list li {
    padding: 10px;
  }
}
</style>
</head>
<body>

<?php if(isset($error)): ?>
<!-- 错误信息 -->
<div class="error"><?php echo '发生错误：' . htmlspecialchars($error); ?></div>
<?php else: ?>
<ul class="monitor-list">
  <?php foreach ($monitors as $monitor): ?>
    <?php
          // 状态
    $status = $monitor['status'] == 2 ? '在线' : '离线';
    $statusColor = $monitor['status'] == 2 ? '#4CAF50' : '#F44336';
    $statusSymbol = $monitor['status'] == 2 ? '✔️' : '❌';
           //百分比
    $uptimeRatio = number_format($monitor['custom_uptime_ratio'], 2) . '%';
    ?>
    <li><?php echo htmlspecialchars($monitor['friendly_name']) . " - <span style='color: $statusColor;'>$status $statusSymbol</span> $uptimeRatio"; ?></li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<div style="text-align: center; margin-top: 10px;">© 2021-2024 SCAXE生存斧 300s更新数据</div>

</body>
</html>
