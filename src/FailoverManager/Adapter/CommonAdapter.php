<?php

namespace Moln\FailoverManager\Adapter;


/**
 * Common socket
 *
 * @package Moln\FailoverManager\Adapter
 * @author  moln.xie@gmail.com
 */
class CommonAdapter extends AbstractResourceAdapter
{
    protected $connectionCallback;
    protected $defaultServerConfig;

    public function connect()
    {
        $call = $this->connectionCallback;

        call_user_func($call, $this);
    }

    /**
     * @return mixed
     */
    public function getConnectionCallback()
    {
        return $this->connectionCallback;
    }

    /**
     * @param callable $connectionCallback
     * @return $this
     */
    public function setConnectionCallback(callable $connectionCallback)
    {
        $this->connectionCallback = $connectionCallback;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDefaultServerConfig()
    {
        return $this->defaultServerConfig ?: parent::getDefaultServerConfig();
    }

    /**
     * @param mixed $defaultServerConfig
     * @return $this
     */
    public function setDefaultServerConfig(array $defaultServerConfig)
    {
        $this->defaultServerConfig = $defaultServerConfig;
        return $this;
    }
}