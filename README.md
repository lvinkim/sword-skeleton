# sword-skeleton
sword 框架

## 安装

```
$ composer create-project lvinkim/sword-skeleton [my-app-name]
```

## 配置

```
$ cp .env.dist .env
```

## 启动

```
$ php bin/server.php

或使用 docker 运行

$ docker-compose up 

```


## 访问 

```
$ curl localhost:8080
{"app":"sword-skeleton"}
```