<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Project;

class CrawlLogRepository extends EntityRepository
{
    /**
     * @param Backlink $backlink
     * @return \Doctrine\ORM\Query
     */
    public function getLastEntry(Backlink $backlink)
    {
        return $this->getEntityManager()
            ->createQuery(
                'SELECT l
                     FROM PoolLinkmotorBundle:CrawlLog l
                     WHERE l.backlink = :backlink
                     ORDER BY l.createdAt DESC'
            )
            ->setMaxResults(1)
            ->setParameter('backlink', $backlink)
            ->getResult();
    }

    /**
     * @param $limit
     * @param Project $project
     * @return array
     */
    public function getNewest($limit, Project $project = null)
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:CrawlLog')
            ->createQueryBuilder('l');

        if ($project) {
            $query->andWhere('l.project = :project ')
                ->setParameter('project', $project);
        }

        $query->orderBy('l.createdAt', 'DESC')->setMaxResults($limit);

        return $query->getQuery()->getResult();
    }
}
