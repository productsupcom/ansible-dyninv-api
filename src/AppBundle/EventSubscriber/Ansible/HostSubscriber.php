<?php

namespace AppBundle\EventSubscriber\Ansible;

use ApiPlatform\Core\EventListener\EventPriorities;
use AppBundle\Entity\Ansible\Host;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class HostSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['updateHost', EventPriorities::PRE_WRITE]],
        ];
    }

    public function updateHost(GetResponseForControllerResultEvent $event)
    {
        $host = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$host instanceof Host || Request::METHOD_PUT !== $method) {
            return;
        }

        $host->setUpdated(new \DateTime("now"));

        $event->setControllerResult($host);
    }
}