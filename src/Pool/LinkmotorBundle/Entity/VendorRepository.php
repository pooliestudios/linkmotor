<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pool\LinkmotorBundle\Entity\Project;

class VendorRepository extends EntityRepository
{
    /**
     * @todo Tatsächlich nach Projekt filtern
     * @param Project $project
     * @param array $filter
     * @return \Doctrine\ORM\Query
     */
    public function getForProject(Project $project, $filter = array())
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Vendor')
            ->createQueryBuilder('v');

        if (isset($filter['keywordVendor']) && $filter['keywordVendor']) {
            $query = $query->where('v.name LIKE :keyword OR v.email LIKE :keyword OR v.company LIKE :keyword')
                ->setParameter('keyword', '%' . $filter['keywordVendor'] . '%');
        }

        return $query->getQuery()->getResult();
    }
}
