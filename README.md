
ZsFRAMEWORK
===============

其主要特性包括：

 + 采用单一入口
 + 支持路由解析
 + 支持自定义配置
 + 支持调试模式
 + 引入MYSQL ORM模型
 + 支持模板功能
 + 全局统一异常处理
 + 支持自定日志功能
 + 支持`Composer`自动加载规范
 + MongoDB 操作类
 + Validate 验证器
 + 前置中间件支持
 

 待完善部分：
  + 持续优化

------

更新日志：
 + 2019.6.14 v1.0
   - 第一版本
 + 2019.6.15 v1.1
   - 新增mongodb操作类
   - 部分功能优化
 + 2019.6.15 v1.1.1
      - 添加git忽略文件、忽略.ide和logs文件夹
      - 忽略 vendor文件夹 
 + 2019.6.16 v1.1.2
   - 优化MongoDB提示
   - 优化Config获取形式
   - 优化代码结构  
 + 2019.6.17 v1.2
   - 新增Validate验证类库
   - 优化调试信息
   - 优化部分代码
   - 新增助手函数
 + 2019.6.18 v1.2.1
   - 新增MongoDB模型映射和DB辅助类
   - 优化部分bug
 + 2019.6.19 v1.3
   - 新增中间件模块
   - 重写路由相关操作
   - 优化核心App类
   - 优化命名空间
 + 2019.6.19 v1.3.1
   - 新增异常类
   - 优化无法正常返回Json的Bug

> 运行环境要求PHP7.2以上。

------

## 目录结构

初始的目录结构如下：

~~~
www  WEB部署目录（或者子目录）
├─app           应用目录
│  ├─common             公共模块目录（可以更改）
│  ├─controllers        控制器目录
│  ├─models             模块目录
│  └─views              视图目录
│
│
├─config                应用配置目录
│  ├─module_name        模块配置目录
│  │  ├─database.php    数据库配置
│  │  ├─cache           缓存配置
│  │  └─ ...            
│  │
│  ├─app.php            应用配置
│  ├─cache.php          缓存配置
│  ├─database.php       数据库配置
│  ├─logger.php         日志配置
│  └─mongodb.php        MongoDB配置
│
├─framework             类库核心
│ ├── App.php
│ ├── Controller.php
│ ├── config
│ ├── exception
│ │   ├── ErrorException.php
│ │   ├── Handle.php
│ │   ├── HttpCode.php
│ │   ├── HttpException.php
│ │   ├── HttpResponseException.php
│ │   └── ThrowableError.php
│ ├── helpers.php       辅助函数
│ ├── model             模型相关
│ │   ├── DB.php
│ │   ├── LoadConnection.php
│ │   ├── Model.php
│ │   └── mongodb mongdb相关操作
│ │       ├── MongoDb.php
│ ├── router            路由实现
│ │   └── Router.php
│ ├── service           核心服务
│ │   ├── Cache.php
│ │   ├── Config.php
│ │   ├── Logger.php
│ │   ├── MongoDb.php
│ │   ├── Redis.php
│ │   ├── Request.php
│ │   ├── Response.php
│ │   └── response
│ │       └── Json.php
│ └── view              视图实现
│     └── View.php
│
├─logs                  日志目录
|
├─public                WEB目录（对外访问目录）
│  ├─index.php          入口文件
|
├─routes                路由定义目录
│  ├─web.php            路由定义
│  └─...                更多
|
├─vendor                第三方类库目录（Composer依赖库）
├─composer.json         composer 定义文件
├─README.md             README 文件
~~~

## 命名规范

`ZsFRAMEWORK`遵循PSR-2命名规范和PSR-4自动加载规范，并且注意如下规范：

### 目录和文件

*   目录不强制规范，驼峰和小写+下划线模式均支持；
*   类库、函数文件统一以`.php`为后缀；
*   类的文件名均以命名空间定义，并且命名空间的路径和类库文件所在路径一致；
*   类名和类文件名保持一致，统一采用驼峰法命名（首字母大写）；

### 函数和类、属性命名

*   类的命名采用驼峰法，并且首字母大写，例如 `User`、`UserType`，默认不需要添加后缀，例如`UserController`应该直接命名为`User`；
*   函数的命名使用小写字母和下划线（小写字母开头）的方式，例如 `get_client_ip`；
*   方法的命名使用驼峰法，并且首字母小写，例如 `getUserName`；
*   属性的命名使用驼峰法，并且首字母小写，例如 `tableName`、`instance`；
*   以双下划线“__”打头的函数或方法作为魔法方法，例如 `__call` 和 `__autoload`；

### 常量和配置

*   常量以大写字母和下划线命名，例如 `ROOT_PATH`和 `SYS_ENV`；
*   配置参数以小写字母和下划线命名，例如 `url_route_on` 和`url_convert`；

### 数据表和字段

*   数据表和字段采用小写加下划线方式命名，并注意字段名不要以下划线开头，例如 `test_user` 表和 `user_name`字段，不建议使用驼峰和中文作为数据表字段命名。