<?php

namespace Pool\LinkmotorBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\Subdomain;

class Domains
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    private $multiPartTopLevelDomains = array(
        'co.uk' => array('co', 'uk'),
        'com.pt' => array('com', 'pt'),
        'com.br' => array('com', 'br'),
        'co.nz' => array('co', 'nz'),
        'com.mx' => array('com', 'mx'),
        'com.au' => array('com', 'au'),
        'com.ar' => array('com', 'ar'),
    );

    public function __construct(Registry $doctrine = null)
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
        $parts = $this->getRealParts(explode('.', $hostname));
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
        $parts = $this->getRealParts(explode('.', $hostname));

        if (count($parts) < 2) {
            return '';
        } else {
            return $parts[count($parts) - 2] . '.' . $parts[count($parts) - 1];
        }
    }

    /**
     * This method makes sure that combined FLD-Domains like "co.uk" are treated as a FLD-Domain
     * @param array $parts
     * @return array
     */
    private function getRealParts($parts)
    {
        $foundMultiPart = null;
        for ($i=0; $i<count($parts); $i++) {
            foreach ($this->multiPartTopLevelDomains as $combined => $multiParts) {
                if ($parts[$i] == $multiParts[0]
                    && isset($parts[$i + 1]) && $parts[$i + 1] == $multiParts[1]
                ) {
                    $foundMultiPart = $combined;
                    break;
                }
                if ($foundMultiPart) {
                    break;
                }
            }
        }

        if (!$foundMultiPart) {
            return $parts;
        }

        $realParts = array();
        for ($i=0; $i<count($parts); $i++) {
            if ($parts[$i] == $multiParts[0]
                && isset($parts[$i+1]) && $parts[$i+1] == $multiParts[1]
            ) {
                $realParts[] = $foundMultiPart;
                $i++;
            } else {
                $realParts[] = $parts[$i];
            }
        }

        return $realParts;
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
