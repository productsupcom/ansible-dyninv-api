<?php

namespace AppBundle\Entity\Ansible;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use AppBundle\Entity\Ansible\Group;
use AppBundle\Entity\Ansible\Host;

/**
 * Group
 *
 * @ApiResource
 * @ORM\Table(name="`group`")
 * @ORM\Entity(repositoryClass="AppBundle\Entity\Ansible\GroupRepository")
 */
class Group
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
     * @Assert\Type(type="string")
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="`variables`", type="json_array", nullable=true)
     */
    private $variables;

    /**
     * @var boolean
     *
     * @ORM\Column(name="enabled", type="boolean")
     */
    private $enabled;


    /**
     * @var ArrayCollection|Host[]
     * @ORM\ManyToMany(targetEntity="Host", mappedBy="groups", cascade={"persist"})
     */
    private $hosts;

    /**
     * @var ArrayCollection|Group[]
     * @ORM\ManyToMany(targetEntity="Group", mappedBy="parentGroups")
     */
    private $childGroups;

    /**
     * @var ArrayCollection|Group[]
     * @ORM\ManyToMany(targetEntity="Group", inversedBy="childGroups")
     * @ORM\JoinTable(name="childgroups",
     *      joinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")}
     *      )
     */
    private $parentGroups;

    public function __construct() {
        //throw new Exception('foo');
        $this->hosts = new ArrayCollection();
        $this->childGroups = new ArrayCollection();
        $this->parentGroups = new ArrayCollection();
        $this->enabled = true;
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
     * Set name
     *
     * @param string $name
     * @return Group
     */
    public function setName(string $name) : self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->name;
    }

    /**
     * Set variables
     *
     * @param array $variables
     * @return Group
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
     * @return Group
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
     * Get hosts
     *
     * @return array
     */
    public function getHosts()
    {
        return $this->hosts;
    }

    /**
     * Add host
     *
     * @param Host $host
     */
    public function addHost(Host $host) : self
    {
        if ($this->hosts->contains($host)) {
            return $this;
        }

        $this->hosts->add($host);
        $host->addGroup($this);

        return $this;
    }

    /**
     * Remove host
     *
     * @param Host $host
     */
    public function removeHost(Host $host) : self
    {
        if (!$this->hosts->contains($host)) {
            return $this;
        }

        $this->hosts->removeElement($host);
        $host->removeGroup($this);

        return $this;
    }
}
