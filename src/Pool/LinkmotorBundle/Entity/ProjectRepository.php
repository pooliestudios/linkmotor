<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository
{
    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Project')
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSelectableProjects($limit)
    {
        $queryBuilder = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Project')
            ->createQueryBuilder('p')
            ->select('p');

        if ($limit) {
            $queryBuilder->setMaxResults($limit)
                ->orderBy('p.id', 'ASC');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
