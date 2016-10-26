<?php

namespace Moln\FailoverManager;

use Moln\FailoverManager\Adapter\Exception\ConnectionException;
use Moln\FailoverManager\Adapter\ResourceInterface;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;


/**
 *
 * @package Mztgame\FailoverManager
 * @author  moln.xie@gmail.com
 */
class ResourceManager implements EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /** @var \Zend\EventManager\ListenerAggregateInterface[] */
    protected $listeners = [];

    public function __construct(array $options = [])
    {
        isset($options['resources']) && $this->setResources($options['resources']);

        if (isset($options['listeners']) && is_array($options['listeners'])) {
            $this->listeners = $options['listeners'];
        }
    }

    protected function attachDefaultListeners()
    {
        foreach ($this->listeners as $listener) {
            $listener->attach($this->getEventManager());
        }
    }

    /**
     * Registered resources
     *
     * @var Adapter\AbstractResourceAdapter[]
     */
    protected $resources = [];

    /**
     * Check if a resource exists
     *
     * @param string $id
     * @return bool
     */
    public function hasResource($id)
    {
        return isset($this->resources[$id]);
    }

    /**
     * Gets a resource
     *
     * @param string $id
     * @return ResourceInterface
     * @throws ConnectionException
     */
    public function getResource($id)
    {
        if (!$this->hasResource($id)) {
            throw new \RuntimeException("No resource with id '{$id}'");
        }

        return $this->resources[$id];
    }

    /**
     * Get a resource server
     *
     * @param string $id
     * @throws \RuntimeException
     * @return array array('host' => <host>[, 'port' => <port>[, 'timeout' => <timeout>]])
     */
    public function getServer($id)
    {
        if (!$this->hasResource($id)) {
            throw new \RuntimeException("No resource with id '{$id}'");
        }

        $resource = &$this->resources[$id];
        return $resource->getServer();
    }

    /**
     * Set a resource
     *
     * @param string $id
     * @param array|Adapter\ResourceInterface $resource
     * @return self Fluent interface
     */
    public function setResource($id, $resource)
    {
        if (is_array($resource)) {
            $resource = $this->createResourceFromArray($resource);
        }

        if (!$resource instanceof ResourceInterface) {
            throw new \RuntimeException('Error resource id: ' . $id);
        }

        $resource->setName($id);

        $this->resources[$id] = $resource;
        return $this;
    }

    /**
     * Remove a resource
     *
     * @param string $id
     * @return self Fluent interface
     */
    public function removeResource($id)
    {
        unset($this->resources[$id]);
        return $this;
    }

    /**
     * Get resources
     *
     * @return Adapter\AbstractResourceAdapter[]
     */
    public function getResources()
    {
        return $this->resources;
    }

    /**
     * Set resources
     * @param array $resources
     * @return $this
     */
    public function setResources(array $resources)
    {
        foreach ($resources as $key => $resource) {
            $this->setResource($key, $resource);
        }

        return $this;
    }

    /**
     * Create resource factory.
     *
     * @param array $config
     * @return ResourceInterface
     */
    private function createResourceFromArray($config)
    {
        $config = $config + ['type' => null, 'options' => []];

        $className = __NAMESPACE__ . '\\Adapter\\' . ucfirst($config['type']) . 'Adapter';

        if (!class_exists($className)) {
            throw new \RuntimeException('Create resource error,type: ' . $config['type']);
        }

        /** @var ResourceInterface $resource */
        return new $className($config['options']);
    }

    /**
     * @return Adapter\AbstractResourceAdapter
     */
    public function getRandomResource()
    {
        $keys = array_keys($this->resources);
        shuffle($keys);

        return $this->resourceLoopConnect($keys);
    }

    /**
     * @return Adapter\AbstractResourceAdapter
     */
    public function getCurrentResource()
    {
        $keys = array_keys($this->resources);

        return $this->resourceLoopConnect($keys);
    }

    /**
     * @param array $keys
     * @return Adapter\AbstractResourceAdapter
     */
    protected function resourceLoopConnect(array $keys)
    {
        $resource = null;
        $event    = new ResourceEvent();
        $event->setTarget($this);
        $event->setParam('keys', $keys);

        $event->setName(ResourceEvent::EVENT_CONNECTION_PRE);
        $results = $this->getEventManager()->triggerEvent($event);
        if (is_array($results->last())) {
            $keys = array_diff($keys, $results->last());
        }

        foreach ($keys as $key) {
            $event->setParam('key', $key);
            $event->setParam('resource', $this->getResource($key));

            try {
                $resource = $this->getResource($key)->connect();

                $event->setName(ResourceEvent::EVENT_CONNECTION_SUCCESS);
                $this->getEventManager()->triggerEvent($event);
                break;
            } catch (ConnectionException $e) {
                $event->setParam('exception', $e);

                $event->setName(ResourceEvent::EVENT_CONNECTION_ERROR);
                $results = $this->getEventManager()->triggerEvent($event);
                $last    = $results->last();

                if ($last instanceof ResourceInterface) {
                    $resource = $last->connect();
                    break;
                }
            }
        }

        if (!$resource) {
            throw new \RuntimeException('All server is broken.', 0, $event->getParam('exception'));
        }

        return $resource;
    }
}
