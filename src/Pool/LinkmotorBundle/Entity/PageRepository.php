<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\User;
use Pool\LinkmotorBundle\Entity\Status;

class PageRepository extends EntityRepository
{
    /**
     * @param Project $project
     * @param User $user
     * @param array $filter
     * @return \Doctrine\ORM\AbstractQuery
     */
    public function getQueryForPagesIndex(Project $project, User $user = null, $filter = null)
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->createQueryBuilder('p')
            ->select('p', 'CONCAT(CONCAT(sd.name, d.name), p.url) completeUrl')
            ->join('p.status', 's')
            ->join('p.subdomain', 'sd')
            ->join('sd.domain', 'd')
            ->leftJoin('d.vendor', 'v')
            ->join('p.assignedTo', 'u')
            ->where('p.project = :project')
            ->setParameter('project', $project->getId());

        if ($user) {
            $query = $query->andWhere('p.assignedTo = :user')
                ->setParameter('user', $user->getId());
        }
        if (isset($filter['status']) && $filter['status'] && $filter['status'] != 'all') {
            $statusValues = Status::getValueForGroup($filter['status']);
            $query = $query->andWhere('p.status IN (:status)')
                ->setParameter('status', $statusValues);
        }

        if (isset($filter['subdomain']) && $filter['subdomain']) {
            $query = $query->andWhere('p.subdomain = :subdomain')
                ->setParameter('subdomain', $filter['subdomain']);
        }
        if (isset($filter['domain']) && $filter['domain']) {
            $query = $query->andWhere('sd.domain = :domain')
                ->setParameter('domain', $filter['domain']);
        }
        if (isset($filter['vendor']) && $filter['vendor']) {
            $query = $query->andWhere('d.vendor = :vendor')
                ->setParameter('vendor', $filter['vendor']);
        }
        if (isset($filter['user']) && $filter['user'] && !$user) {
            $query = $query->andWhere('p.assignedTo = :user')
                ->setParameter('user', $filter['user']);
        }
        if (isset($filter['keyword']) && $filter['keyword']) {
            $like = $query->expr()->like(
                $query->expr()->concat(
                    'sd.name',
                    $query->expr()->concat(
                        $query->expr()->literal('.'),
                        $query->expr()->concat('d.name', 'p.url')
                    )
                ),
                ':keyword'
            );
            $query->andWhere($like)
                ->setParameter('keyword', '%' . $filter['keyword'] . '%');
        }

        return $query;
    }

    /**
     * @param $limit
     * @param Project $project
     * @param User $user
     * @return array
     */
    public function getNewest($limit, Project $project = null, User $user = null)
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->createQueryBuilder('p')
            ->andWhere('p.status = 1');

        if ($user) {
            $query = $query->andWhere('p.assignedTo = :user')
                ->setParameter('user', $user);
        }

        if ($project) {
            $query = $query->andWhere('p.project = :project')
                ->setParameter('project', $project);

        }

        return $query->orderBy('p.createdAt', 'DESC')->setMaxResults($limit)->getQuery()->getResult();
    }

    /**
     * @param Domain $domain
     * @param Project $project
     *
     * @return array
     */
    public function getDeleteableForDomainAndProject(Domain $domain, Project $project)
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->createQueryBuilder('p')
            ->join('p.status', 's')
            ->join('p.subdomain', 'sd')
            ->where('p.project = :project AND p.status IN (:status) AND sd.domain=:domain')
            ->setParameters(
                array(
                    'project' => $project->getId(),
                    'status' => array(1, 3), // "Neu" oder "Nicht relevant"
                    'domain' => $domain->getId()
                )
            )
            ->getQuery()
            ->getResult();
    }

    public function getNextPageToCrawl($interval)
    {
        $limit = new \DateTime(date('Y-m-d H:i:s', strtotime('-' . $interval)));
        $pages = $this->getEntityManager()
            ->createQuery(
                "SELECT p
                 FROM PoolLinkmotorBundle:Page p
                 WHERE p.lastCrawledAt IS NULL OR p.lastCrawledAt < :timeLimit
                 ORDER BY p.lastCrawledAt ASC"
            )
            ->setMaxResults(1)
            ->setParameter('timeLimit', $limit)
            ->getResult();

        if ($pages) {
            return $pages[0];
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return int
     */
    public function getNewPagesCount(Project $project, User $user = null)
    {
        return $this->getPagesStatusCount(1, $project, $user);
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return int
     */
    public function getRelevantPagesCount(Project $project, User $user = null)
    {
        return $this->getPagesStatusCount(2, $project, $user);
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return int
     */
    public function getContactedPagesCount(Project $project, User $user = null)
    {
        return $this->getPagesStatusCount(array(4, 5), $project, $user);
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return int
     */
    public function getFirstContactPagesCount(Project $project, User $user = null)
    {
        return $this->getPagesStatusCount(4, $project, $user);
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return int
     */
    public function getSecondContactPagesCount(Project $project, User $user = null)
    {
        return $this->getPagesStatusCount(5, $project, $user);
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return int
     */
    public function getInProgressPagesCount(Project $project, User $user = null)
    {
        return $this->getPagesStatusCount(8, $project, $user);
    }

    /**
     * @param mixed $status
     * @param Project $project
     * @param User $user
     *
     * @return mixed
     */
    private function getPagesStatusCount($status, Project $project, User $user = null)
    {
        if (!is_array($status)) {
            $status = array($status);
        }
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->createQueryBuilder('p')
            ->select('COUNT(p)')
            ->andWhere('p.status IN (:status)')
            ->setParameter('status', $status)
            ->andWhere('p.project = :project')
            ->setParameter('project', $project);

        if ($user) {
            $query = $query->andWhere('p.assignedTo = :user')
                ->setParameter('user', $user);
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
