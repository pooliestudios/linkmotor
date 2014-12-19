<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Competitor
 *
 * @ORM\Table(name="competitors")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\CompetitorRepository")
 */
class Competitor
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
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="competitors")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */
    protected $domain;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="competitors")
     * @ORM\JoinColumn(name="assigned_to_user_id", referencedColumnName="id")
     */
    protected $assignedTo;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="competitors")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="sourceCompetitor")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $pages;

    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
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
     * Set importLimit
     *
     * @param integer $importLimit
     * @return Competitor
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
     * @return Competitor
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
     * Set assignedTo
     *
     * @param \Pool\LinkmotorBundle\Entity\User $assignedTo
     * @return Competitor
     */
    public function setAssignedTo(User $assignedTo = null)
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
     * @return Competitor
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

    /**
     * Set lastImportAt
     *
     * @param \DateTime $lastImportAt
     * @return Competitor
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
     * @return Competitor
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
     * Add pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     * @return Competitor
     */
    public function addPage(Page $pages)
    {
        $this->pages[] = $pages;
    
        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     */
    public function removePage(Page $pages)
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
     * Set domain
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $domain
     * @return Competitor
     */
    public function setDomain(\Pool\LinkmotorBundle\Entity\Domain $domain = null)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return \Pool\LinkmotorBundle\Entity\Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
