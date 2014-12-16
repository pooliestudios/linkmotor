<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pool\LinkmotorBundle\Entity\Project;

class DomainRepository extends EntityRepository
{
    /**
     * @param Project $project
     * @param $filter
     * @return array
     */
    public function getForProject(Project $project, $filter = array())
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Domain')
            ->createQueryBuilder('d')
            ->join('d.subdomains', 'sd')
            ->join('sd.pages', 'p')
            ->where('p.project = :project')
            ->setParameter('project', $project->getId());

        if (isset($filter['keyword']) && $filter['keyword']) {
            $query->leftJoin('d.vendor', 'v');
            $like = $query->expr()->orX(
                $query->expr()->like('v.name', ':keywordName'),
                $query->expr()->like('v.email', ':keywordEmail'),
                $query->expr()->like('v.company', ':keywordCompany'),
                $query->expr()->like('d.name', ':keywordDomain')
            );
            $query = $query->andWhere($like)
                ->setParameter('keywordName', '%' . $filter['keyword'] . '%')
                ->setParameter('keywordEmail', '%' . $filter['keyword'] . '%')
                ->setParameter('keywordCompany', '%' . $filter['keyword'] . '%')
                ->setParameter('keywordDomain', '%' . $filter['keyword'] . '%');
        }

        return $query->orderBy('d.name', 'ASC')
            ->getQuery()->getResult();
    }

    public function getNextDomainToCrawl($interval)
    {
        $limit = new \DateTime(date('Y-m-d H:i:s', strtotime('-' . $interval)));
        $domains = $this->getEntityManager()
            ->createQuery(
                "SELECT d
                 FROM PoolLinkmotorBundle:Domain d
                 WHERE d.lastCrawledAt IS NULL OR d.lastCrawledAt < :timeLimit
                 ORDER BY d.lastCrawledAt ASC"
            )
            ->setMaxResults(1)
            ->setParameter('timeLimit', $limit)
            ->getResult();

        if ($domains) {
            return $domains[0];
        }

        return null;
    }
}
