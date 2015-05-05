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

    public function getTemplateInstances()
    {
        return $this->templateInstances;
    }

    public function __construct()
    {
        parent::__construct();
        // your own logic
        $this->templateInstances = new ArrayCollection();
    }
}
