<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Keyword
 *
 * @ORM\Table(name="keywords")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\KeywordRepository")
 */
class Keyword
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
     * @var string
     * @ORM\Column(name="keyword", type="string", length=255)
     * @Assert\NotBlank()
     */
    protected $keyword;

    /**
     * @var integer
     * @ORM\Column(name="import_limit", type="integer")
     */
    protected $importLimit;

    /**
     * @var integer
     * @ORM\Column(name="import_interval", type="integer")
     */
    protected $importInterval;

    /**
     * @var \DateTime
     * @ORM\Column(name="last_import_at", type="datetime", nullable=true)
     */
    protected $lastImportAt;

    /**
     * @var \DateTime
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="keywords")
     * @ORM\JoinColumn(name="assigned_to_user_id", referencedColumnName="id")
     */
    protected $assignedTo;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="keywords")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="sourceKeyword")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $pages;

    /**
     * @ORM\ManyToOne(targetEntity="Market", inversedBy="keywords")
     * @ORM\JoinColumn(name="market_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $market;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setKeyword('');
        $this->setImportInterval(0); // no automatic import
        $this->setImportLimit(25); // 25 pages
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set keyword
     *
     * @param string $keyword
     * @return Keyword
     */
    public function setKeyword($keyword)
    {
        $this->keyword = $keyword;
    
        return $this;
    }

    /**
     * Get keyword
     *
     * @return string 
     */
    public function getKeyword()
    {
        return $this->keyword;
    }

    /**
     * Set importLimit
     *
     * @param integer $importLimit
     * @return Keyword
     */
    public function setImportLimit($importLimit)
    {
        $this->importLimit = $importLimit;
    
        return $this;
    }

    /**
     * Get importLimit
     *
     * @return integer 
     */
    public function getImportLimit()
    {
        return $this->importLimit;
    }

    /**
     * Set importInterval
     *
     * @param integer $importInterval
     * @return Keyword
     */
    public function setImportInterval($importInterval)
    {
        $this->importInterval = $importInterval;
    
        return $this;
    }

    /**
     * Get importInterval
     *
     * @return integer 
     */
    public function getImportInterval()
    {
        return $this->importInterval;
    }

    /**
     * Set lastImportAt
     *
     * @param \DateTime $lastImportAt
     * @return Keyword
     */
    public function setLastImportAt($lastImportAt)
    {
        $this->lastImportAt = $lastImportAt;
    
        return $this;
    }

    /**
     * Get lastImportAt
     *
     * @return \DateTime 
     */
    public function getLastImportAt()
    {
        return $this->lastImportAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Keyword
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
     * Set assignedTo
     *
     * @param \Pool\LinkmotorBundle\Entity\User $assignedTo
     * @return Keyword
     */
    public function setAssignedTo(\Pool\LinkmotorBundle\Entity\User $assignedTo = null)
    {
        $this->assignedTo = $assignedTo;
    
        return $this;
    }

    /**
     * Get assignedTo
     *
     * @return \Pool\LinkmotorBundle\Entity\User
     */
    public function getAssignedTo()
    {
        return $this->assignedTo;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Keyword
     */
    public function setProject(\Pool\LinkmotorBundle\Entity\Project $project = null)
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

    /**
     * Add pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     * @return Keyword
     */
    public function addPage(\Pool\LinkmotorBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;
    
        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     */
    public function removePage(\Pool\LinkmotorBundle\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set market
     *
     * @param \Pool\LinkmotorBundle\Entity\Market $market
     * @return Keyword
     */
    public function setMarket(\Pool\LinkmotorBundle\Entity\Market $market = null)
    {
        $this->market = $market;
    
        return $this;
    }

    /**
     * Get market
     *
     * @return \Pool\LinkmotorBundle\Entity\Market
     */
    public function getMarket()
    {
        return $this->market;
    }
}
