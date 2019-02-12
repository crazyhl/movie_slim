# 电影网站不知道第几版

## 简介
使用 `slim` 框架构建一个电影站，数据抓取来自各大资源站，调用他们的数据接口当做数据源

## 说明

* 基础框架 `slim`
* 项目创建自 `slim/slim-skeleton`
* 配置项目 `namespace autoload` 
```
"autoload": {
        "psr-4": {
            "App\\": "src/"
        }
    },
```
* 创建目录 `console` 用来存放异步任务入口
* `src` 目录下的 `Command` 文件夹，用来存放具体的任务文件
