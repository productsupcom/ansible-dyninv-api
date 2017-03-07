<?php

namespace AppBundle\Action\Ansible;

use AppBundle\Entity\Ansible\Host;
use AppBundle\Entity\Ansible\Group;
use Doctrine\Common\Persistence\ManagerRegistry;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;

class GroupSpecial
{
    private $em;
    private $meta;

    public function __construct(\Doctrine\ORM\EntityManager $em)
    {
        $this->em = $em;
        $this->meta = [];
    }

    /**
     * @Route(
     *     name="ansible_group_special",
     *     path="/api/inventory",
     *     defaults={"_api_resource_class"=Group::class, "_api_collection_operation_name"="special"}
     * )
     * @Method("GET")
     */
    public function __invoke()
    {
        $data = [];
        $groups = $this->em->getRepository('AppBundle\Entity\Ansible\Group')->findAll();
        foreach ($groups as $group) {
            $hosts = [];
            foreach ($group->getHosts() as $host) {
                if (count($host->getVariables()) > 0) {
                    $this->meta[$host->getAnsibleHost()] = $host->getVariables();
                }
                $hosts[] = $host->getAnsibleHost();
            }

            $groupdata = [];
            $groupdata["hosts"] = $hosts;
            if (count($group->getVariables()) > 0) {
                $groupdata["vars"] = $group->getVariables();
            }

            if (count($group->getChildGroups()) > 0) {
                foreach ($group->getChildGroups() as $childGroup) {
                    $groupdata["children"][] = $childGroup->getName();
                }
            }

            $data[$group->getName()] = $groupdata;
        }

        if (count($this->meta) > 0) {
            $data["_meta"]["hostvars"] = $this->meta;
        }

        return $data;
    }
}

