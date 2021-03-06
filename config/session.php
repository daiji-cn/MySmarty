<?php
// session设置
return [
    // 开启状态，1 总是开启 2 根据情况按需开启
    'status' => 2,
    /**
     * session存储位置
     * 空：使用默认的php存放
     * file：存放在runtime目录下
     * mysql：存放在mysql
     * redis：存放在redis
     * elasticsearch：存放在elasticsearch
     * memcached：存放在memcached
     * mongodb:存放在mongodb
     */
    'type' => '',
    'session_type_params' => [
        // mysql session配置
        'mysql' => [
            // 数据库
            'database' => 'test',
            // 缓存表
            'table' => 'session'
        ],
        // redis session配置
        'redis' => [
            // 使用第几个库（0 - 15）
            'db' => 0
        ],
        // elasticsearch session配置
        'elasticsearch' => [
            // 默认 数据库，索引
            'database' => 'mysession',
            // 默认 表，文档
            'table' => 'session'
        ],
        'mongodb' => [
            // 默认 数据库，索引
            'database' => 'mysession',
            // 默认 表，文档
            'table' => 'session'
        ]
    ],
    // Cookie 的 生命周期，以秒为单位。
    'lifetime' => 3600,
    // 此 cookie 的有效 路径。 on the domain where 设置为“/”表示对于本域上所有的路径此 cookie 都可用。
    'path' => '/',
    // Cookie 的作用 域。 例如：“www.php.net”。 如果要让 cookie 在所有的子域中都可用，此参数必须以点（.）开头，例如：“.php.net”。
    'domain' => '',
    // 设置为 TRUE 表示 cookie 仅在使用 安全 链接时可用。
    'secure' => false,
    // 设置为 TRUE 表示 PHP 发送 cookie 的时候会使用 httponly 标记。
    'httponly' => true
];