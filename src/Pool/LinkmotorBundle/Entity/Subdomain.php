<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Subdomain
 *
 * @ORM\Table(name="subdomains")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\SubdomainRepository")
 */
class Subdomain
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
     */
    protected $name;

    /**
     * @ORM\Column(name="sichtbarkeitsindex", type="float", nullable=true)
     */
    protected $sichtbarkeitsindex;

    /**
     * @ORM\Column(name="ovi", type="float", nullable=true)
     */
    protected $ovi;

    /**
     * @ORM\Column(name="robots_txt", type="text", nullable=true)
     */
    protected $robotsTxt;

    /**
     * @var \DateTime $robotsTxtLastFetched
     * @ORM\Column(name="robots_txt_last_fetched", type="datetime", nullable=true)
     */
    protected $robotsTxtLastFetched;

    /**
     * @ORM\Column(name="last_crawled_at", type="datetime", nullable=true)
     */
    protected $lastCrawledAt;

    /**
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="subdomains")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */
    protected $domain;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="subdomain")
     * @ORM\OrderBy({"url" = "ASC"})
     */
    protected $pages;

    /**
     * @ORM\ManyToOne(targetEntity="Vendor", inversedBy="subdomains")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id")
     */
    protected $vendor;

    /**
     * @ORM\OneToMany(targetEntity="Project", mappedBy="subdomain")
     */
    protected $projects;

    public function __construct()
    {
        $this->name = '';
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function urlMatches($url)
    {
        $urlParts = $this->getUrlParts($url);

        return $urlParts['subdomain'] == $this->getName();
    }

    /**
     * @param Project $project
     * @return bool
     */
    public function matchesProjectSubdomain(Project $project)
    {
        return $project->getSubdomain() && $this->getName() == $project->getSubdomain()->getName();
    }

    /**
     * @todo wird auch in PageCreator und Domain benutzt
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

        foreach ($this->getPages() as $page) {
            if ($page->getProject() == $project) {
                $numPages++;
            }
        }

        return $numPages;
    }

    public function getNumBacklinks(Project $project)
    {
        $numBacklinks = 0;

        foreach ($this->getPages() as $page) {
            if ($page->getProject() == $project) {
                $numBacklinks += $page->getBacklinks()->count();
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
        $this->name = strtolower($name);

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set domain
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $domain
     * @return Subdomain
     */
    public function setDomain(Domain $domain = null)
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

    public function getFull()
    {
        $subdomain = $this->getName();
        if ($subdomain) {
            $subdomain = $subdomain . '.';
        }

        return $subdomain . $this->getDomain()->getName();
    }

    public function getFullForDisplay()
    {
        $subdomain = $this->getName();
        if ($subdomain) {
            $subdomain = $subdomain . '.';
        }

        return $subdomain . $this->getDomain()->getNameForDisplay();
    }

    /**
     * Add page
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $page
     * @return Subdomain
     */
    public function addPage(Page $page)
    {
        $this->pages[] = $page;
    
        return $this;
    }

    /**
     * Remove page
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $page
     */
    public function removePage(Page $page)
    {
        $this->pages->removeElement($page);
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
     * Set vendor
     *
     * @param \Pool\LinkmotorBundle\Entity\Vendor $vendor
     * @return Subdomain
     */
    public function setVendor(Vendor $vendor = null)
    {
        $this->vendor = $vendor;
    
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
     * Add projects
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $projects
     * @return Subdomain
     */
    public function addProject(Project $projects)
    {
        $this->projects[] = $projects;
    
        return $this;
    }

    /**
     * Remove projects
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $projects
     */
    public function removeProject(Project $projects)
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
     * Set robotsTxt
     *
     * @param string $robotsTxt
     * @return Subdomain
     */
    public function setRobotsTxt($robotsTxt)
    {
        $this->robotsTxt = $robotsTxt;
    
        return $this;
    }

    /**
     * Get robotsTxt
     *
     * @return string 
     */
    public function getRobotsTxt()
    {
        return $this->robotsTxt;
    }

    /**
     * Set robotsTxtLastFetched
     *
     * @param \DateTime $robotsTxtLastFetched
     * @return Subdomain
     */
    public function setRobotsTxtLastFetched($robotsTxtLastFetched)
    {
        $this->robotsTxtLastFetched = $robotsTxtLastFetched;
    
        return $this;
    }

    /**
     * Get robotsTxtLastFetched
     *
     * @return \DateTime 
     */
    public function getRobotsTxtLastFetched()
    {
        return $this->robotsTxtLastFetched;
    }

    public function robotsTxtNeedsRefresh()
    {
        $fetchLimit = date('Y-m-d H:i:s', strtotime('-24 hours'));
        if (!$this->robotsTxt
            || !$this->robotsTxtLastFetched
            || $this->robotsTxtLastFetched->format('Y-m-d H:i:s') < $fetchLimit
        ) {
            return true;
        }

        return false;
    }

    /**
     * Set sichtbarkeitsindex
     *
     * @param float $sichtbarkeitsindex
     * @return Subdomain
     */
    public function setSichtbarkeitsindex($sichtbarkeitsindex)
    {
        $this->sichtbarkeitsindex = $sichtbarkeitsindex;
    
        return $this;
    }

    /**
     * Get sichtbarkeitsindex
     *
     * @return float 
     */
    public function getSichtbarkeitsindex()
    {
        return $this->sichtbarkeitsindex;
    }

    /**
     * Set ovi
     *
     * @param float $ovi
     * @return Subdomain
     */
    public function setOvi($ovi)
    {
        $this->ovi = $ovi;
    
        return $this;
    }

    /**
     * Get ovi
     *
     * @return float 
     */
    public function getOvi()
    {
        return $this->ovi;
    }

    /**
     * Set lastCrawledAt
     *
     * @param \DateTime $lastCrawledAt
     * @return Subdomain
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
}
