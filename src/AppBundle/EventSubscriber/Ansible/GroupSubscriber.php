<?php

namespace AppBundle\EventSubscriber\Ansible;

use ApiPlatform\Core\EventListener\EventPriorities;
use AppBundle\Entity\Ansible\Group;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final class GroupSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => [['updateGroup', EventPriorities::PRE_WRITE]],
        ];
    }

    public function updateGroup(GetResponseForControllerResultEvent $event)
    {
        $group = $event->getControllerResult();
        $method = $event->getRequest()->getMethod();

        if (!$group instanceof Group || Request::METHOD_PUT !== $method) {
            return;
        }

        $group->setUpdated(new \DateTime("now"));

        $event->setControllerResult($group);
    }
}