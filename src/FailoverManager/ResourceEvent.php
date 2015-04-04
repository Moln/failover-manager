<?php

namespace Moln\FailoverManager;

use Zend\EventManager\Event;


class ResourceEvent extends Event
{
    const EVENT_CONNECTION_ERROR   = 'resource.connection.error';
    const EVENT_CONNECTION_SUCCESS = 'resource.connection.success';
    const EVENT_CONNECTION_PRE     = 'resource.connection.pre';
    const EVENT_CONNECTION_POST    = 'resource.connection.post';
}