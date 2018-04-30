
# 东软42乐乐视频站 爬虫
抓取东软42乐乐视频站所有视频数据，包括视频文件，视频简介，及其他相关页面数据，视频下载到本地后使用 avconv 转码为标清Mp4格式

2015-11-8

##运行

执行安装脚本,创建必要的目录及数据表
```
$ bash install.sh
```

运行蜘蛛守护进程
```
$ php5 spider-daemon.php
```

# 存储地址样例

电影专栏	/dyzl/..[42]
电视剧专栏	/dsjzl/sgnb/sgnb25.mp4
动画专栏	/dhzl/hmjzzfczx[42].mp4
高清专栏	/gqzl/xltfn[42].mp4
经典视频	/jdsp/yidongmigong[42].mp4


