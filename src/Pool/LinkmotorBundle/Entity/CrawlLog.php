<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * CrawlLog
 *
 * @ORM\Table(name="crawl_log",
 *     indexes={
 *         @ORM\Index(name="created_at_idx", columns={"created_at"}),
 *         @ORM\Index(name="url_ok_idx", columns={"url_ok"}),
 *         @ORM\Index(name="type_ok_idx", columns={"type_ok"}),
 *         @ORM\Index(name="anchor_ok_idx", columns={"anchor_ok"}),
 *         @ORM\Index(name="follow_ok_idx", columns={"follow_ok"}),
 *         @ORM\Index(name="xpath_ok_idx", columns={"xpath_ok"}),
 *     })
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\CrawlLogRepository")
 */
class CrawlLog
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
     * @ORM\Column(name="url_ok", type="boolean", nullable=true)
     */
    protected $urlOk;

    /**
     * @ORM\Column(name="xpath", type="text", nullable=true)
     */
    protected $xPath;

    /**
     * @ORM\Column(name="xpath_ok", type="boolean", nullable=true)
     */
    protected $xPathOk;

    /**
     * @ORM\Column(name="type", type="string", length=1, nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(name="type_ok", type="boolean")
     */
    protected $typeOk;

    /**
     * @ORM\Column(name="anchor", type="string", length=255, nullable=true)
     */
    protected $anchor;

    /**
     * @ORM\Column(name="anchor_ok", type="boolean")
     */
    protected $anchorOk;

    /**
     * @ORM\Column(name="follow", type="boolean", nullable=true)
     */
    protected $follow;

    /**
     * @ORM\Column(name="follow_ok", type="boolean")
     */
    protected $followOk;

    /**
     * @ORM\Column(name="status_code", type="integer", nullable=true)
     */
    protected $statusCode;

    /**
     * @ORM\Column(name="status_code_ok", type="boolean")
     */
    protected $statusCodeOk;
      
    /**
     * @ORM\Column(name="meta_index", type="boolean", nullable=true)
     */
    protected $metaIndex;

    /**
     * @ORM\Column(name="meta_index_ok", type="boolean")
     */
    protected $metaIndexOk;

    /**
     * @ORM\Column(name="meta_follow", type="boolean", nullable=true)
     */
    protected $metaFollow;

    /**
     * @ORM\Column(name="meta_follow_ok", type="boolean")
     */
    protected $metaFollowOk;

    /**
     * @ORM\Column(name="x_robots_index", type="boolean", nullable=true)
     */
    protected $xRobotsIndex;

    /**
     * @ORM\Column(name="x_robots_index_ok", type="boolean")
     */
    protected $xRobotsIndexOk;

    /**
     * @ORM\Column(name="x_robots_follow", type="boolean", nullable=true)
     */
    protected $xRobotsFollow;

    /**
     * @ORM\Column(name="x_robots_follow_ok", type="boolean")
     */
    protected $xRobotsFollowOk;

    /**
     * @ORM\Column(name="robots_google", type="boolean", nullable=true)
     */
    protected $robotsGoogle;

    /**
     * @ORM\Column(name="robots_google_ok", type="boolean")
     */
    protected $robotsGoogleOk;

    /**
     * @ORM\Column(name="crawl_type", type="string", length=4)
     */
    protected $crawlType;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="Backlink", inversedBy="crawlLog")
     * @ORM\JoinColumn(name="backlink_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $backlink;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="crawlLog")
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
     * Set urlOk
     *
     * @param boolean $urlOk
     * @return CrawlLog
     */
    public function setUrlOk($urlOk)
    {
        $this->urlOk = $urlOk;
    
        return $this;
    }

    /**
     * Get urlOk
     *
     * @return boolean 
     */
    public function getUrlOk()
    {
        return $this->urlOk;
    }

    /**
     * Set xPath
     *
     * @param string $xPath
     * @return CrawlLog
     */
    public function setXPath($xPath)
    {
        $this->xPath = $xPath;
    
        return $this;
    }

    /**
     * Get xPath
     *
     * @return string 
     */
    public function getXPath()
    {
        return $this->xPath;
    }

    /**
     * Set type
     *
     * @param string $type
     * @return CrawlLog
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

    /**
     * Set typeOk
     *
     * @param boolean $typeOk
     * @return CrawlLog
     */
    public function setTypeOk($typeOk)
    {
        $this->typeOk = $typeOk;
    
        return $this;
    }

    /**
     * Get typeOk
     *
     * @return boolean 
     */
    public function getTypeOk()
    {
        return $this->typeOk;
    }

    /**
     * Set anchor
     *
     * @param string $anchor
     * @return CrawlLog
     */
    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
    
        return $this;
    }

    /**
     * Get anchor
     *
     * @return string 
     */
    public function getAnchor()
    {
        return $this->anchor;
    }

    /**
     * Set anchorOk
     *
     * @param boolean $anchorOk
     * @return CrawlLog
     */
    public function setAnchorOk($anchorOk)
    {
        $this->anchorOk = $anchorOk;
    
        return $this;
    }

    /**
     * Get anchorOk
     *
     * @return boolean 
     */
    public function getAnchorOk()
    {
        return $this->anchorOk;
    }

    /**
     * Set follow
     *
     * @param boolean $follow
     * @return CrawlLog
     */
    public function setFollow($follow)
    {
        $this->follow = $follow;
    
        return $this;
    }

    /**
     * Get follow
     *
     * @return boolean 
     */
    public function getFollow()
    {
        return $this->follow;
    }

    /**
     * Set followOk
     *
     * @param boolean $followOk
     * @return CrawlLog
     */
    public function setFollowOk($followOk)
    {
        $this->followOk = $followOk;
    
        return $this;
    }

    /**
     * Get followOk
     *
     * @return boolean 
     */
    public function getFollowOk()
    {
        return $this->followOk;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     * @return CrawlLog
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
     * @return CrawlLog
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
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return CrawlLog
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
     * Set xPathOk
     *
     * @param boolean $xPathOk
     * @return CrawlLog
     */
    public function setXPathOk($xPathOk)
    {
        $this->xPathOk = $xPathOk;
    
        return $this;
    }

    /**
     * Get xPathOk
     *
     * @return boolean 
     */
    public function getXPathOk()
    {
        return $this->xPathOk;
    }

    /**
     * Set crawlType
     *
     * @param string $crawlType
     * @return CrawlLog
     */
    public function setCrawlType($crawlType)
    {
        $this->crawlType = $crawlType;
    
        return $this;
    }

    /**
     * Get crawlType
     *
     * @return string 
     */
    public function getCrawlType()
    {
        return $this->crawlType;
    }

    /**
     * Set metaIndexOk
     *
     * @param boolean $metaIndexOk
     * @return CrawlLog
     */
    public function setMetaIndexOk($metaIndexOk)
    {
        $this->metaIndexOk = $metaIndexOk;
    
        return $this;
    }

    /**
     * Get metaIndexOk
     *
     * @return boolean 
     */
    public function getMetaIndexOk()
    {
        return $this->metaIndexOk;
    }

    /**
     * Set metaFollowOk
     *
     * @param boolean $metaFollowOk
     * @return CrawlLog
     */
    public function setMetaFollowOk($metaFollowOk)
    {
        $this->metaFollowOk = $metaFollowOk;
    
        return $this;
    }

    /**
     * Get metaFollowOk
     *
     * @return boolean 
     */
    public function getMetaFollowOk()
    {
        return $this->metaFollowOk;
    }

    /**
     * Set xRobotsIndexOk
     *
     * @param boolean $xRobotsIndexOk
     * @return CrawlLog
     */
    public function setXRobotsIndexOk($xRobotsIndexOk)
    {
        $this->xRobotsIndexOk = $xRobotsIndexOk;
    
        return $this;
    }

    /**
     * Get xRobotsIndexOk
     *
     * @return boolean 
     */
    public function getXRobotsIndexOk()
    {
        return $this->xRobotsIndexOk;
    }

    /**
     * Set xRobotsFollowOk
     *
     * @param boolean $xRobotsFollowOk
     * @return CrawlLog
     */
    public function setXRobotsFollowOk($xRobotsFollowOk)
    {
        $this->xRobotsFollowOk = $xRobotsFollowOk;
    
        return $this;
    }

    /**
     * Get xRobotsFollowOk
     *
     * @return boolean 
     */
    public function getXRobotsFollowOk()
    {
        return $this->xRobotsFollowOk;
    }

    /**
     * Set robotsGoogleOk
     *
     * @param boolean $robotsGoogleOk
     * @return CrawlLog
     */
    public function setRobotsGoogleOk($robotsGoogleOk)
    {
        $this->robotsGoogleOk = $robotsGoogleOk;
    
        return $this;
    }

    /**
     * Get robotsGoogleOk
     *
     * @return boolean 
     */
    public function getRobotsGoogleOk()
    {
        return $this->robotsGoogleOk;
    }

    /**
     * Set statusCode
     *
     * @param integer $statusCode
     * @return CrawlLog
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
    
        return $this;
    }

    /**
     * Get statusCode
     *
     * @return integer 
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set statusCodeOk
     *
     * @param boolean $statusCodeOk
     * @return CrawlLog
     */
    public function setStatusCodeOk($statusCodeOk)
    {
        $this->statusCodeOk = $statusCodeOk;
    
        return $this;
    }

    /**
     * Get statusCodeOk
     *
     * @return boolean 
     */
    public function getStatusCodeOk()
    {
        return $this->statusCodeOk;
    }

    /**
     * Set metaIndex
     *
     * @param boolean $metaIndex
     * @return CrawlLog
     */
    public function setMetaIndex($metaIndex)
    {
        $this->metaIndex = $metaIndex;
    
        return $this;
    }

    /**
     * Get metaIndex
     *
     * @return boolean 
     */
    public function getMetaIndex()
    {
        return $this->metaIndex;
    }

    /**
     * Set metaFollow
     *
     * @param boolean $metaFollow
     * @return CrawlLog
     */
    public function setMetaFollow($metaFollow)
    {
        $this->metaFollow = $metaFollow;
    
        return $this;
    }

    /**
     * Get metaFollow
     *
     * @return boolean 
     */
    public function getMetaFollow()
    {
        return $this->metaFollow;
    }

    /**
     * Set xRobotsIndex
     *
     * @param boolean $xRobotsIndex
     * @return CrawlLog
     */
    public function setXRobotsIndex($xRobotsIndex)
    {
        $this->xRobotsIndex = $xRobotsIndex;
    
        return $this;
    }

    /**
     * Get xRobotsIndex
     *
     * @return boolean 
     */
    public function getXRobotsIndex()
    {
        return $this->xRobotsIndex;
    }

    /**
     * Set xRobotsFollow
     *
     * @param boolean $xRobotsFollow
     * @return CrawlLog
     */
    public function setXRobotsFollow($xRobotsFollow)
    {
        $this->xRobotsFollow = $xRobotsFollow;
    
        return $this;
    }

    /**
     * Get xRobotsFollow
     *
     * @return boolean 
     */
    public function getXRobotsFollow()
    {
        return $this->xRobotsFollow;
    }

    /**
     * Set robotsGoogle
     *
     * @param boolean $robotsGoogle
     * @return CrawlLog
     */
    public function setRobotsGoogle($robotsGoogle)
    {
        $this->robotsGoogle = $robotsGoogle;
    
        return $this;
    }

    /**
     * Get robotsGoogle
     *
     * @return boolean 
     */
    public function getRobotsGoogle()
    {
        return $this->robotsGoogle;
    }
}
