<?php

namespace MolnTest\FailoverManager;


use Moln\FailoverManager\Adapter\CommonAdapter;
use Moln\FailoverManager\Adapter\RedisAdapter;
use Moln\FailoverManager\Adapter\ResourceInterface;
use Moln\FailoverManager\ResourceManager;

class ResourceManagerTest extends \PHPUnit_Framework_TestCase
{

    public function testGetHasSetResources()
    {
        $mockResource = new CommonAdapter([
            'connectionCallback' => function () {
                return true;
            },
        ]);

        $resources = new \Moln\FailoverManager\ResourceManager(
            [
                'resources' => [
                    'master' => [
                        'type' => 'redis',
                        'options' => [
                            'server' => 'tcp://192.168.39.18:6379?password=111'
                        ]
                    ],
                    'slave1' => [
                        'type' => 'redis',
                        'options' => [
                            'server' => 'tcp://192.168.39.18:6379?password=222'
                        ]
                    ],
                    'slave2' => $mockResource,
                ],
                'listeners' => [
                    new \Moln\FailoverManager\Listener\FileConfigFailoverListener(['file' => 'failover.json']),
                ]
            ]
        );

        $master = $resources->getResource('master');
        $this->assertEquals('master', $master->getName());
        $this->assertInstanceOf(RedisAdapter::class, $master);

        $slave2 = $resources->getResource('slave2');
        $this->assertEquals('slave2', $slave2->getName());
        $this->assertInstanceOf(CommonAdapter::class, $slave2);
    }
}
