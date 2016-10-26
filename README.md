Failover manager
================

[![Build Status](https://travis-ci.org/moln/failover-manager.png)](https://travis-ci.org/moln/failover-manager)
[![Latest Stable Version](https://poser.pugx.org/moln/failover-manager/v/stable.png)](https://packagist.org/packages/moln/failover-manager)

Resource failover manager
资源故障转移管理器


## 安装Installation using Composer

```
{
    "require": {
        "moln/failover-manager": "^1.0"
    }
}
```

## Example - 使用举例


```php
include '../vendor/autoload.php';

$resources = new \Moln\FailoverManager\ResourceManager(
    [
        'resources' => [
            'master' => [
                'type' => 'redis',
                'options' => [
                    'server' => 'tcp://192.168.177.10:6379?password=111'
                ]
            ],
            'slave1' => [
                'type' => 'redis',
                'options' => [
                    'server' => 'tcp://192.168.177.10:6379?password=222'
                ]
            ],
        ],
        'listeners' => [
            new \Moln\FailoverManager\Listener\FileConfigFailoverListener(['file' => 'failover.json']),
        ]
    ]
);

$resource = $resources->getRandomResource();
/** @var \Redis $redis */
$redis = $resource->getResource();

print_r($redis->info());
print_r($resource->getServer());
```
