<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\ExecutionContextInterface;

/**
 * Backlink
 *
 * @ORM\Table(name="backlinks",
 *     indexes={
 *         @ORM\Index(name="created_at_idx", columns={"created_at"}),
 *         @ORM\Index(name="url_ok_idx", columns={"url_ok"}),
 *         @ORM\Index(name="type_ok_idx", columns={"type_ok"}),
 *         @ORM\Index(name="anchor_ok_idx", columns={"anchor_ok"}),
 *         @ORM\Index(name="follow_ok_idx", columns={"follow_ok"}),
 *     })
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\BacklinkRepository")
 * @Gedmo\Loggable
 * @Assert\Callback(methods={"doesBacklinkMatchProject"}, groups={"Default", "costTypeMoney"})
 */
class Backlink
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
     * @Assert\NotBlank(groups={"Default", "costTypeMoney"})
     * @ORM\Column(name="url", type="string", length=255)
     * @Gedmo\Versioned
     */
    protected $url;

    /**
     * @ORM\Column(name="url_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $urlOk;

    /**
     * @ORM\Column(name="xpath", type="text", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xPath;

    /**
     * @ORM\Column(name="xpath_last_crawl", type="text", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xPathLastCrawl;

    /**
     * @ORM\Column(name="xpath_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xPathOk;
    
    /**
     * "i": Image, "t": Text
     *
     * @ORM\Column(name="type", type="string", length=1)
     * @Gedmo\Versioned
     */
    protected $type;

    /**
     * "dom" or "text"
     * 
     * @ORM\Column(name="crawl_type", type="string", length=4)
     * @Gedmo\Versioned
     */
    protected $crawlType;

    /**
     * @ORM\Column(name="type_last_crawl", type="string", length=1, nullable=true)
     * @Gedmo\Versioned
     */
    protected $typeLastCrawl;

    /**
     * @ORM\Column(name="type_ok", type="smallint")
     * @Gedmo\Versioned
     */
    protected $typeOk;

    /**
     * @ORM\Column(name="anchor", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    protected $anchor;

    /**
     * @ORM\Column(name="anchor_last_crawl", type="string", length=255, nullable=true)
     * @Gedmo\Versioned
     */
    protected $anchorLastCrawl;

    /**
     * @ORM\Column(name="anchor_ok", type="smallint")
     * @Gedmo\Versioned
     */
    protected $anchorOk;

    /**
     * @ORM\Column(name="follow", type="smallint")
     * @Gedmo\Versioned
     */
    protected $follow;

    /**
     * @ORM\Column(name="follow_last_crawl", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $followLastCrawl;

    /**
     * @ORM\Column(name="follow_ok", type="smallint")
     * @Gedmo\Versioned
     */
    protected $followOk;

    /**
     * @Assert\NotBlank(groups={"Default", "costTypeMoney"})
     * @ORM\Column(name="status_code", type="string", length=3)
     * @Gedmo\Versioned
     */
    protected $statusCode;

    /**
     * @ORM\Column(name="status_code_last_crawl", type="string", length=3, nullable=true)
     * @Gedmo\Versioned
     */
    protected $statusCodeLastCrawl;

    /**
     * @ORM\Column(name="status_code_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $statusCodeOk;

    /**
     * @ORM\Column(name="meta_index", type="smallint")
     * @Gedmo\Versioned
     */
    protected $metaIndex;

    /**
     * @ORM\Column(name="meta_index_last_crawl", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $metaIndexLastCrawl;

    /**
     * @ORM\Column(name="meta_index_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $metaIndexOk;

    /**
     * @ORM\Column(name="meta_follow", type="smallint")
     * @Gedmo\Versioned
     */
    protected $metaFollow;

    /**
     * @ORM\Column(name="meta_follow_last_crawl", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $metaFollowLastCrawl;

    /**
     * @ORM\Column(name="meta_follow_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $metaFollowOk;

    /**
     * @ORM\Column(name="xrobots_follow", type="smallint")
     * @Gedmo\Versioned
     */
    protected $xRobotsFollow;

    /**
     * @ORM\Column(name="xrobots_follow_last_crawl", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xRobotsFollowLastCrawl;

    /**
     * @ORM\Column(name="xrobots_follow_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xRobotsFollowOk;

    /**
     * @ORM\Column(name="xrobots_index", type="smallint")
     * @Gedmo\Versioned
     */
    protected $xRobotsIndex;

    /**
     * @ORM\Column(name="xrobots_index_last_crawl", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xRobotsIndexLastCrawl;

    /**
     * @ORM\Column(name="xrobots_index_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $xRobotsIndexOk;

    /**
     * @ORM\Column(name="robots_google", type="smallint")
     * @Gedmo\Versioned
     */
    protected $robotsGoogle;

    /**
     * @ORM\Column(name="robots_google_last_crawl", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $robotsGoogleLastCrawl;

    /**
     * @ORM\Column(name="robots_google_ok", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $robotsGoogleOk;

    /**
     * @ORM\Column(name="is_offline", type="smallint")
     * @Gedmo\Versioned
     */
    protected $isOffline;

    /**
     * @ORM\Column(name="ignore_position", type="smallint")
     * @Gedmo\Versioned
     */
    protected $ignorePosition;

    /**
     * @ORM\Column(name="cost_type", type="smallint")
     * @Gedmo\Versioned
     */
    protected $costType;
    
    public static $costTypes = array(
        0 => 'None',
        1 => 'One-Time',
        2 => 'Monthly',
        3 => 'Annual',
        4 => 'Link exchange',
        5 => 'Other exchange'
    );

    /**
     * @ORM\Column(name="price", type="float")
     * @Assert\GreaterThan(groups={"costTypeMoney"}, value = 0)
     * @Gedmo\Versioned
     */
    protected $price;

    /**
     * @ORM\Column(name="cost_note", type="text", nullable=true)
     * @Gedmo\Versioned
     */
    protected $costNote;

    /**
     * @ORM\Column(name="last_crawled_at", type="datetime", nullable=true)
     */
    protected $lastCrawledAt;

    /**
     * @ORM\Column(name="created_at", type="datetime")
     * @Gedmo\Versioned
     */
    protected $createdAt;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="backlinks")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * @Gedmo\Versioned
     */
    protected $assignedTo;

    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="backlinks")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $page;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="backlinks")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\OneToMany(targetEntity="CrawlLog", mappedBy="backlink", cascade={"remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $crawlLog;

    /**
     * @ORM\OneToMany(targetEntity="Alert", mappedBy="backlink", cascade={"remove"})
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $alerts;

    public function __construct()
    {
        $this->setOffline(false);
        $this->setAnchor('');
        $this->setType('t');
        $this->setCrawlType('dom');
        $this->setFollow(true);
        $this->setStatusCode('200');
        $this->setMetaIndex(1);
        $this->setMetaFollow(1);
        $this->setXRobotsFollow(1);
        $this->setXRobotsIndex(1);
        $this->setRobotsGoogle(1);
        $this->setIgnorePosition(false);
        $this->setCostType(0);
        $this->setPrice(0);
        $this->setCostNote('');
        $this->setCreatedAt(new \DateTime());
        $this->crawlLog = new \Doctrine\Common\Collections\ArrayCollection();
        $this->alerts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public static function determineValidationGroups(FormInterface $form)
    {
        $data = $form->getData();
        if ($data->getCostType() >=1 && $data->getCostType() <= 3) {
            return array('costTypeMoney');
        }

        return array('Default');
    }

    /**
     * @param ExecutionContextInterface $context
     */
    public function doesBacklinkMatchProject(ExecutionContextInterface $context)
    {
        if (!$this->urlIsValid()) {
            $context->addViolationAt('url', 'The url must start with http://, https:// or //');
        } else {
            $error = $this->checkProjectDomainOrSubdomain();
            if ($error) {
                $context->addViolationAt('url', $error);
            }
        }
    }

    public function urlIsValid()
    {
        return stripos($this->getUrl(), 'http://') === 0
            || stripos($this->getUrl(), 'https://') === 0
            || stripos($this->getUrl(), '//') === 0;
    }

    public function checkProjectDomainOrSubdomain()
    {
        $projectDomain = $this->getProject()->getDomain();
        $projectSubdomain = $this->getProject()->getSubdomain();

        $error = '';
        if ($projectDomain) {
            if (!$projectDomain->urlMatches($this->getUrl())) {
                $error = "The url's domain does not match the project's domain";
            }
        } elseif ($projectSubdomain) {
            if (!$projectSubdomain->getDomain()->urlMatches($this->getUrl())) {
                $error = "The url's domain does not match the project's domain";
            }
            if (!$projectSubdomain->urlMatches($this->getUrl())) {
                $error = "The url's subdomain does not match the project's subdomain";
            }
        }

        return $error;
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
     * @return Backlink
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
     * Set type
     *
     * @param string $type
     * @return Backlink
     */
    public function setType($type)
    {
        $this->type = $type;
        $this->setTypeOk($this->checkTypeOk());

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

    public function getTypeName()
    {
        if ($this->type == 't') {
            return 'Text';
        } elseif ($this->type == 'i') {
            return 'Image';
        } else {
            return '-';
        }
    }

    /**
     * Set anchor
     *
     * @param string $anchor
     * @return Backlink
     */
    public function setAnchor($anchor)
    {
        $this->anchor = $anchor;
        $this->setAnchorOk($this->checkAnchorOk());
    
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
     * Set follow
     *
     * @param boolean $follow
     * @return Backlink
     */
    public function setFollow($follow)
    {
        $this->follow = $follow;
        $this->setFollowOk($this->checkFollowOk());

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
     * Set page
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $page
     * @return Backlink
     */
    public function setPage(Page $page = null)
    {
        $this->page = $page;
    
        return $this;
    }

    /**
     * Get page
     *
     * @return \Pool\LinkmotorBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Backlink
     */
    public function setProject(Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Backlink
     */
    public function setProjectAndApplyDefaultValues(Project $project = null)
    {
        $this->setProject($project);
        $this->setIgnorePosition($project->getSettingsIgnorePosition());

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

    public function checkTypeOk()
    {
        return $this->getType() == $this->getTypeLastCrawl();
    }

    /**
     * Set typeLastCrawled
     *
     * @param string $typeLastCrawl
     * @return Backlink
     */
    public function setTypeLastCrawl($typeLastCrawl)
    {
        $this->typeLastCrawl = $typeLastCrawl;
        $this->setTypeOk($this->checkTypeOk());

        return $this;
    }

    /**
     * Get typeLastCrawl
     *
     * @return string 
     */
    public function getTypeLastCrawl()
    {
        return $this->typeLastCrawl;
    }

    /**
     * Set typeOk
     *
     * @param boolean $typeOk
     * @return Backlink
     */
    public function setTypeOk($typeOk)
    {
        $this->typeOk = $typeOk ? 1 : 0;
    
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

    public function checkAnchorOk()
    {
        return $this->getAnchor() == $this->getAnchorLastCrawl();
    }

    /**
     * Set anchorLastCrawl
     *
     * @param string $anchorLastCrawl
     * @return Backlink
     */
    public function setAnchorLastCrawl($anchorLastCrawl)
    {
        $this->anchorLastCrawl = $anchorLastCrawl;
        $this->setAnchorOk($this->checkAnchorOk());

        return $this;
    }

    /**
     * Get anchorLastCrawl
     *
     * @return string 
     */
    public function getAnchorLastCrawl()
    {
        return $this->anchorLastCrawl;
    }

    /**
     * Set anchorOk
     *
     * @param boolean $anchorOk
     * @return Backlink
     */
    public function setAnchorOk($anchorOk)
    {
        $this->anchorOk = $anchorOk ? 1 : 0;
    
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

    public function checkFollowOk()
    {
        return $this->getFollow() == $this->getFollowLastCrawl();
    }

    /**
     * Set followLastCrawl
     *
     * @param boolean $followLastCrawl
     * @return Backlink
     */
    public function setFollowLastCrawl($followLastCrawl)
    {
        $this->followLastCrawl = $followLastCrawl ? 1 : 0;
        $this->setFollowOk($this->checkFollowOk());

        return $this;
    }

    /**
     * Get followLastCrawl
     *
     * @return boolean 
     */
    public function getFollowLastCrawl()
    {
        return $this->followLastCrawl;
    }

    /**
     * Set followOk
     *
     * @param boolean $followOk
     * @return Backlink
     */
    public function setFollowOk($followOk)
    {
        $this->followOk = $followOk ? 1 : 0;
    
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
     * @return Backlink
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
     * Set urlOk
     *
     * @param boolean $urlOk
     * @return Backlink
     */
    public function setUrlOk($urlOk)
    {
        $this->urlOk = $urlOk ? 1 : 0;

        if (!$urlOk) {
            $this->setAnchorLastCrawl(null);
            $this->setTypeLastCrawl(null);
            $this->setFollowLastCrawl(null);
            $this->setXPathLastCrawl(null);
            $this->setStatusCodeLastCrawl(null);
            $this->setMetaIndexLastCrawl(null);
            $this->setMetaFollowLastCrawl(null);
            $this->setXRobotsFollowLastCrawl(null);
            $this->setXRobotsIndexLastCrawl(null);
            $this->setRobotsGoogleLastCrawl(null);
        }

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
     * Set xPathLastCrawl
     *
     * @param string $xPathLastCrawl
     * @return Backlink
     */
    public function setXPathLastCrawl($xPathLastCrawl)
    {
        $this->xPathLastCrawl = $xPathLastCrawl;
        $this->setXPathOk($this->checkXPathOk());

        return $this;
    }

    /**
     * Get xPathLastCrawl
     *
     * @return string 
     */
    public function getXPathLastCrawl()
    {
        return $this->xPathLastCrawl;
    }

    public function checkXPathOk()
    {
        return $this->getXPath() != null
               && $this->getXPath() == $this->getXPathLastCrawl();
    }

    /**
     * Add crawlLog
     *
     * @param \Pool\LinkmotorBundle\Entity\CrawlLog $crawlLog
     * @return Backlink
     */
    public function addCrawlLog(CrawlLog $crawlLog)
    {
        $this->crawlLog[] = $crawlLog;
    
        return $this;
    }

    /**
     * Remove crawlLog
     *
     * @param \Pool\LinkmotorBundle\Entity\CrawlLog $crawlLog
     */
    public function removeCrawlLog(CrawlLog $crawlLog)
    {
        $this->crawlLog->removeElement($crawlLog);
    }

    /**
     * Get crawlLog
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCrawlLog()
    {
        return $this->crawlLog;
    }

    /**
     * Set lastCrawledAt
     *
     * @param \DateTime $lastCrawledAt
     * @return Backlink
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
     * Add alerts
     *
     * @param \Pool\LinkmotorBundle\Entity\Alert $alerts
     * @return Backlink
     */
    public function addAlert(Alert $alerts)
    {
        $this->alerts[] = $alerts;
    
        return $this;
    }

    /**
     * Remove alerts
     *
     * @param \Pool\LinkmotorBundle\Entity\Alert $alerts
     */
    public function removeAlert(Alert $alerts)
    {
        $this->alerts->removeElement($alerts);
    }

    /**
     * Get alerts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

    /**
     * @return bool
     */
    public function hasAlert()
    {
        return !$this->getAlerts()->isEmpty();
    }

    /**
     * Set xPath
     *
     * @param string $xPath
     * @return Backlink
     */
    public function setXPath($xPath)
    {
        $this->xPath = $xPath;
        $this->setXPathOk($this->checkXPathOk());
    
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
     * Set xPathOk
     *
     * @param boolean $xPathOk
     * @return Backlink
     */
    public function setXPathOk($xPathOk)
    {
        $this->xPathOk = $xPathOk ? 1 : 0;
    
        return $this;
    }

    /**
     * Get xPathOk
     *
     * @return boolean 
     */
    public function getXPathOk()
    {
        if ($this->getIgnorePosition()) {
            return true;
        }

        return $this->xPathOk;
    }

    public function getStatus()
    {
        if ($this->isOffline()) {
            return 'offline';
        }

        if ($this->getLastCrawledAt() === null) {
            return 'pending';
        }

        if (count($this->alerts) == 0) {
            return 'ok';
        }

        foreach ($this->alerts as $alert) {
            if ($alert->getType() == 'e') {
                return 'error';
            }
        }

        return 'warning';
    }

    public function isOfflineMayBeChangedByUser(User $user)
    {
        return $user->getId() == $this->getAssignedTo()->getId()
               || $user->isAdmin();
    }

    /**
     * Set isOffline
     *
     * @param boolean $isOffline
     * @return Backlink
     */
    public function setOffline($isOffline)
    {
        $this->isOffline = $isOffline ? 1 : 0;
    
        return $this;
    }

    /**
     * Get isOffline
     *
     * @return boolean 
     */
    public function isOffline()
    {
        return $this->isOffline;
    }

    /**
     * Set assignedTo
     *
     * @param \Pool\LinkmotorBundle\Entity\User $assignedTo
     * @return Backlink
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
     * Set ignorePosition
     *
     * @param boolean $ignorePosition
     * @return Backlink
     */
    public function setIgnorePosition($ignorePosition)
    {
        $this->ignorePosition = $ignorePosition ? 1 : 0;
    
        return $this;
    }

    /**
     * Get ignorePosition
     *
     * @return boolean 
     */
    public function getIgnorePosition()
    {
        return $this->ignorePosition;
    }

    /**
     * Set crawlType
     *
     * @param string $crawlType
     * @return Backlink
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

    public function getCrawlTypeName()
    {
        if ($this->getCrawlType() == 'text') {
            return 'Textmatching';
        }

        return 'DOM';
    }

    /**
     * Set costType
     *
     * @param integer $costType
     * @return Backlink
     */
    public function setCostType($costType)
    {
        $this->costType = $costType;

        return $this;
    }

    /**
     * Get costType
     *
     * @return integer 
     */
    public function getCostType()
    {
        return $this->costType;
    }

    public function getCostTypeName()
    {
        return self::$costTypes[$this->getCostType()];
    }

    /**
     * Set price
     *
     * @param float $price
     * @return Backlink
     */
    public function setPrice($price)
    {
        $this->price = $price;
    
        return $this;
    }

    /**
     * Get price
     *
     * @return float 
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set costNote
     *
     * @param string $costNote
     * @return Backlink
     */
    public function setCostNote($costNote)
    {
        $this->costNote = $costNote;
    
        return $this;
    }

    /**
     * Get costNote
     *
     * @return string 
     */
    public function getCostNote()
    {
        return $this->costNote;
    }

    /**
     * Set statusCode
     *
     * @param string $statusCode
     * @return Backlink
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = $statusCode;
        $this->setStatusCodeOk($this->checkStatusCodeOk());

        return $this;
    }

    /**
     * @return bool
     */
    public function checkStatusCodeOk()
    {
        return $this->getStatusCode() == $this->getStatusCodeLastCrawl();
    }

    /**
     * Get statusCode
     *
     * @return string
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Set statusCodeLastCrawl
     *
     * @param string $statusCodeLastCrawl
     * @return Backlink
     */
    public function setStatusCodeLastCrawl($statusCodeLastCrawl)
    {
        $this->statusCodeLastCrawl = $statusCodeLastCrawl;
        $this->setStatusCodeOk($this->checkStatusCodeOk());

        return $this;
    }

    /**
     * Get statusCodeLastCrawl
     *
     * @return string
     */
    public function getStatusCodeLastCrawl()
    {
        return $this->statusCodeLastCrawl;
    }

    /**
     * Set statusCodeOk
     *
     * @param integer $statusCodeOk
     * @return Backlink
     */
    public function setStatusCodeOk($statusCodeOk)
    {
        $this->statusCodeOk = $statusCodeOk;

        return $this;
    }

    /**
     * Get statusCodeOk
     *
     * @return integer
     */
    public function getStatusCodeOk()
    {
        return $this->statusCodeOk;
    }

    /**
     * Set metaIndex
     *
     * @param integer $metaIndex
     * @return Backlink
     */
    public function setMetaIndex($metaIndex)
    {
        $this->metaIndex = $metaIndex;
        $this->setMetaIndexOk($this->checkMetaIndexOk());
    
        return $this;
    }

    /**
     * @return bool
     */
    public function checkMetaIndexOk()
    {
        return $this->getMetaIndex() == $this->getMetaIndexLastCrawl();
    }

    /**
     * Get metaIndex
     *
     * @return integer 
     */
    public function getMetaIndex()
    {
        return $this->metaIndex;
    }

    /**
     * Set metaIndexLastCrawl
     *
     * @param integer $metaIndexLastCrawl
     * @return Backlink
     */
    public function setMetaIndexLastCrawl($metaIndexLastCrawl)
    {
        $this->metaIndexLastCrawl = $metaIndexLastCrawl;
        $this->setMetaIndexOk($this->checkMetaIndexOk());
    
        return $this;
    }

    /**
     * Get metaIndexLastCrawl
     *
     * @return integer 
     */
    public function getMetaIndexLastCrawl()
    {
        return $this->metaIndexLastCrawl;
    }

    /**
     * Set metaIndexOk
     *
     * @param integer $metaIndexOk
     * @return Backlink
     */
    public function setMetaIndexOk($metaIndexOk)
    {
        $this->metaIndexOk = $metaIndexOk;
    
        return $this;
    }

    /**
     * Get metaIndexOk
     *
     * @return integer 
     */
    public function getMetaIndexOk()
    {
        return $this->metaIndexOk;
    }

    /**
     * Set metaFollow
     *
     * @param integer $metaFollow
     * @return Backlink
     */
    public function setMetaFollow($metaFollow)
    {
        $this->metaFollow = $metaFollow;
        $this->setMetaFollowOk($this->checkMetaFollowOk());

        return $this;
    }

    /**
     * @return bool
     */
    public function checkMetaFollowOk()
    {
        return $this->getMetaFollow() == $this->getMetaFollowLastCrawl();
    }

    /**
     * Get metaFollow
     *
     * @return integer 
     */
    public function getMetaFollow()
    {
        return $this->metaFollow;
    }

    /**
     * Set metaFollowLastCrawl
     *
     * @param integer $metaFollowLastCrawl
     * @return Backlink
     */
    public function setMetaFollowLastCrawl($metaFollowLastCrawl)
    {
        $this->metaFollowLastCrawl = $metaFollowLastCrawl;
        $this->setMetaFollowOk($this->checkMetaFollowOk());
    
        return $this;
    }

    /**
     * Get metaFollowLastCrawl
     *
     * @return integer 
     */
    public function getMetaFollowLastCrawl()
    {
        return $this->metaFollowLastCrawl;
    }

    /**
     * Set metaFollowOk
     *
     * @param integer $metaFollowOk
     * @return Backlink
     */
    public function setMetaFollowOk($metaFollowOk)
    {
        $this->metaFollowOk = $metaFollowOk;
    
        return $this;
    }

    /**
     * Get metaFollowOk
     *
     * @return integer 
     */
    public function getMetaFollowOk()
    {
        return $this->metaFollowOk;
    }

    /**
     * Set xRobotsFollow
     *
     * @param integer $xRobotsFollow
     * @return Backlink
     */
    public function setXRobotsFollow($xRobotsFollow)
    {
        $this->xRobotsFollow = $xRobotsFollow;
        $this->setXRobotsFollowOk($this->checkXRobotsFollowOk());
    
        return $this;
    }

    /**
     * @return bool
     */
    public function checkXRobotsFollowOk()
    {
        return $this->getXRobotsFollow() == $this->getXRobotsFollowLastCrawl();
    }

    /**
     * Get xRobotsFollow
     *
     * @return integer 
     */
    public function getXRobotsFollow()
    {
        return $this->xRobotsFollow;
    }

    /**
     * Set xRobotsFollowLastCrawl
     *
     * @param integer $xRobotsFollowLastCrawl
     * @return Backlink
     */
    public function setXRobotsFollowLastCrawl($xRobotsFollowLastCrawl)
    {
        $this->xRobotsFollowLastCrawl = $xRobotsFollowLastCrawl;
        $this->setXRobotsFollowOk($this->checkXRobotsFollowOk());
    
        return $this;
    }

    /**
     * Get xRobotsFollowLastCrawl
     *
     * @return integer 
     */
    public function getXRobotsFollowLastCrawl()
    {
        return $this->xRobotsFollowLastCrawl;
    }

    /**
     * Set xRobotsFollowOk
     *
     * @param integer $xRobotsFollowOk
     * @return Backlink
     */
    public function setXRobotsFollowOk($xRobotsFollowOk)
    {
        $this->xRobotsFollowOk = $xRobotsFollowOk;
    
        return $this;
    }

    /**
     * Get xRobotsFollowOk
     *
     * @return integer 
     */
    public function getXRobotsFollowOk()
    {
        return $this->xRobotsFollowOk;
    }

    /**
     * Set xRobotsIndex
     *
     * @param integer $xRobotsIndex
     * @return Backlink
     */
    public function setXRobotsIndex($xRobotsIndex)
    {
        $this->xRobotsIndex = $xRobotsIndex;
        $this->setXRobotsIndexOk($this->checkXRobotsIndexOk());
    
        return $this;
    }

    /**
     * @return bool
     */
    public function checkXRobotsIndexOk()
    {
        return $this->getXRobotsIndex() == $this->getXRobotsIndexLastCrawl();
    }

    /**
     * Get xRobotsIndex
     *
     * @return integer 
     */
    public function getXRobotsIndex()
    {
        return $this->xRobotsIndex;
    }

    /**
     * Set xRobotsIndexLastCrawl
     *
     * @param integer $xRobotsIndexLastCrawl
     * @return Backlink
     */
    public function setXRobotsIndexLastCrawl($xRobotsIndexLastCrawl)
    {
        $this->xRobotsIndexLastCrawl = $xRobotsIndexLastCrawl;
        $this->setXRobotsIndexOk($this->checkXRobotsIndexOk());
    
        return $this;
    }

    /**
     * Get xRobotsIndexLastCrawl
     *
     * @return integer 
     */
    public function getXRobotsIndexLastCrawl()
    {
        return $this->xRobotsIndexLastCrawl;
    }

    /**
     * Set xRobotsIndexOk
     *
     * @param integer $xRobotsIndexOk
     * @return Backlink
     */
    public function setXRobotsIndexOk($xRobotsIndexOk)
    {
        $this->xRobotsIndexOk = $xRobotsIndexOk;
    
        return $this;
    }

    /**
     * Get xRobotsIndexOk
     *
     * @return integer 
     */
    public function getXRobotsIndexOk()
    {
        return $this->xRobotsIndexOk;
    }

    /**
     * Set robotsGoogle
     *
     * @param integer $robotsGoogle
     * @return Backlink
     */
    public function setRobotsGoogle($robotsGoogle)
    {
        $this->robotsGoogle = $robotsGoogle;
        $this->setRobotsGoogleOk($this->checkRobotsGoogleOk());

        return $this;
    }

    /**
     * @return bool
     */
    public function checkRobotsGoogleOk()
    {
        return $this->getRobotsGoogle() == $this->getRobotsGoogleLastCrawl();
    }

    /**
     * Get robotsGoogle
     *
     * @return integer 
     */
    public function getRobotsGoogle()
    {
        return $this->robotsGoogle;
    }

    /**
     * Set robotsGoogleLastCrawl
     *
     * @param integer $robotsGoogleLastCrawl
     * @return Backlink
     */
    public function setRobotsGoogleLastCrawl($robotsGoogleLastCrawl)
    {
        $this->robotsGoogleLastCrawl = $robotsGoogleLastCrawl;
        $this->setRobotsGoogleOk($this->checkRobotsGoogleOk());
    
        return $this;
    }

    /**
     * Get robotsGoogleLastCrawl
     *
     * @return integer 
     */
    public function getRobotsGoogleLastCrawl()
    {
        return $this->robotsGoogleLastCrawl;
    }

    /**
     * Set robotsGoogleOk
     *
     * @param integer $robotsGoogleOk
     * @return Backlink
     */
    public function setRobotsGoogleOk($robotsGoogleOk)
    {
        $this->robotsGoogleOk = $robotsGoogleOk;
    
        return $this;
    }

    /**
     * Get robotsGoogleOk
     *
     * @return integer 
     */
    public function getRobotsGoogleOk()
    {
        return $this->robotsGoogleOk;
    }
}
