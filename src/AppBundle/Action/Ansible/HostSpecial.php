<?php

namespace AppBundle\Action\Ansible;

use AppBundle\Entity\Ansible\Host;
use AppBundle\Entity\Ansible\Group;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class HostSpecial
{
    private $em;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * @Route(
     *     name="ansible_host_special",
     *     path="/api/inventory/{ansible_host}",
     *     defaults={"_api_resource_class"=Host::class, "_api_item_operation_name"="special"}
     * )
     * @Method("GET")
     */
    public function __invoke($ansible_host) //Request $request)
    {
        var_dump($ansible_host);
//        $data = [];

//        $groups = $this->em->getRepository('AppBundle\Entity\Ansible\Host')->findAll();

        return $data;
    }
}

