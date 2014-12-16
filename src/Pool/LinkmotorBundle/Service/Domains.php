<?php

namespace Pool\LinkmotorBundle\Service;

use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\Subdomain;

class Domains
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function isDomain($value)
    {
        return $this->getDomain($value) != '' && $this->getSubdomain($value) == '';
    }

    public function isSubDomain($value)
    {
        return $this->getSubdomain($value) != '';
    }

    public function getSubdomain($hostname)
    {
        $hostname = strtolower($hostname);
        $parts = explode('.', $hostname);
        if (count($parts) < 3) {
            return '';
        } else {
            // somehow array_slice does not properly work with -2 as $offst
            return implode('.', array_reverse(array_slice(array_reverse($parts), 2)));
        }
    }

    public function getDomain($hostname)
    {
        $hostname = strtolower($hostname);
        $parts = explode('.', $hostname);
        if (count($parts) < 2) {
            return '';
        } else {
            return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }
    }

    public function addDomain($name)
    {
        $domain = $this->doctrine->getRepository('PoolLinkmotorBundle:Domain')->findOneByName($name);
        if (!$domain) {
            $domain = new Domain();
            $domain->setName($name);

            $em = $this->doctrine->getManager();
            $em->persist($domain);
            $em->flush($domain);
        }

        return $domain;
    }

    public function addDomainAndSubdomain($name)
    {
        $domainName = $this->getDomain($name);
        $subdomainName = $this->getSubdomain($name);
        $domain = $this->addDomain($domainName);

        $subdomain = $this->doctrine
            ->getRepository('PoolLinkmotorBundle:Subdomain')
            ->findOneBy(array('name' => $subdomainName, 'domain' => $domain->getId()));
        if (!$subdomain) {
            $subdomain = new Subdomain();
            $subdomain->setName($subdomainName);
            $subdomain->setDomain($domain);

            $em = $this->doctrine->getManager();
            $em->persist($subdomain);
            $em->flush();
        }

        return $subdomain;
    }

    /**
     * @param Domain $domain
     * @param Project $project
     */
    public function deleteAllNewPagesFor(Domain $domain, Project $project)
    {
        $em = $this->doctrine->getManager();
        $pages = $this->doctrine->getRepository('PoolLinkmotorBundle:Page')
            ->getDeleteableForDomainAndProject($domain, $project);
        foreach ($pages as $page) {
            $em->remove($page);
        }
        $em->flush();
    }
}
