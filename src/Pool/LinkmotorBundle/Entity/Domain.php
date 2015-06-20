<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Pool\LinkmotorBundle\Entity\Project;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Domain
 *
 * @ORM\Table(name="domains")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\DomainRepository")
 * @UniqueEntity(fields={"name"}, message="This domain is already taken!")
 */
class Domain
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
     * @ORM\Column(name="name", type="string", length=128)
     * @Assert\NotBlank()
     * @Assert\Regex(
     *     pattern="/[\/:]/",
     *     match=false,
     *     message="The domain may not contain / or :"
     * )
     */
    protected $name;

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
     * @ORM\Column(name="net_pop", type="integer", nullable=true)
     */
    protected $netPop;

    /**
     * @var \DateTime
     * @ORM\Column(name="first_day", type="date", nullable=true)
     */
    protected $firstDay;

    /**
     * @ORM\Column(name="last_crawled_at", type="datetime", nullable=true)
     */
    protected $lastCrawledAt;

    /**
     * @ORM\OneToMany(targetEntity="Subdomain", mappedBy="domain")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $subdomains;

    /**
     * @ORM\OneToMany(targetEntity="Blacklist", mappedBy="domain")
     */
    protected $blacklists;

    /**
     * @ORM\OneToMany(targetEntity="Competitor", mappedBy="domain")
     */
    protected $competitors;

    /**
     * @ORM\ManyToOne(targetEntity="Vendor", inversedBy="domains")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id")
     */
    protected $vendor;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="domain")
     */
    protected $projects;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setAuthority(0);
        $this->setDomainPop(null);
        $this->setLinkPop(null);
        $this->setNetPop(null);
        $this->setName('');
        $this->subdomains = new \Doctrine\Common\Collections\ArrayCollection();
        $this->blacklists = new \Doctrine\Common\Collections\ArrayCollection();
        $this->competitors = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function urlMatches($url)
    {
        // Issue #70: Prüfung der Domain funktioniert bei // als Protokoll nicht
        // Das scheint ein PHP-Bug zu sein, der auf dem Server noch nicht behoben ist.
        // Deshalb hier ein Workaround.

        $emptyScheme = false;
        if (strpos($url, '//') === 0) {
            $url = 'http:' . $url;
            $emptyScheme = true;
        }
        $urlParts = $this->getUrlParts($url);
        if ($emptyScheme) {
            $urlParts['scheme'] = '';
        }

        return $urlParts['domain'] == $this->getName();
    }

    /**
     * @todo wird auch in PageCreator und Subdomain benutzt
     * @param string $url
     * @return array
     */
    public function getUrlParts($url)
    {
        $urlParts = parse_url($url);

        if (!isset($urlParts['path'])) {
            $path = '/';
        } else {
            $path = $urlParts['path'];
        }

        if (isset($urlParts['query'])) {
            $path .= '?' . $urlParts['query'];
        }

        $urlParts['host'] = isset($urlParts['host']) ? $urlParts['host'] : '';
        $urlParts['host'] = strtolower($urlParts['host']);
        $parts = explode('.', $urlParts['host']);
        if (count($parts) == 2) {
            $domain = $urlParts['host'];
            $subdomain = '';
        } else {
            $subdomain = array_shift($parts);
            $domain = implode('.', $parts);
        }

        return array(
            'scheme' => isset($urlParts['scheme']) ? $urlParts['scheme'] : '',
            'domain' => $domain,
            'subdomain' => $subdomain,
            'path' => $path
        );
    }

    public function getNumPages(Project $project)
    {
        $numPages = 0;

        foreach ($this->getSubdomains() as $subdomain) {
            foreach ($subdomain->getPages() as $page) {
                if ($page->getProject() == $project) {
                    $numPages++;
                }
            }
        }

        return $numPages;
    }

    public function getNumBacklinks(Project $project)
    {
        $numBacklinks = 0;

        foreach ($this->getSubdomains() as $subdomain) {
            foreach ($subdomain->getPages() as $page) {
                if ($page->getProject() == $project) {
                    $numBacklinks += $page->getBacklinks()->count();
                }
            }
        }

        return $numBacklinks;
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
     * Set name
     *
     * @param string $name
     * @return Project
     */
    public function setName($name)
    {
        $this->name = mb_strtolower($name, 'UTF-8');

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return idn_to_ascii($this->name);
    }

    public function getNameForDisplay()
    {
        return idn_to_utf8($this->getName());
    }

    /**
     * Add subdomains
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $subdomains
     * @return Domain
     */
    public function addSubdomain(\Pool\LinkmotorBundle\Entity\Domain $subdomains)
    {
        $this->subdomains[] = $subdomains;
    
        return $this;
    }

    /**
     * Remove subdomains
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $subdomains
     */
    public function removeSubdomain(\Pool\LinkmotorBundle\Entity\Domain $subdomains)
    {
        $this->subdomains->removeElement($subdomains);
    }

    /**
     * Get subdomains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubdomains()
    {
        return $this->subdomains;
    }

    /**
     * Set authority
     *
     * @param integer $authority
     * @return Domain
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
     * Set vendor
     *
     * @param \Pool\LinkmotorBundle\Entity\Vendor $vendor
     * @return Domain
     */
    public function setVendor(\Pool\LinkmotorBundle\Entity\Vendor $vendor = null)
    {
        $this->vendor = $vendor;
        $vendor->addDomain($this);

        return $this;
    }

    /**
     * Get vendor
     *
     * @return \Pool\LinkmotorBundle\Entity\Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }

    /**
     * Set lastCrawlAt
     *
     * @param \DateTime $lastCrawlAt
     * @return Domain
     */
    public function setLastCrawlAt($lastCrawlAt)
    {
        $this->lastCrawlAt = $lastCrawlAt;
    
        return $this;
    }

    /**
     * @return bool
     */
    public function getNotYetCrawled()
    {
        return $this->lastCrawledAt === null;
    }

    /**
     * Get lastCrawlAt
     *
     * @return \DateTime 
     */
    public function getLastCrawlAt()
    {
        return $this->lastCrawlAt;
    }

    /**
     * Set lastCrawledAt
     *
     * @param \DateTime $lastCrawledAt
     * @return Domain
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
     * Add blacklists
     *
     * @param \Pool\LinkmotorBundle\Entity\Blacklist $blacklists
     * @return Domain
     */
    public function addBlacklist(\Pool\LinkmotorBundle\Entity\Blacklist $blacklists)
    {
        $this->blacklists[] = $blacklists;
    
        return $this;
    }

    /**
     * Remove blacklists
     *
     * @param \Pool\LinkmotorBundle\Entity\Blacklist $blacklists
     */
    public function removeBlacklist(\Pool\LinkmotorBundle\Entity\Blacklist $blacklists)
    {
        $this->blacklists->removeElement($blacklists);
    }

    /**
     * Get blacklists
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBlacklists()
    {
        return $this->blacklists;
    }

    /**
     * Add competitors
     *
     * @param \Pool\LinkmotorBundle\Entity\Competitor $competitors
     * @return Domain
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
     * @param Project $project
     * @return bool
     */
    public function blacklistedIn(Project $project)
    {
        foreach ($this->getBlacklists() as $item) {
            if ($item->getProject() == $project) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Project $project
     * @return bool
     */
    public function competitorIn(Project $project)
    {
        foreach ($this->getCompetitors() as $item) {
            if ($item->getProject() == $project) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param Project $project
     * @return bool
     */
    public function matchesProjectDomain(Project $project)
    {
        return $project->getDomain() && $this->getName() == $project->getDomainName();
    }

    /**
     * Add projects
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $projects
     * @return Domain
     */
    public function addProject(\Pool\LinkmotorBundle\Entity\Project $projects)
    {
        $this->projects[] = $projects;
    
        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $projects
     */
    public function removeProject(\Pool\LinkmotorBundle\Entity\Project $projects)
    {
        $this->projects->removeElement($projects);
    }

    /**
     * Get projects
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getProjects()
    {
        return $this->projects;
    }

    /**
     * Set linkPop
     *
     * @param integer $linkPop
     * @return Domain
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
     * @return Domain
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
     * Set netPop
     *
     * @param integer $netPop
     * @return Domain
     */
    public function setNetPop($netPop)
    {
        $this->netPop = $netPop;
    
        return $this;
    }

    /**
     * Get netPop
     *
     * @return integer 
     */
    public function getNetPop()
    {
        return $this->netPop;
    }

    /**
     * Set firstDay
     *
     * @param \DateTime $firstDay
     * @return Domain
     */
    public function setFirstDay($firstDay)
    {
        $this->firstDay = $firstDay;
    
        return $this;
    }

    /**
     * Get firstDay
     *
     * @return \DateTime 
     */
    public function getFirstDay()
    {
        return $this->firstDay;
    }

    /**
     * @return string|null
     */
    public function getFirstYear()
    {
        if ($this->firstDay) {
            return $this->firstDay->format('Y');
        }

        return null;
    }
}
