<?php
$apiKey = '写你的api';
$cacheFile = 'auth/api_cache.json';
$cacheTime = 300; // 缓存时间秒

// 检查缓存文件未过期
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    // 读取数据
    $responseData = json_decode(file_get_contents($cacheFile), true);
} else {
    // 缓存文件已过期，请求API
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

    // 保存到缓存文件
    $responseData = json_decode($response, true);
    if ($responseData['stat'] === 'ok') {
        file_put_contents($cacheFile, $response);
    }
}

// 检查是否有错误
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
<title>SCAXE-状态监控</title>
<style>
body {
  font-family: 'Roboto', sans-serif;
  background-color: #e9ecef;
  color: #495057;
  margin: 0;
  padding: 20px;
}

header {
  background-color: #007bff;
  color: #fff;
  padding: 10px 0;
  text-align: center;
  font-size: 24px;
}

.error {
  color: #dc3545;
  margin: 20px 0;
}

.monitor-list {
  max-width: 600px;
  margin: 20px auto;
  padding: 0;
}

.monitor-list li {
  background-color: #fff;
  border-left: 5px solid #28a745; /* 默认在线绿 */
  border-radius: 0.25rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  margin-bottom: 1rem;
  padding: 0.75rem 1rem;
  list-style-type: none;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.monitor-list li.offline {
  border-left-color: #ffc107; /* 离线黄 */
}

.monitor-list li.offline .progress-bar {
  background-color: #dc3545; /* 离线进度条红 */
}

.monitor-list li:hover {
  transform: translateY(-0.25rem);
  box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.status {
  border-radius: 1rem;
  padding: 0.25rem 0.75rem;
  font-size: 0.875rem;
  font-weight: 500;
}

.online {
  background-color: #28a745; 
}

.offline {
  background-color: #dc3545; 
}

/* 进度条样式 */
.progress {
  background-color: #ddd;
  border-radius: 1rem;
  overflow: hidden;
  margin-top: 0.5rem;
}

.progress-bar {
  height: 1rem;
  border-radius: 1rem;
  transition: width 0.6s ease;
}

/* 总体状态样式 */
.overall-status {
  background-color: #fff;
  border-left: 5px solid #28a745; /* 默认在线绿 */
  border-radius: 0.25rem;
  box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
  margin-bottom: 1rem;
  padding: 0.75rem 1rem;
  list-style-type: none;
  font-size: 1.25rem; /* 更大的字体 */
}

.overall-status.online {
  color: #28a745; /* 在线绿 */
  padding: 0.75rem 1rem;
}

.overall-status.offline {
  border-left-color: #ffc107; /* 离线黄 */
  color: #ffc107; /* 离线黄 */
  padding: 0.75rem 1rem;
}

footer {
  text-align: center;
  padding: 2rem 0;
  font-size: 0.875rem;
}

@media (max-width: 768px) {
  body {
    padding: 10px;
  }

  .monitor-list {
    width: 100%;
  }
}

</style>
</head>
<body>

<?php if(isset($error)): ?>
<!-- 显示错误信息 -->
<div class="error"><?php echo '发生错误：' . htmlspecialchars($error); ?></div>
<?php else: ?>
<!-- 总体状态框 -->
<?php
// 检查所有服务是否在线
$all_services_online = true;
foreach ($monitors as $monitor) {
    if ($monitor['status'] != 2) { // 有任何服务不是在线状态
        $all_services_online = false;
        break;
    }
}
?>

<?php if ($all_services_online): ?>
<ul class="monitor-list">
    <li class="overall-status online">
        ✔️ 所有服务正常！
    </li>
<?php else: ?>
    <li class="overall-status offline">
        ⚠️ 部分服务故障！
    </li>
<?php endif; ?>


  <?php foreach ($monitors as $monitor): ?>
    <?php
    // 在线状态显示
    $status = $monitor['status'] == 2 ? '在线' : '离线';
    $statusColor = $monitor['status'] == 2 ? '#4CAF50' : '#F44336';
    $statusSymbol = $monitor['status'] == 2 ? '✔️' : '❌';
    // 计算在线百分比
    $uptimeRatio = number_format($monitor['custom_uptime_ratio'], 2);
    // 设置进度条颜色
    $progressBarColor = $monitor['status'] == 2 ? '#4CAF50' : '#dc3545'; // 在线状态绿，离线状态红
    ?>
    <li class="<?php echo $monitor['status'] == 2 ? 'online' : 'offline'; ?>">
      <?php echo htmlspecialchars($monitor['friendly_name']) . " - <span style='color: $statusColor;'>$status $statusSymbol</span>"; ?>
      <div class="progress">
        <div class="progress-bar" style="width: <?php echo $uptimeRatio; ?>%; background-color: <?php echo $progressBarColor; ?>;"></div>
      </div>
      <?php echo $uptimeRatio . '%'; ?>
    </li>
  <?php endforeach; ?>
</ul>
<?php endif; ?>
<div style="text-align: center; margin-top: 10px;">© 2021-2024 <a href="https://www.axe.ink" style="color:black;">SCAXE生存斧</a> 300s更新数据</div>

</body>
</html>
