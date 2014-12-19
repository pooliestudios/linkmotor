<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Alert
 *
 * @ORM\Table(name="alerts",
 *     indexes={
 *         @ORM\Index(name="created_at_idx", columns={"created_at"}),
 *         @ORM\Index(name="hide_until_idx", columns={"hide_until"})
 *     })
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\AlertRepository")
 */
class Alert
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="type", type="string", length=1)
     */
    protected $type;

    /**
     * @ORM\Column(name="hide_until", type="datetime", nullable=true)
     */
    protected $hideUntil;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Backlink", inversedBy="alerts")
     * @ORM\JoinColumn(name="backlink_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $backlink;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="alerts")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="alerts")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
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
     * Set type
     *
     * @param string $type
     * @return Alert
     */
    public function setType($type)
    {
        $this->type = $type;
    
        return $this;
    }

    /**
     * Get type
     *
     * @return string 
     */
    public function getType()
    {
        return $this->type;
    }

    public function getTypeForView()
    {
        if ($this->type == 'e') {
            return 'uk-alert-danger';
        }
        if ($this->type == 'w') {
            return 'uk-alert-warning';
        }
    }

    /**
     * Set hideUntil
     *
     * @param \DateTime $hideUntil
     * @return Alert
     */
    public function setHideUntil($hideUntil)
    {
        $this->hideUntil = $hideUntil;
    
        return $this;
    }

    /**
     * Get hideUntil
     *
     * @return \DateTime 
     */
    public function getHideUntil()
    {
        return $this->hideUntil;
    }

    public function isCurrentlyHidden()
    {
        if (!$this->getHideUntil()) {
            return false;
        }

        return $this->getHideUntil()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s');
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Alert
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;
    
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set backlink
     *
     * @param \Pool\LinkmotorBundle\Entity\Backlink $backlink
     * @return Alert
     */
    public function setBacklink(Backlink $backlink = null)
    {
        $this->backlink = $backlink;
    
        return $this;
    }

    /**
     * Get backlink
     *
     * @return \Pool\LinkmotorBundle\Entity\Backlink
     */
    public function getBacklink()
    {
        return $this->backlink;
    }

    /**
     * Set user
     *
     * @param \Pool\LinkmotorBundle\Entity\User $user
     * @return Alert
     */
    public function setUser(User $user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Pool\LinkmotorBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Alert
     */
    public function setProject(Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return \Pool\LinkmotorBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
