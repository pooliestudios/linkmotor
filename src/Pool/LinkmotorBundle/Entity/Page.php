<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Page
 *
 * @ORM\Table(name="pages")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\PageRepository")
 * @Gedmo\Loggable
 */
class Page
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
     * @ORM\Column(name="url", type="string", length=255)
     * @Gedmo\Versioned
     */
    protected $url;

    /**
     * @ORM\Column(name="authority", type="integer")
     */
    protected $authority;

    /**
     * @ORM\Column(name="link_pop", type="integer", nullable=true)
     */
    protected $linkPop;

    /**
     * @ORM\Column(name="domain_pop", type="integer", nullable=true)
     */
    protected $domainPop;

    /**
     * @ORM\Column(name="twitter_count", type="integer", nullable=true)
     */
    protected $twitterCount;

    /**
     * @ORM\Column(name="facebook_count", type="integer", nullable=true)
     */
    protected $facebookCount;

    /**
     * @ORM\Column(name="gplus_count", type="integer", nullable=true)
     */
    protected $gplusCount;

    /**
     * @ORM\Column(name="scheme", type="string", length=5)
     * @Gedmo\Versioned
     */
    protected $scheme;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Versioned
     */
    protected $createdAt;

    /**
     * @ORM\Column(name="last_modified_at", type="datetime")
     */
    protected $lastModifiedAt;

    /**
     * @ORM\Column(name="last_crawled_at", type="datetime", nullable=true)
     * @Gedmo\Versioned
     */
    protected $lastCrawledAt;

    /**
     * @ORM\ManyToOne(targetEntity="Subdomain", inversedBy="pages")
     * @ORM\JoinColumn(name="subdomain_id", referencedColumnName="id")
     */
    protected $subdomain;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="pages")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Gedmo\Versioned
     */
    protected $assignedTo;

    /**
     * @ORM\OneToMany(targetEntity="Backlink", mappedBy="page")
     * @ORM\OrderBy({"url" = "ASC"})
     */
    protected $backlinks;

    /**
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="pages")
     * @ORM\JoinColumn(name="status_id", referencedColumnName="id")
     * @Gedmo\Versioned
     */
    protected $status;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="pages")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="Competitor", inversedBy="pages")
     * @ORM\JoinColumn(name="source_competitor_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $sourceCompetitor;

    /**
     * @ORM\ManyToOne(targetEntity="Keyword", inversedBy="pages")
     * @ORM\JoinColumn(name="source_keyword_id", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $sourceKeyword;

    /**
     * @ORM\OneToMany(targetEntity="PageNote", mappedBy="page")
     * @ORM\OrderBy({"createdAt" = "ASC"})
     */
    protected $notes;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAuthority(0);
        $this->setDomainPop(null);
        $this->setLinkPop(null);
        $this->setCreatedAt(new \DateTime());
        $this->setLastModifiedAt($this->getCreatedAt());
        $this->backlinks = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set url
     *
     * @param string $url
     * @return Page
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return string
     */
    public function getFull()
    {
        return $this->getScheme() . '://' . $this->getFullWithoutScheme();
    }

    /**
     * @return string
     */
    public function getFullWithoutScheme()
    {
        return $this->getSubdomain()->getFull() . $this->getUrl();
    }

    /**
     * @return string
     */
    public function getFullWithoutSchemeForDisplay()
    {
        return $this->getSubdomain()->getFullForDisplay() . $this->getUrl();
    }

    /**
     * @return string
     */
    public function getFullForDisplay()
    {
        return $this->getFullWithoutSchemeForDisplay();
    }

    /**
     * Set subdomain
     *
     * @param \Pool\LinkmotorBundle\Entity\Subdomain $subdomain
     * @return Page
     */
    public function setSubdomain(\Pool\LinkmotorBundle\Entity\Subdomain $subdomain = null)
    {
        $this->subdomain = $subdomain;
    
        return $this;
    }

    /**
     * Get subdomain
     *
     * @return \Pool\LinkmotorBundle\Entity\Subdomain
     */
    public function getSubdomain()
    {
        return $this->subdomain;
    }

    /**
     * Set authority
     *
     * @param integer $authority
     * @return Page
     */
    public function setAuthority($authority)
    {
        $this->authority = $authority;
    
        return $this;
    }

    /**
     * Get authority
     *
     * @return integer 
     */
    public function getAuthority()
    {
        return $this->authority;
    }

    /**
     * Add backlinks
     *
     * @param \Pool\LinkmotorBundle\Entity\Backlink $backlinks
     * @return Page
     */
    public function addBacklink(\Pool\LinkmotorBundle\Entity\Backlink $backlinks)
    {
        $this->backlinks[] = $backlinks;
    
        return $this;
    }

    /**
     * Remove backlinks
     *
     * @param \Pool\LinkmotorBundle\Entity\Backlink $backlinks
     */
    public function removeBacklink(Backlink $backlinks)
    {
        $this->backlinks->removeElement($backlinks);
    }

    /**
     * Get backlinks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBacklinks()
    {
        return $this->backlinks;
    }

    public function hasBacklink()
    {
        return !$this->getBacklinks()->isEmpty();
    }

    /**
     * @param Project $project
     * @param int $excludeBacklinkId
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getBacklinksForProject(Project $project, $excludeBacklinkId = 0)
    {
        $backlinksInProject = array();
        foreach ($this->backlinks as $backlink) {
            if ($backlink->getProject() == $project && $backlink->getId() != $excludeBacklinkId) {
                $backlinksInProject[] = $backlink;
            }
        }

        return $backlinksInProject;
    }

    /**
     * Set assignedTo
     *
     * @param \Pool\LinkmotorBundle\Entity\User $assignedTo
     * @return Page
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
     * Set status
     *
     * @param \Pool\LinkmotorBundle\Entity\Status $status
     * @return Page
     */
    public function setStatus(Status $status = null)
    {
        $this->status = $status;
    
        return $this;
    }

    /**
     * Get status
     *
     * @return \Pool\LinkmotorBundle\Entity\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    public function getStatusMayBeChangedByUser(User $user)
    {
        return !in_array($this->getStatus()->getId(), array(6, 7))
                && (
                    $user->getId() == $this->getAssignedTo()->getId()
                    || $user->isAdmin()
                );
    }

    /**
     * Set scheme
     *
     * @param string $scheme
     * @return Page
     */
    public function setScheme($scheme)
    {
        $this->scheme = $scheme;
    
        return $this;
    }

    /**
     * Get scheme
     *
     * @return string 
     */
    public function getScheme()
    {
        if (!$this->scheme) {
            $this->scheme = 'http';
        }

        return $this->scheme;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return Page
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
     * Set lastCrawledAt
     *
     * @param \DateTime $lastCrawledAt
     * @return Page
     */
    public function setLastCrawledAt($lastCrawledAt)
    {
        $this->lastCrawledAt = $lastCrawledAt;
    
        return $this;
    }

    /**
     * Get lastCrawledAt
     *
     * @return \DateTime 
     */
    public function getLastCrawledAt()
    {
        return $this->lastCrawledAt;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Page
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
     * Set sourceCompetitor
     *
     * @param \Pool\LinkmotorBundle\Entity\Competitor $sourceCompetitor
     * @return Page
     */
    public function setSourceCompetitor(\Pool\LinkmotorBundle\Entity\Competitor $sourceCompetitor = null)
    {
        $this->sourceCompetitor = $sourceCompetitor;
    
        return $this;
    }

    /**
     * Get sourceCompetitor
     *
     * @return \Pool\LinkmotorBundle\Entity\Competitor
     */
    public function getSourceCompetitor()
    {
        return $this->sourceCompetitor;
    }

    /**
     * Set sourceKeyword
     *
     * @param \Pool\LinkmotorBundle\Entity\Keyword $sourceKeyword
     * @return Page
     */
    public function setSourceKeyword(\Pool\LinkmotorBundle\Entity\Keyword $sourceKeyword = null)
    {
        $this->sourceKeyword = $sourceKeyword;
    
        return $this;
    }

    /**
     * Get sourceKeyword
     *
     * @return \Pool\LinkmotorBundle\Entity\Keyword
     */
    public function getSourceKeyword()
    {
        return $this->sourceKeyword;
    }

    /**
     * Set lastModifiedAt
     *
     * @param \DateTime $lastModifiedAt
     * @return Page
     */
    public function setLastModifiedAt($lastModifiedAt)
    {
        $this->lastModifiedAt = $lastModifiedAt;
    
        return $this;
    }

    /**
     * Get lastModifiedAt
     *
     * @return \DateTime 
     */
    public function getLastModifiedAt()
    {
        return $this->lastModifiedAt;
    }

    /**
     * Add notes
     *
     * @param \Pool\LinkmotorBundle\Entity\PageNote $notes
     * @return Page
     */
    public function addNote(\Pool\LinkmotorBundle\Entity\PageNote $notes)
    {
        $this->notes[] = $notes;
    
        return $this;
    }

    /**
     * Remove notes
     *
     * @param \Pool\LinkmotorBundle\Entity\PageNote $notes
     */
    public function removeNote(\Pool\LinkmotorBundle\Entity\PageNote $notes)
    {
        $this->notes->removeElement($notes);
    }

    /**
     * Get notes
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * @return Note
     */
    public function getLastNote()
    {
        return $this->getNotes()->last();
    }

    /**
     * Set linkPop
     *
     * @param integer $linkPop
     * @return Page
     */
    public function setLinkPop($linkPop)
    {
        $this->linkPop = $linkPop;
    
        return $this;
    }

    /**
     * Get linkPop
     *
     * @return integer 
     */
    public function getLinkPop()
    {
        return $this->linkPop;
    }

    /**
     * Set domainPop
     *
     * @param integer $domainPop
     * @return Page
     */
    public function setDomainPop($domainPop)
    {
        $this->domainPop = $domainPop;
    
        return $this;
    }

    /**
     * Get domainPop
     *
     * @return integer 
     */
    public function getDomainPop()
    {
        return $this->domainPop;
    }

    /**
     * Set twitterCount
     *
     * @param integer $twitterCount
     * @return Page
     */
    public function setTwitterCount($twitterCount)
    {
        $this->twitterCount = $twitterCount;
    
        return $this;
    }

    /**
     * Get twitterCount
     *
     * @return integer 
     */
    public function getTwitterCount()
    {
        return $this->twitterCount;
    }

    /**
     * Set facebookCount
     *
     * @param integer $facebookCount
     * @return Page
     */
    public function setFacebookCount($facebookCount)
    {
        $this->facebookCount = $facebookCount;
    
        return $this;
    }

    /**
     * Get facebookCount
     *
     * @return integer 
     */
    public function getFacebookCount()
    {
        return $this->facebookCount;
    }

    /**
     * Set gplusCount
     *
     * @param integer $gplusCount
     * @return Page
     */
    public function setGplusCount($gplusCount)
    {
        $this->gplusCount = $gplusCount;
    
        return $this;
    }

    /**
     * Get gplusCount
     *
     * @return integer 
     */
    public function getGplusCount()
    {
        return $this->gplusCount;
    }
}
