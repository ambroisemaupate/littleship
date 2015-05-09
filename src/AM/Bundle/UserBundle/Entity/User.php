<?php
// src/Acme/UserBundle/Entity/User.php

namespace AM\Bundle\UserBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\OneToMany(targetEntity="AM\Bundle\DockerBundle\Entity\TemplateInstance", mappedBy="user")
     */
    protected $templateInstances = null;

    /**
     * @ORM\Column(type="integer", name="instance_max_count", unique=false, nullable=true)
     */
    protected $instanceMaxCount = 0;


    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->templateInstances = new ArrayCollection();
    }

    /**
     * Gets the value of instanceMaxCount.
     *
     * @return mixed
     */
    public function getInstanceMaxCount()
    {
        return $this->instanceMaxCount;
    }

    /**
     * Sets the value of instanceMaxCount.
     *
     * @param mixed $instanceMaxCount the instance max count
     *
     * @return self
     */
    public function setInstanceMaxCount($instanceMaxCount)
    {
        $this->instanceMaxCount = (int) $instanceMaxCount;

        return $this;
    }

    /**
     * Gets the value of templateInstances.
     *
     * @return mixed
     */
    public function getTemplateInstances()
    {
        return $this->templateInstances;
    }

    /**
     * Sets the value of templateInstances.
     *
     * @param mixed $templateInstances the template instances
     *
     * @return self
     */
    public function setTemplateInstances($templateInstances)
    {
        $this->templateInstances = $templateInstances;

        return $this;
    }
}
