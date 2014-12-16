<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class AlertRepository extends EntityRepository
{
    /**
     * @param Project $project
     * @param User $user
     *
     * @return \Doctrine\ORM\Query
     */
    public function getQueryNewest(Project $project = null, User $user = null)
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Alert')
            ->createQueryBuilder('a')
            ->where('a.hideUntil IS NULL OR a.hideUntil <= :now')
            ->setParameter('now', new \DateTime());

        if ($project) {
            $query = $query->andWhere('a.project = :project')
                ->setParameter('project', $project->getId());
        }

        if ($user) {
            $query = $query->andWhere('a.user = :user')
                ->setParameter('user', $user->getId());
        }

        return $query->orderBy('a.createdAt', 'DESC')
            ->getQuery();
    }

    /**
     * @todo COUNT direkt in der Query machen
     * @param Project $project
     * @param User $user
     * @return int
     */
    public function getCount(Project $project = null, User $user = null)
    {
        $query = $this->getQueryNewest($project, $user);

        return count($query->getResult());
    }

    /**
     * @param $type
     * @param NotificationSetting $notificationSetting
     * @param Project $project
     * @param $which
     * @return int
     */
    public function getCountForNotification($type, NotificationSetting $notificationSetting, Project $project, $which)
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Alert')
            ->createQueryBuilder('a')
            ->select('COUNT (a.id)')
            ->where('a.hideUntil IS NULL OR a.hideUntil <= :now')
            ->andWhere('a.project = :project')
            ->andWhere('a.type = :type')
            ->setParameter('now', new \DateTime())
            ->setParameter('project', $project)
            ->setParameter('type', $type);

        if ($type == 'w') {
            if (!$notificationSetting->getAllWarnings()) {
                $query->andWhere('a.user = :user')
                    ->setParameter('user', $notificationSetting->getUser());
            }
        } else {
            if (!$notificationSetting->getAllErrors()) {
                $query->andWhere('a.user = :user')
                    ->setParameter('user', $notificationSetting->getUser());
            }
        }

        if ($which == 'new') {
            $limitDays = 7;
            if ($type == 'w') {
                if ($notificationSetting->getWarningsWhen() == 8) {
                    $limitDays = 1;
                }
            } else {
                if ($notificationSetting->getErrorsWhen() == 8) {
                    $limitDays = 1;
                }
            }
            $query->andWhere('a.createdAt >= :limitNew')
                ->setParameter('limitNew', date('Y-m-d H:i:s', strtotime("-{$limitDays} days")));
        }

        return $query->getQuery()->getSingleScalarResult();
    }
}
