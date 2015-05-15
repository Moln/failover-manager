# Failover manager
Resource failover manager

## Installation using Composer

```
{
    "require": {
        "moln/failover-manager": "1.*"
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
