<?php

namespace AppBundle\Entity\Ansible;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use AppBundle\Entity\Ansible\Group;

/**
 * Host
 *
 * @ApiResource
 * @ORM\Table(name="host")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Ansible\HostRepository")
 * @ORM\HasLifecycleCallbacks
 */

class Host
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="fqdn", type="string", length=255, unique=true, nullable=true)
     */
    private $fqdn;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=39, unique=true, nullable=true)
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="hostname", type="string", length=255, unique=true, nullable=false)
     */
    private $hostname;

    /**
     * @var string
     *
     * @ORM\Column(name="domain", type="string", length=255, unique=true, nullable=true)
     */
    private $domain;

    /**
     * @var array
     *
     * @ORM\Column(name="variables", type="json_array", nullable=true)
     */
    private $variables;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean", options={"default":true})
     */
    private $enabled;

    /**
     * @var ArrayCollection|Group[]
     *
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="hosts", cascade={"persist"})
     * @ORM\JoinTable(name="hostgroups",
     *      joinColumns={@ORM\JoinColumn(name="host_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="group_id", referencedColumnName="id")}
     *      )
     */
    private $groups;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime")
     */
    private $updated;

    public function __construct() {
        $this->groups = new ArrayCollection();
        $this->created = new \DateTime("now");
        $this->updated = new \DateTime("now");
        $this->enabled = true;
    }

    private function doStuffOnPreUpdate(PreUpdateEventArgs $event)
    {
        //var_dump($event->getEntityChangeSet());
        //if (!$event->hasChangedField('enabled')) {
            $this->updated = new \DateTime("now");
        //}
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId() : int
    {
        return $this->id;
    }

    /**
     * Set FQDN
     *
     * @param string $host
     * @return Host
     */
    public function setFqdn(string $fqdn) : self
    {
        $this->fqdn = $fqdn;

        return $this;
    }

    /**
     * Get FQDN
     *
     * @return string
     */
    public function getFqdn() : string
    {
        return $this->fqdn;
    }

    /**
     * Set hostname
     *
     * @param string $hostname
     * @return Host
     */
    public function setHostname(string $hostname) : self
    {
        $this->hostname = $hostname;

        return $this;
    }

    /**
     * Get hostname
     *
     * @return string
     */
    public function getHostname() : string
    {
        return $this->hostname;
    }

    /**
     * Set variables
     *
     * @param array $variables
     * @return Host
     */
    public function setVariables(array $variables) : self
    {
        $this->variables = $variables;

        return $this;
    }

    /**
     * Get variables
     *
     * @return array
     */
    public function getVariables() : ?array
    {
        return $this->variables;
    }

    /**
     * Set enabled
     *
     * @param boolean $enabled
     * @return Host
     */
    public function setEnabled(bool $enabled) : self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Get enabled
     *
     * @return boolean
     */
    public function getEnabled() : bool
    {
        return $this->enabled;
    }

    /**
     * Get groups
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Set groups
     *
     * @param array $groups
     */
    public function setGroups(array $groups) : self
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * Add groups
     *
     * @param Group $group
     */
    public function addGroup(Group $group) : self
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
        }

        return $this;
    }

    /**
     * Set ip
     *
     * @param string $ip
     * @return IP
     */
    public function setIp(string $ip) : self
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * Get ip
     *
     * @return string
     */
    public function getIp() : ?string
    {
        return $this->ip;
    }

    /**
     * Set domain
     *
     * @param string $domain
     * @return Domain
     */
    public function setDomain(string $domain) : self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Get domain
     *
     * @return string
     */
    public function getDomain() : ?string
    {
        return $this->domain;
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return Host
     */
    public function setCreated(\DateTime $created) : self
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated() : \DateTime
    {
        return $this->created;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return Host
     */
    public function setUpdated(\DateTime $updated) : self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated() : \DateTime
    {
        return $this->updated;
    }
}
