<?php

namespace AppBundle\Entity\Ansible;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\PersistentCollection;
use AppBundle\Entity\Ansible\Host;

/**
 * Group
 *
 * @ApiResource(collectionOperations={
 *     "get"={"method"="GET"},
 *     "post"={"method"="POST"},
 *     "special"={
 *         "route_name"="ansible_group_special",
 *         "normalization_context"={"groups"={"inventory"}}
 *     }
 * })
 * @ORM\Table(name="`ansible_group`")
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
     * @ORM\Column(name="name", type="string", unique=true, length=255)
     * @Groups({"inventory"})
     */
    private $name;

    /**
     * @var array
     *
     * @ORM\Column(name="`variables`", type="json_array", nullable=true)
     * @Groups({"inventory"})
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
     * @Groups({"inventory"})
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
     * @ORM\JoinTable(name="ansible_childgroups",
     *      joinColumns={@ORM\JoinColumn(name="child_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="parent_id", referencedColumnName="id")}
     *      )
     */
    private $parentGroups;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime", options={"default": 0})
     */
    private $created;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated", type="datetime", options={"default": 0})
     */
    private $updated;

    public function __construct() {
        $this->created = new \DateTime("now");
        $this->updated = new \DateTime("now");
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

    /**
     * Get child-groups
     *
     * @return array
     */
    public function getChildGroups()
    {
        return $this->childGroups;
    }

    /**
     * Add child-group
     *
     * @param Group $group
     */
    public function addChildGroup(Group $group) : self
    {
        if ($this->childGroups->contains($group)) {
            return $this;
        }

        $this->childGroups->add($group);
        $group->addParentGroup($this);

        return $this;
    }

    /**
     * Remove child-group
     *
     * @param Host $host
     */
    public function removeChildGroup(Group $group) : self
    {
        if (!$this->childGroups->contains($group)) {
            return $this;
        }

        $this->childGroups->removeElement($group);
        $group->removeParentGroup($this);

        return $this;
    }

    /**
     * Get parent-groups
     *
     * @return array
     */
    public function getParentGroups()
    {
        return $this->parentGroups;
    }

    /**
     * Add parent-group
     *
     * @param Group $group
     */
    public function addParentGroup(Group $group) : self
    {
        if ($this->parentGroups->contains($group)) {
            return $this;
        }

        $this->parentGroups->add($group);
        //$host->addGroup($this);

        return $this;
    }

    /**
     * Remove parent-group
     *
     * @param Host $host
     */
    public function removeParentGroup(Group $group) : self
    {
        if (!$this->parentGroups->contains($group)) {
            return $this;
        }

        $this->parentGroups->removeElement($group);
        //$host->removeGroup($this);

        return $this;
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
