<?php

namespace Moln\FailoverManager\Adapter;

use Redis;

/**
 * Class RedisResource
 *
 * @package Moln\FailoverManager\Adapter
 * @author  moln.xie@gmail.com
 *
 * @method \Redis getResource()
 */
class RedisAdapter extends AbstractResourceAdapter
{
    protected $libOptions = [];

    /** @var Redis */
    protected $resource;

    public function __construct(array $options = [])
    {
        if (!extension_loaded('redis')) {
            throw new \RuntimeException("Redis extension is not loaded");
        }

        parent::__construct($options);
    }

    /**
     * Connects to redis server
     *
     *
     * @param array & $resource
     *
     * @return null
     * @throws Exception\ConnectionException
     */
    public function connect()
    {
        if ($this->resource) {
            return $this;
        }

        $redis  = new Redis();
        $server = $this->getServer();

        if ($server['persistent_id']) {
            //connect or reuse persistent connection
            $success = $redis->pconnect($server['host'], $server['port'], $server['timeout'], $server['persistent_id']);
        } elseif ($server['port']) {
            $success = $redis->connect($server['host'], $server['port'], $server['timeout']);
        } elseif ($server['timeout']) {
            //connect through unix domain socket
            $success = $redis->connect($server['host'], $server['timeout']);
        } else {
            $success = $redis->connect($server['host']);
        }

        if (!$success) {
            throw new Exception\ConnectionException('Could not estabilish connection with Redis instance');
        }

        if ($server['password']) {
            $redis->auth($server['password']);
        }

        if (!empty($server['auth'])) {
            $redis->auth($server['auth']);
        }

        if ($server['database']) {
            $redis->select($server['database']);
        }

        foreach ($this->getLibOptions() as $k => $v) {
            $redis->setOption($k, $v);
        }

        $this->resource = $redis;
        return $this;
    }


    /**
     * Set Redis options
     *
     * @param array $libOptions
     * @return $this
     */
    public function setLibOptions(array $libOptions)
    {
        $this->libOptions = $libOptions;

        if ($this->resource) {
            foreach ($libOptions as $key => $value) {
                $this->resource->setOption($key, $value);
            }
        }

        return $this;
    }

    /**
     * Get Redis options
     *
     * @return array
     */
    public function getLibOptions()
    {
        return $this->libOptions;
    }

    public function getDefaultServerConfig()
    {
        return [
            'host'          => null,
            'port'          => 6379,
            'timeout'       => 0,
            'password'      => null,
            'database'      => null,
            'persistent_id' => null,
        ];
    }
}
