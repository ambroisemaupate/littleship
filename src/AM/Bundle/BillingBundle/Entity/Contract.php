<?php

namespace AM\Bundle\BillingBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use AM\Bundle\BillingBundle\Entity\Contact;
use AM\Bundle\UserBundle\Entity\User;

/**
 * Contract
 *
 * @ORM\Table()
 * @ORM\Entity
 */
class Contract
{
    const ANNUAL = 1;
    const QUARTERLY = 2;
    const BIMONTHLY = 3;
    const MONTHLY = 4;
    const WEEKLY = 5;

    public static $typeToHuman = [
        Contract::ANNUAL => 'Annual',
        Contract::QUARTERLY => 'Quaterly',
        Contract::BIMONTHLY => 'Bimonthly',
        Contract::MONTHLY => 'Monthly',
        Contract::WEEKLY => 'Weekly',
    ];

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount = 0.00;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="startedAt", type="datetime")
     */
    private $startedAt;

    /**
     * @var string
     *
     * @ORM\Column(name="currency", type="string", length=4)
     */
    private $currency;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="AM\Bundle\BillingBundle\Entity\Contact", inversedBy="contracts")
     * @ORM\JoinColumn(name="contact", referencedColumnName="id")
     */
    protected $contact;

    /**
     * @ORM\ManyToOne(targetEntity="AM\Bundle\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

    /**
     *
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->type = static::ANNUAL;
        $this->currency = 'EUR';
        $this->startedAt = new \Datetime('now');
    }

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
     * Set amount
     *
     * @param float $amount
     * @return Contract
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set startedAt
     *
     * @param \DateTime $startedAt
     * @return Contract
     */
    public function setStartedAt($startedAt)
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    /**
     * Get startedAt
     *
     * @return \DateTime
     */
    public function getStartedAt()
    {
        return $this->startedAt;
    }

    /**
     * Set currency
     *
     * @param string $currency
     * @return Contract
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;

        return $this;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return Contract
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Gets the value of contact.
     *
     * @return Contact
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * Sets the value of contact.
     *
     * @param Contact $contact the contact
     *
     * @return self
     */
    public function setContact(Contact $contact)
    {
        $this->contact = $contact;

        return $this;
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
    public function setUser(User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get nomarlized amount on one year.
     *
     * @return float
     */
    public function getNormalizedAmount()
    {
        switch ($this->type) {
            case static::ANNUAL:
                return $this->amount;
                break;
            case static::QUARTERLY:
                return $this->amount * 4;
                break;
            case static::BIMONTHLY:
                return $this->amount * 6;
                break;
            case static::MONTHLY:
                return $this->amount * 12;
                break;
            case static::WEEKLY:
                return $this->amount * 52.1775;
                break;

            default:
                return $this->amount;
                break;
        }
    }

    /**
     * @return string
     */
    public function getHumanType()
    {
        return static::$typeToHuman[$this->type];
    }
}
