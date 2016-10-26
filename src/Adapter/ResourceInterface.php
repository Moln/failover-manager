<?php
namespace Moln\FailoverManager\Adapter;

interface ResourceInterface
{

    /**
     * Connect resource
     * @return ResourceInterface
     */
    public function connect();

    /**
     *
     * @return mixed
     */
    public function getResource();

    /**
     * Get server config
     * @return array
     */
    public function getServer();

    /**
     * Get resource name
     * @return string
     */
    public function getName();

    /**
     * Set resource name
     * @param string
     */
    public function setName($name);
}
