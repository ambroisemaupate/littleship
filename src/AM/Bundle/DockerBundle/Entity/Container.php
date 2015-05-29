<?php

namespace AM\Bundle\DockerBundle\Entity;

use AM\Bundle\UserBundle\Entity\User;
use Doctrine\ORM\Mapping as ORM;

/**
 * Container
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Container
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="containerId", type="string", length=255, unique=true)
     */
    private $containerId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="configuration", type="text")
     */
    private $configuration;

    /**
     * @var boolean
     *
     * @ORM\Column(name="synced", type="boolean")
     */
    private $synced = false;

    /**
     * @var User
     * @ORM\ManyToOne(targetEntity="AM\Bundle\UserBundle\Entity\User", inversedBy="containers")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set containerId
     *
     * @param string $containerId
     * @return Container
     */
    public function setContainerId($containerId)
    {
        $this->containerId = $containerId;

        return $this;
    }

    /**
     * Get containerId
     *
     * @return string
     */
    public function getContainerId()
    {
        return $this->containerId;
    }

    /**
     * Set configuration
     *
     * @param string $configuration
     * @return Container
     */
    public function setConfiguration($configuration)
    {
        $this->configuration = $configuration;

        return $this;
    }

    /**
     * Get configuration
     *
     * @return string
     */
    public function getConfiguration()
    {
        return $this->configuration;
    }

    /**
     * Set synced
     *
     * @param boolean $synced
     * @return Container
     */
    public function setSynced($synced)
    {
        $this->synced = $synced;

        return $this;
    }

    /**
     * Get synced
     *
     * @return boolean
     */
    public function getSynced()
    {
        return $this->synced;
    }

    /**
     * Gets the value of user.
     *
     * @return User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Sets the value of user.
     *
     * @param User $user the user
     *
     * @return self
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Gets the value of name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the value of name.
     *
     * @param string $name the name
     *
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}
