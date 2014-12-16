<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    /**
     * @param string $name
     * @return array
     */
    public function getUserByName($name)
    {
        // First: find by name
        $users = $this->getEntityManager()
            ->createQuery(
                'SELECT u
                     FROM PoolLinkmotorBundle:User u
                     WHERE u.name = :name'
            )
            ->setParameter('name', $name)
            ->getResult();

        if (count($users) == 1) {
            return $users[0];
        }

        // Not found, or no unique match. Now search by email
        $users = $this->getEntityManager()
            ->createQuery(
                'SELECT u
                     FROM PoolLinkmotorBundle:User u
                     WHERE u.email = :name'
            )
            ->setParameter('name', $name)
            ->getResult();

        if (count($users) == 1) {
            return $users[0];
        }

        return null;
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllUsersQueryBuilder()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:User')
            ->createQueryBuilder('u')
            ->orderBy('u.inactive', 'ASC')
            ->addOrderBy('u.name', 'ASC');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllNonSupportUsersQueryBuilder()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:User')
            ->createQueryBuilder('u')
            ->where('u.email != :supportEmail')
            ->setParameter('supportEmail', 'support@linkmotor.de')
            ->orderBy('u.inactive', 'ASC')
            ->addOrderBy('u.name', 'ASC');
    }

    /**
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getAllActiveNonSupportUsersQueryBuilder()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:User')
            ->createQueryBuilder('u')
            ->where('u.email != :supportEmail AND u.inactive = 0')
            ->setParameter('supportEmail', 'support@linkmotor.de')
            ->addOrderBy('u.name', 'ASC');
    }

    /**
     * @return int
     */
    public function getNumNonSupportUsers()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:User')
            ->createQueryBuilder('u')
            ->where('u.email != :supportEmail')
            ->setParameter('supportEmail', 'support@linkmotor.de')
            ->select('COUNT(u.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return mixed
     */
    public function getAllNonSupportUsers()
    {
        return $this->getAllNonSupportUsersQueryBuilder()->getQuery()->getResult();
    }

    /**
     * @return mixed
     */
    public function getAllActiveNonSupportUsers()
    {
        return $this->getAllActiveNonSupportUsersQueryBuilder()->getQuery()->getResult();
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getAllNonSupportUsersQueryBuilder()
            ->select('COUNT(u)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param int $limit
     * @return array
     */
    public function getSelectableUsers($limit)
    {
        $queryBuilder = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:User')
            ->createQueryBuilder('u')
            ->select('u')
            ->where('u.email != :supportEmail')
            ->setParameter('supportEmail', 'support@linkmotor.de');

        if ($limit) {
            $queryBuilder->setMaxResults($limit)
                ->orderBy('u.id', 'ASC');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
