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
}
