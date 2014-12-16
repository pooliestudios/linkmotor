<?php

namespace Pool\LinkmotorBundle\EventListener;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Pool\LinkmotorBundle\Entity\ActionStats;
use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Page;

class StatsUpdater
{
    public function preUpdate(PreUpdateEventArgs $event)
    {
        $entity = $event->getEntity();

        if ($entity instanceof Page && $event->hasChangedField('status')) {
            $em = $event->getEntityManager();
            $logEntries = $em->getRepository('Gedmo\Loggable\Entity\LogEntry')
                ->getLogEntries($entity);

            $contactStatusIds = array(4, 5);

            $isFirstStatusChange = true;
            if (in_array($event->getNewValue('status')->getId(), $contactStatusIds)) {
                $isFirstContactStatusChange = true;
            } else {
                $isFirstContactStatusChange = false;
            }

            $isFirstLogEntry = true;
            foreach ($logEntries as $logEntry) {
                if ($logEntry->getAction() == 'update') {
                    if ($isFirstLogEntry) {
                        // little bit of a hack to presume the first entry is always the one
                        // that is about to be processed here and therefore needs to be ignored
                        $isFirstLogEntry = false;
                        continue;
                    }
                    $data = $logEntry->getData();
                    if (isset($data['status'])) {
                        $isFirstStatusChange = false;
                        if (in_array($data['status']['id'], $contactStatusIds)) {
                            $isFirstContactStatusChange = false;
                        }
                    }
                    if (!$isFirstContactStatusChange && !$isFirstStatusChange) {
                        break;
                    }
                }
            }

            if ($isFirstStatusChange || $isFirstContactStatusChange) {
                $today = new \DateTime();
                $actionStatsItem = $em->getRepository('PoolLinkmotorBundle:ActionStats')
                    ->findOneBy(
                        array(
                            'date' => $today,
                            'project' => $entity->getProject(),
                            'user' => $entity->getAssignedTo()
                        )
                    );
                if (!$actionStatsItem) {
                    $actionStatsItem = new ActionStats();
                    $actionStatsItem->setProject($entity->getProject());
                    $actionStatsItem->setUser($entity->getAssignedTo());
                    $actionStatsItem->setDate($today);
                }
                if ($isFirstStatusChange) {
                    $actionStatsItem->setNumCheckedPages($actionStatsItem->getNumCheckedPages() + 1);
                }
                if ($isFirstContactStatusChange) {
                    $actionStatsItem->setNumContactsMade($actionStatsItem->getNumContactsMade() + 1);
                }

                $em->persist($actionStatsItem);
                $em->flush();
            }
        }
    }

    /**
     * Currently only used for when backlinks are created, as no status can be set while creating a prospect
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Backlink) {
            $em = $args->getEntityManager();
            $actionStatsItem = $em->getRepository('PoolLinkmotorBundle:ActionStats')
                ->findOneBy(
                    array(
                        'date' => $entity->getCreatedAt(),
                        'project' => $entity->getProject(),
                        'user' => $entity->getAssignedTo()
                    )
                );
            if (!$actionStatsItem) {
                $actionStatsItem = new ActionStats();
                $actionStatsItem->setProject($entity->getProject());
                $actionStatsItem->setUser($entity->getAssignedTo());
                $actionStatsItem->setDate($entity->getCreatedAt());
            }
            $actionStatsItem->setNumBacklinksCreated($actionStatsItem->getNumBacklinksCreated() + 1);

            $em->persist($actionStatsItem);
            $em->flush();
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        if ($entity instanceof Backlink) {
            $em = $args->getEntityManager();
            $logEntries = $em->getRepository('Gedmo\Loggable\Entity\LogEntry')
                ->getLogEntries($entity);

            foreach ($logEntries as $logEntry) {
                if ($logEntry->getAction() == 'create') {
                    $data = $logEntry->getData();
                    $userId = $data['assignedTo']['id'];
                    $user = $em->getRepository('PoolLinkmotorBundle:User')->find($userId);
                    if (!$user) {
                        break;
                    }
                    $date = $data['createdAt'];
                    $actionStatsItem = $em->getRepository('PoolLinkmotorBundle:ActionStats')
                        ->findOneBy(
                            array(
                                'date' => $date,
                                'project' => $entity->getProject(),
                                'user' => $user
                            )
                        );
                    if (!$actionStatsItem) {
                        break;
                    }
                    $actionStatsItem->setNumBacklinksCreated($actionStatsItem->getNumBacklinksCreated() - 1);
                    if ($actionStatsItem->isEmpty()) {
                        $em->remove($actionStatsItem);
                    } else {
                        $em->persist($actionStatsItem);
                    }
                    $em->flush();
                    break;
                }
            }
        }
    }
}
