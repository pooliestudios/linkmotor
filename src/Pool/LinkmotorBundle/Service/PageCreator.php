<?php

namespace Pool\LinkmotorBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Pool\LinkmotorBundle\Entity\User;

class PageCreator
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    /**
     * @var Domains
     */
    private $domains;

    public function __construct(Registry $doctrine = null, Domains $domains = null)
    {
        $this->doctrine = $doctrine;
        $this->domains = $domains;
    }

    /**
     * @param Project $project
     * @param string $url
     *
     * @return bool
     */
    public function checkIfPageExists(Project $project, $url)
    {
        $urlParts = $this->getUrlParts($url);
        $subdomain = $this->getSubdomain($urlParts['subdomain'], $urlParts['domain']);
        if (!$subdomain) {
            return false;
        }

        $pages = $this->doctrine
            ->getRepository('PoolLinkmotorBundle:Page')
            ->findBy(array('project' => $project, 'url' => $urlParts['path'], 'subdomain' => $subdomain));

        if ($pages) {
            return $pages[0];
        }

        return false;
    }

    /**
     * @param Project $project
     * @param $url
     * @param User $user
     *
     * @return Page
     */
    public function addPage(Project $project, $url, User $user)
    {
        if (!$url) {
            return false;
        }

        $em = $this->doctrine->getManager();

        $urlParts = $this->getUrlParts($url);
        if (!$urlParts['domain']) {
            return false;
        }
        $domain = $this->doctrine->getRepository('PoolLinkmotorBundle:Domain')->findOneByName($urlParts['domain']);
        if (!$domain) {
            $domain = new Domain();
            $domain->setName($urlParts['domain']);

            $em->persist($domain);
            $em->flush();
        } else {
            if ($domain->blacklistedIn($project) || $domain->competitorIn($project)
                || $domain->matchesProjectDomain($project)
            ) {
                return false;
            }
        }
        $subdomain = $this->doctrine->getRepository('PoolLinkmotorBundle:Subdomain')->findOneBy(
            array('domain' => $domain, 'name' => $urlParts['subdomain'])
        );
        if (!$subdomain) {
            $subdomain = new Subdomain();
            $subdomain->setDomain($domain);
            $subdomain->setName($urlParts['subdomain']);
            $em->persist($subdomain);
            $em->flush();
        } elseif ($domain->getName() == $project->getDomainName() && $subdomain->matchesProjectSubdomain($project)) {
            return false;
        }

        $findPage = $em->getRepository('PoolLinkmotorBundle:Page')->findOneBy(
            array('project' => $project, 'subdomain' => $subdomain, 'url' => $urlParts['path'])
        );
        if ($findPage) {
            return $findPage;
        } else {
            $page = new Page();
            $page->setProject($project);
            $page->setScheme($urlParts['scheme']);
            $page->setSubdomain($subdomain);
            $page->setUrl($urlParts['path']);
            $page->setStatus($this->doctrine->getRepository('PoolLinkmotorBundle:Status')->find(1));
            $page->setAssignedTo($user);

            $em->persist($page);
            $em->flush();

            return $page;
        }
    }

    /**
     * @param Project $project
     * @param $url
     *
     * @return bool|Subdomain
     */
    public function checkIfSubdomainHasPages(Project $project, $url)
    {
        $urlParts = $this->getUrlParts($url);
        $hasPages = false;
        $subdomain = $this->getSubdomain($urlParts['subdomain'], $urlParts['domain']);
        if ($subdomain) {
            $pages = $this->doctrine
                ->getRepository('PoolLinkmotorBundle:Page')
                ->findBySubdomain($subdomain);

            if ($pages) {
                foreach ($pages as $page) {
                    if ($page->getProject() == $project) {
                        $hasPages = true;
                        break;
                    }
                }
            }
        }

        return array(
            'subdomain' => $subdomain,
            'hasPages' => $hasPages
        );
    }

    /**
     * @param string $subdomain
     * @param string $domain
     *
     * @return \Pool\LinkmotorBundle\Entity\Subdomain
     */
    public function getSubdomain($subdomain, $domain)
    {
        $domainObjects = $this->doctrine
            ->getRepository('PoolLinkmotorBundle:Domain')
            ->findByName($domain);

        if ($domainObjects) {
            foreach ($domainObjects as $domainObject) {
                foreach ($domainObject->getSubdomains() as $subdomainObject) {
                    if ($subdomainObject->getName() == $subdomain) {
                        return $subdomainObject;
                    }
                }
            }
        }

        return null;
    }

    /**
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

        $domain = $this->domains->getDomain($urlParts['host']);
        $subdomain = $this->domains->getSubdomain($urlParts['host']);

        return array(
            'scheme' => isset($urlParts['scheme']) ? $urlParts['scheme'] : '',
            'domain' => $domain,
            'subdomain' => $subdomain,
            'path' => $path
        );
    }
}
