<?php

namespace AppBundle\Controller\Ansible;

use AppBundle\Entity\Ansible\Host;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Doctrine\ORM\EntityManager;

class HostController extends Controller
{
    private $em;
    private $request;

    public function __construct(EntityManager $em, RequestStack $RequestStack)
    {
        $this->em = $em;
        $this->request = $RequestStack->getCurrentRequest();
    }

    public function specialAction()
    {
        $data = [];
        $hosts = $this->em->getRepository('AppBundle\Entity\Ansible\Host')->findAll();
        foreach ($hosts as $host) {
            if ($host->getAnsibleHost() === $this->request->get('ansibleHost')) {
                if (count($host->getVariables()) > 0) {
                    $data = $host->getVariables();
                }
            }
        }

        $response = new JsonResponse();
        $response->setData($data);
        return $response;
    }
}
