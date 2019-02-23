# 影视CMS

## 简介
使用 `slim` 框架构建一个电影站，数据抓取来自各大资源站，调用他们的数据接口当做数据源

## 项目说明

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
* 数据库采用 `illuminate/database`
* 这个项目不会用模板相关的东西
* `src` 目录下的 `Controller` 文件夹，用来存放前端接口，所有 `Controlelr` 都会继承 `BaseController`
* `redis` 会用来做缓存以及一些任务投递相关
* `src` 目录下的 `Task` 文件夹，用来存放异步任务的具体实现，所有 `Task` 都会继承 `BaskTask`
* `src` 目录下的 `Model` 文件夹，用来存放模型，所有 `Model` 都会继承 `illuminate/database` 中的 `Model`
* 前端认证准备采用 `jwt` ，后续准备在中间件中加上自动续期的操作，放入到 `cookie` 中，然后前端更新token

## 使用说明

* 使用 php console/application.php jwt:generateOctString 生成 jwk 的 key，设置到 settings 的 jwtSignatureKey 中 
