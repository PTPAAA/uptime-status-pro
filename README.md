# uptime-status-pro
利用uptimerobot的api实现在线服务项目状态监测
[![image](https://github.com/PTPAAA/uptime-status-pro/blob/main/B370F58B1D16032AD4418EBCE51242DF.jpg)
# 如何部署？
下载仓库内api_cache.json和index.php文件，在php环境中运行，填入uptimerobot dashboard的api密钥即可运行
# 已经有类似项目了，为什么还要写个这个？
类似项目使用js代码存放apikey，而apikey被拿到后再被不怀好意的人部署uptime-status可能会暴露被检测服务器的IP，而php项目则不会有这种问题，此项目也可以在vercel上运行部署(已经测试)
# 千万注意！一定要设置好url规则阻止api_cache.json被访问，否则安全性形同虚设！
