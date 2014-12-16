<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class SubdomainRepository extends EntityRepository
{
    /**
     * @param $interval
     * @return Subdomain|null
     */
    public function getNextSubdomainToCrawl($interval)
    {
        $limit = new \DateTime(date('Y-m-d H:i:s', strtotime('-' . $interval)));
        $subdomains = $this->getEntityManager()
            ->createQuery(
                "SELECT s
                 FROM PoolLinkmotorBundle:Subdomain s
                 WHERE s.lastCrawledAt IS NULL OR s.lastCrawledAt < :timeLimit
                 ORDER BY s.lastCrawledAt ASC"
            )
            ->setMaxResults(1)
            ->setParameter('timeLimit', $limit)
            ->getResult();

        if ($subdomains) {
            return $subdomains[0];
        }

        return null;
    }
}
