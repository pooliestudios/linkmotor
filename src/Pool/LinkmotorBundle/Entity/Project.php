<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="projects")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\ProjectRepository")
 * @UniqueEntity(fields={"name"}, message="This name is already taken!")
 */
class Project
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
     * @var array (stored as JSON) - holds project wide settings
     *
     * @ORM\Column(name="settings", type="text");
     */
    protected $settings;

    /**
     * @var Domain
     *
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="projects")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */
    protected $domain;

    /**
     * @var Subdomain
     *
     * @ORM\ManyToOne(targetEntity="Subdomain", inversedBy="projects")
     * @ORM\JoinColumn(name="subdomain_id", referencedColumnName="id")
     */
    protected $subdomain;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="project")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $pages;

    /**
     * @ORM\OneToMany(targetEntity="Backlink", mappedBy="project")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $backlinks;

    /**
     * @ORM\OneToMany(targetEntity="Competitor", mappedBy="project")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $competitors;

    /**
     * @ORM\OneToMany(targetEntity="Keyword", mappedBy="project")
     * @ORM\OrderBy({"keyword" = "ASC"})
     */
    protected $keywords;

    /**
     * @ORM\OneToMany(targetEntity="Blacklist", mappedBy="project")
     */
    protected $blacklist;

    /**
     * @ORM\OneToMany(targetEntity="CrawlLog", mappedBy="project")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $crawlLog;

    /**
     * @ORM\OneToMany(targetEntity="Alert", mappedBy="project")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $alerts;

    /**
     * @ORM\OneToMany(targetEntity="NotificationSetting", mappedBy="project")
     */
    protected $notificationSettings;

    /**
     * @ORM\OneToMany(targetEntity="ActionStats", mappedBy="project")
     */
    protected $actionStats;

    /**
     * @ORM\OneToMany(targetEntity="Import", mappedBy="project")
     */
    protected $imports;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setSettings(array());
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->backlinks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->competitors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->keywords = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blacklist = new \Doctrine\Common\Collections\ArrayCollection();
        $this->crawlLog = new \Doctrine\Common\Collections\ArrayCollection();
        $this->alerts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actionStats = new \Doctrine\Common\Collections\ArrayCollection();
        $this->imports = new \Doctrine\Common\Collections\ArrayCollection();
        $this->notificationSettings = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param Project $project
     * @return bool
     */
    public function equals(Project $project = null)
    {
        if ($project === null) {
            return false;
        }

        return $this->getName() == $project->getName();
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
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        if ($this->domain) {
            return $this->domain->getName();
        } elseif ($this->subdomain) {
            return $this->subdomain->getFull();
        }

        return 'unassigned';
    }

    /**
     * Set settings
     *
     * @param array $settings
     * @return Project
     */
    public function setSettings($settings)
    {
        if (!is_array($settings)) {
            $settings = array();
        }
        $this->settings = @json_encode($settings);

        return $this;
    }

    /**
     * Get settings
     *
     * @return string
     */
    public function getSettings()
    {
        $settings = @json_decode($this->settings, true);
        if (!is_array($settings)) {
            $settings = array();
        }

        return $settings;
    }

    /**
     * @return bool
     */
    public function getSettingsIgnorePosition()
    {
        $settings = $this->getSettings();

        return isset($settings['backlinkIgnorePosition']) ? $settings['backlinkIgnorePosition'] : true;
    }

    /**
     * Add backlinks
     *
     * @param \Pool\LinkmotorBundle\Entity\Backlink $backlinks
     * @return Project
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
    public function removeBacklink(\Pool\LinkmotorBundle\Entity\Backlink $backlinks)
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

    /**
     * Add crawlLog
     *
     * @param \Pool\LinkmotorBundle\Entity\CrawlLog $crawlLog
     * @return Project
     */
    public function addCrawlLog(\Pool\LinkmotorBundle\Entity\CrawlLog $crawlLog)
    {
        $this->crawlLog[] = $crawlLog;
    
        return $this;
    }

    /**
     * Remove crawlLog
     *
     * @param \Pool\LinkmotorBundle\Entity\CrawlLog $crawlLog
     */
    public function removeCrawlLog(\Pool\LinkmotorBundle\Entity\CrawlLog $crawlLog)
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
     * Add alerts
     *
     * @param \Pool\LinkmotorBundle\Entity\Alert $alerts
     * @return Project
     */
    public function addAlert(\Pool\LinkmotorBundle\Entity\Alert $alerts)
    {
        $this->alerts[] = $alerts;
    
        return $this;
    }

    /**
     * Remove alerts
     *
     * @param \Pool\LinkmotorBundle\Entity\Alert $alerts
     */
    public function removeAlert(\Pool\LinkmotorBundle\Entity\Alert $alerts)
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
     * Add pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     * @return Project
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
     * Add competitors
     *
     * @param \Pool\LinkmotorBundle\Entity\Competitor $competitors
     * @return Project
     */
    public function addCompetitor(\Pool\LinkmotorBundle\Entity\Competitor $competitors)
    {
        $this->competitors[] = $competitors;
    
        return $this;
    }

    /**
     * Remove competitors
     *
     * @param \Pool\LinkmotorBundle\Entity\Competitor $competitors
     */
    public function removeCompetitor(\Pool\LinkmotorBundle\Entity\Competitor $competitors)
    {
        $this->competitors->removeElement($competitors);
    }

    /**
     * Get competitors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompetitors()
    {
        return $this->competitors;
    }

    /**
     * Add blacklist
     *
     * @param \Pool\LinkmotorBundle\Entity\Blacklist $blacklist
     * @return Project
     */
    public function addBlacklist(\Pool\LinkmotorBundle\Entity\Blacklist $blacklist)
    {
        $this->blacklist[] = $blacklist;
    
        return $this;
    }

    /**
     * Remove blacklist
     *
     * @param \Pool\LinkmotorBundle\Entity\Blacklist $blacklist
     */
    public function removeBlacklist(\Pool\LinkmotorBundle\Entity\Blacklist $blacklist)
    {
        $this->blacklist->removeElement($blacklist);
    }

    /**
     * Get blacklist
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBlacklist()
    {
        return $this->blacklist;
    }

    /**
     * Add keywords
     *
     * @param \Pool\LinkmotorBundle\Entity\Keyword $keywords
     * @return Project
     */
    public function addKeyword(\Pool\LinkmotorBundle\Entity\Keyword $keywords)
    {
        $this->keywords[] = $keywords;
    
        return $this;
    }

    /**
     * Remove keywords
     *
     * @param \Pool\LinkmotorBundle\Entity\Keyword $keywords
     */
    public function removeKeyword(\Pool\LinkmotorBundle\Entity\Keyword $keywords)
    {
        $this->keywords->removeElement($keywords);
    }

    /**
     * Get keywords
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKeywords()
    {
        return $this->keywords;
    }

    /**
     * Set domain
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $domain
     * @return Project
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

    /**
     * Set subdomain
     *
     * @param \Pool\LinkmotorBundle\Entity\Subdomain $subdomain
     * @return Project
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

    public function getDomainName()
    {
        $domainName = '';

        if ($this->getDomain()) {
            $domainName = $this->getDomain()->getName();
        } elseif ($this->getSubdomain()) {
            $domainName = $this->getSubdomain()->getDomain()->getName();
        }

        return $domainName;
    }

    /**
     * Add actionStats
     *
     * @param \Pool\LinkmotorBundle\Entity\ActionStats $actionStats
     * @return Project
     */
    public function addActionStat(\Pool\LinkmotorBundle\Entity\ActionStats $actionStats)
    {
        $this->actionStats[] = $actionStats;
    
        return $this;
    }

    /**
     * Remove actionStats
     *
     * @param \Pool\LinkmotorBundle\Entity\ActionStats $actionStats
     */
    public function removeActionStat(\Pool\LinkmotorBundle\Entity\ActionStats $actionStats)
    {
        $this->actionStats->removeElement($actionStats);
    }

    /**
     * Get actionStats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActionStats()
    {
        return $this->actionStats;
    }

    /**
     * Add imports
     *
     * @param \Pool\LinkmotorBundle\Entity\Import $imports
     * @return Project
     */
    public function addImport(\Pool\LinkmotorBundle\Entity\Import $imports)
    {
        $this->imports[] = $imports;
    
        return $this;
    }

    /**
     * Remove imports
     *
     * @param \Pool\LinkmotorBundle\Entity\Import $imports
     */
    public function removeImport(\Pool\LinkmotorBundle\Entity\Import $imports)
    {
        $this->imports->removeElement($imports);
    }

    /**
     * Get imports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImports()
    {
        return $this->imports;
    }
}
