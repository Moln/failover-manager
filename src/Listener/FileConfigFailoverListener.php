<?php
namespace Moln\FailoverManager\Listener;

use Moln\FailoverManager\ResourceEvent;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\EventManager\EventManagerInterface;

/**
 * Write a file, when connection error.
 *
 * @author  moln.xie@gmail.com
 */
class FileConfigFailoverListener extends AbstractListenerAggregate
{
    protected $file;

    public function __construct(array $options = [])
    {
        isset($options['file']) && $this->setFile($options['file']);
    }

    /**
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;
        return $this;
    }

    /**
     * Attach one or more listeners
     *
     * Implementors may add an optional $priority argument; the EventManager
     * implementation will pass this to the aggregate.
     *
     * @param EventManagerInterface $events
     * @param int $priority
     *
     * @return void
     */
    public function attach(EventManagerInterface $events, $priority = 1)
    {
        $events->attach(ResourceEvent::EVENT_CONNECTION_PRE, [$this, 'onPrepare'], $priority);
        $events->attach(ResourceEvent::EVENT_CONNECTION_ERROR, [$this, 'onError'], $priority);
    }

    public function onPrepare(ResourceEvent $event)
    {
        $file = $this->getFile();
        if ($file && file_exists($file)) {
            $keys = json_decode(file_get_contents($file), true);

            if (is_array($keys)) {
                return $keys;
            }
        }
    }

    public function onError(ResourceEvent $event)
    {
        $key  = $event->getParam('key');
        $keys = [];
        $file = $this->getFile();

        if (file_exists($file)) {
            $keys = json_decode(file_get_contents($file), true) ?: $keys;
        }

        $keys[] = $key;
        $keys = array_unique($keys);
        file_put_contents($file, json_encode($keys), LOCK_EX);
    }
}
