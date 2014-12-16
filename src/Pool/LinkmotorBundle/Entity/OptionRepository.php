<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class OptionRepository extends EntityRepository
{
    public function getStartingWith($startingWith)
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Option')
            ->createQueryBuilder('o');

        if ($startingWith) {
            $query = $query
                ->where('o.name LIKE :startingWith')
                ->setParameter('startingWith', "{$startingWith}%");
        }

        return $query->getQuery()->getResult();
    }
}
