<?php

namespace Moln\FailoverManager\Adapter;

/**
 * Class AbstractResourceAdapter
 * @package Moln\FailoverManager\Adapter
 * @author moln.xie@gmail.com
 */
abstract class AbstractResourceAdapter implements ResourceInterface
{

    protected $name;

    protected $server;

    protected $resource;

    public function __construct(array $options = [])
    {
        $this->setOptions($options);
    }

    /**
     * Configure state
     *
     * @param  array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . str_replace(' ', '', ucwords(str_replace('_', ' ', $key)));
            if (method_exists($this, $method)) {
                $this->{$method}($value);
            }
        }

        return $this;
    }

    /**
     * Set a resource
     * @param $resource
     * @return $this
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
        return $this;
    }

    /**
     * Get a resource
     * @return mixed
     */
    public function getResource()
    {
        if (!$this->resource) {
            $this->connect();
        }
        return $this->resource;
    }

    /**
     * @return mixed
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * @param mixed $server
     * @return $this
     */
    public function setServer($server)
    {
        $this->normalizeServer($server);
        $this->server = $server;
        return $this;
    }

    /**
     * Get resource name.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set resource name.
     * @param string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Normalize one server into the following format:
     * array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     *
     * @param string|array $server
     */
    protected function normalizeServer(&$server)
    {
        $defaultConfig = $this->getDefaultServerConfig();
        if (is_array($server)) {
            // array('host' => <host>[, 'port' => <port>, ['timeout' => <timeout>]])
            $server = $server + $defaultConfig;
        } else {
            // parse server from URI, "tcp://127.0.0.1:1000/?timeout=0"
            $uri = parse_url(trim($server));

            if (isset($uri['query'])) {
                parse_str($uri['query'], $uriQuery);
                $uri += $uriQuery;
            }

            $server = $defaultConfig;
            foreach ($server as $key => $val) {
                if (isset($uri[$key])) {
                    $server[$key] = $uri[$key];
                }
            }
        }
    }


    /**
     * Default server config
     * @return array
     */
    protected function getDefaultServerConfig()
    {
        return [
            'host'    => null,
            'port'    => null,
            'timeout' => 0,
        ];
    }
}
