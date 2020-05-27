<?php
/**
 * 自定义命令
 * key 命令名
 * value 路由（模块/控制器/方法/参数一/参数一值）
 * 示例，控制台执行 php mysmarty test
 * 'test' => 'home/index/test'
 * 控制器请勿继承SmartyController
 */
return [
    'test' => 'home/index/test',
    'test1' => 'home/index/test1/a/1/b/2'
];