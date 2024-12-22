# 贴吧云签到每日签到完成情况自定义消息推送

[MoeNetwork/Tieba-Cloud-Sign: 百度贴吧云签到](https://github.com/MoeNetwork/Tieba-Cloud-Sign)平台可用的贴吧云签到每日签到完成情况使用HTTP请求推送

小白魔改了一下CrazyCodingConclave大佬的代码：

[CrazyCodingConclave/tieba_sign_pushplus: 贴吧云签到每日签到完成情况PushPlus推送插件](https://github.com/CrazyCodingConclave/tieba_sign_pushplus)

再次感谢CrazyCodingConclave大佬。

# 使用方法

1. 打包下载项目文件夹，重命名文件夹名称为`tieba_sign_msghttp`

2. 上传到`贴吧云签到`项目路径的`/plugins`文件夹下，`Docker`项目可以用下面的命令主把机上的文件上传到容器中

   ```bash
   docker cp /tieba_sign_msghttp tieba-cloud-sign-web-1:/var/www/plugins
   ```

3. 在`贴吧云签到`后台`插件管理`中选中`自定义消息推送`插件，点击`安装插件`，然后`激活插件`

4. 在`贴吧云签到`后台`个人设置`中设置`自定义消息推送`为`是`，填入自己的`HTTP API请求回调url`，然后选择`推送时间`，提交更改即可。
