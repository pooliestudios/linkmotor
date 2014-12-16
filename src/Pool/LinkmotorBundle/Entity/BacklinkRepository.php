<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\EntityRepository;

class BacklinkRepository extends EntityRepository
{
    /**
     * @param Project $project
     * @param User $user
     * @param array $filter
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getQueryForBacklinkIndex(Project $project, User $user = null, $filter = array())
    {
        $query = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('b', 'CONCAT(CONCAT(sd.name, d.name), p.url) completeUrl')
            ->join('b.page', 'p')
            ->join('p.subdomain', 'sd')
            ->join('sd.domain', 'd')
            ->leftJoin('d.vendor', 'v')
            ->where('b.project = :project')
            ->setParameter('project', $project->getId());

        if ($user) {
            $query = $query->andWhere('b.assignedTo = :user')
                ->setParameter('user', $user->getId());
        }

        if (isset($filter['backlinkStatus']) && $filter['backlinkStatus'] != 'all') {
            if ($filter['backlinkStatus'] == 'online') {
                $query = $query->andWhere('b.isOffline = 0');
            } elseif ($filter['backlinkStatus'] == 'offline') {
                $query = $query->andWhere('b.isOffline = 1');
            } elseif ($filter['backlinkStatus'] == 'pending') {
                $query = $query->andWhere('b.lastCrawledAt IS NULL');
            } elseif ($filter['backlinkStatus'] == 'error') {
                $query = $query->leftJoin('b.alerts', 'a')
                    ->where(
                        'a.project = :alertProject AND a.type = :alertType
                         AND (a.hideUntil IS NULL OR a.hideUntil <= :hideUntil)'
                    )
                    ->setParameters(
                        array(
                            'alertProject' => $project,
                            'alertType' => 'e',
                            'hideUntil' => new \DateTime()
                        )
                    );
                if ($user) {
                    $query = $query->andWhere('a.user = :alertUser')
                        ->setParameter('alertUser', $user->getId());
                }
            } elseif ($filter['backlinkStatus'] == 'ok') {
                $query = $query->leftJoin('b.alerts', 'a')
                    ->andWhere('a.type IS NULL AND b.lastCrawledAt IS NOT NULL');
            } elseif ($filter['backlinkStatus'] == 'warning') {
                $query = $query->leftJoin('b.alerts', 'a')
                    ->where('a.project = :alertProject AND a.type = :alertType AND (a.hideUntil IS NULL OR a.hideUntil <= :hideUntil)')
                    ->setParameters(
                        array(
                            'alertProject' => $project,
                            'alertType' => 'w',
                            'hideUntil' => new \DateTime()
                        )
                    );
            } elseif ($filter['backlinkStatus'] == 'alerts') {
                $query = $query->leftJoin('b.alerts', 'a')
                    ->where('a.project = :alertProject AND (a.hideUntil IS NULL OR a.hideUntil <= :hideUntil)')
                    ->setParameters(
                        array(
                            'alertProject' => $project,
                            'hideUntil' => new \DateTime()
                        )
                    );
                if ($user) {
                    $query = $query->andWhere('a.user = :alertUser')
                        ->setParameter('alertUser', $user->getId());
                }
            }
        }
        if (isset($filter['crawlType']) && $filter['crawlType'] && $filter['crawlType'] != 'all') {
            $query = $query->andWhere('b.crawlType = :crawlType')
                ->setParameter('crawlType', $filter['crawlType']);
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
            $query = $query->andWhere('b.assignedTo = :user')
                ->setParameter('user', $filter['user']);
        }
        if (isset($filter['date']) && $filter['date']) {
            if ($filter['date'] == 'last-7-days') {
                $query->andWhere('b.createdAt >= :sevenDaysAgo')
                    ->setParameter('sevenDaysAgo', new \DateTime(date('Y-m-d 00:00:00', strtotime('-7 days'))));
            }
            if ($filter['date'] == 'last-14-days') {
                $query->andWhere('b.createdAt >= :fourteenDaysAgo')
                    ->setParameter('fourteenDaysAgo', new \DateTime(date('Y-m-d 00:00:00', strtotime('-14 days'))));
            }
            if ($filter['date'] == 'last-30-days') {
                $query->andWhere('b.createdAt >= :thirtyDaysAgo')
                    ->setParameter('thirtyDaysAgo', new \DateTime(date('Y-m-d 00:00:00', strtotime('-30 days'))));
            }
            if ($filter['date'] == 'saved') {
                if ($filter['dateFrom']) {
                    try {
                        $dateFrom = new \DateTime("{$filter['dateFrom']} 00:00:00");
                        $query->andWhere('b.createdAt >= :dateFrom')
                            ->setParameter('dateFrom', $dateFrom);
                    } catch (\Exception $e) {
                        // Nichts tun, der Fall sollte eigentlich schon beim Setzen des Filters abgefangen werden
                    }
                }
                if ($filter['dateTo']) {
                    try {
                        $dateTo = new \DateTime("{$filter['dateTo']} 23:59:59");
                        $query->andWhere('b.createdAt <= :dateTo')
                            ->setParameter('dateTo', $dateTo);
                    } catch (\Exception $e) {
                        // Nichts tun, der Fall sollte eigentlich schon beim Setzen des Filters abgefangen werden
                    }

                }
            }
        }
        if (isset($filter['costType']) && $filter['costType'] != '') {
            $query = $query->andWhere('b.costType = :costType')
                ->setParameter('costType', $filter['costType']);
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

    public function getNewestBacklinksForProject($project, $limit)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT b
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project
                 ORDER BY b.createdAt DESC"
            )
            ->setMaxResults($limit)
            ->setParameter('project', $project)
            ->getResult();
    }

    public function getNextBacklinkToCrawl($interval)
    {
        $limit = new \DateTime(date('Y-m-d H:i:s', strtotime('-' . $interval)));
        $backlinks = $this->getEntityManager()
            ->createQuery(
                "SELECT b
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE (b.isOffline = 0) AND (b.lastCrawledAt IS NULL OR b.lastCrawledAt < :timeLimit)
                 ORDER BY b.lastCrawledAt ASC"
            )
            ->setMaxResults(1)
            ->setParameter('timeLimit', $limit)
            ->getResult();

        if ($backlinks) {
            return $backlinks[0];
        }

        return null;
    }

    public function findSame(Backlink $backlink)
    {
        // When $backlink has no ID, the query should still work this way
        $id = $backlink->getId();
        if (!$id) {
            $id = 0;
        }
        $sameBacklinks = $this->getEntityManager()
            ->createQuery(
                "SELECT b
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.id <> :id AND b.page = :page AND b.url = :url AND b.type = :type AND b.anchor = :anchor"
            )->setParameters(
                array(
                    'id' => $id,
                    'page' => $backlink->getPage(),
                    'url' => $backlink->getUrl(),
                    'type' => $backlink->getType(),
                    'anchor' => $backlink->getAnchor()
                )
            )
            ->getResult();

        if ($sameBacklinks) {
            return $sameBacklinks[0];
        }

        return null;
    }

    /**
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('COUNT(b)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getNumFollow(Project $project)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.follow = 1 AND b.crawlType = 'dom' "
            )->setParameter('project', $project)
            ->getSingleScalarResult();
    }

    public function getNumNofollow(Project $project)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.follow = 0 AND b.crawlType = 'dom' "
            )->setParameter('project', $project)
            ->getSingleScalarResult();
    }

    public function getNumTypeImage(Project $project)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.crawlType = 'dom' AND b.type = 'i'"
            )->setParameter('project', $project)
            ->getSingleScalarResult();
    }

    public function getNumTypeText(Project $project)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.crawlType = 'dom' AND b.type = 't'"
            )->setParameter('project', $project)
            ->getSingleScalarResult();
    }

    public function getNumBacklinksOnline(Project $project)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0"
            )->setParameter('project', $project)
            ->getSingleScalarResult();
    }

    public function getNumDomains(Project $project)
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('COUNT(d.id)')
            ->join('b.page', 'p')
            ->join('p.subdomain', 'sd')
            ->join('sd.domain', 'd')
            ->where('b.project = :project AND b.isOffline = 0')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAvgDomainAuthority(Project $project)
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('AVG(d.authority)')
            ->join('b.page', 'p')
            ->join('p.subdomain', 'sd')
            ->join('sd.domain', 'd')
            ->where('b.project = :project AND b.isOffline = 0')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAvgPageAuthority(Project $project)
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('AVG(p.authority)')
            ->join('b.page', 'p')
            ->where('b.project = :project AND b.isOffline = 0')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAvgDomainNetPop(Project $project)
    {
        return $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('AVG(d.netPop)')
            ->join('b.page', 'p')
            ->join('p.subdomain', 'sd')
            ->join('sd.domain', 'd')
            ->where('b.project = :project AND b.isOffline = 0')
            ->setParameter('project', $project)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getAuthoritySpread(Project $project)
    {
        $pageAuthorities = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('p.authority')
            ->join('b.page', 'p')
            ->where('b.project = :project AND b.isOffline = 0')
            ->setParameter('project', $project)
            ->getQuery()
            ->getScalarResult();

        $domainAuthorities = $this->getEntityManager()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->createQueryBuilder('b')
            ->select('d.authority')
            ->join('b.page', 'p')
            ->join('p.subdomain', 'sd')
            ->join('sd.domain', 'd')
            ->where('b.project = :project AND b.isOffline = 0')
            ->setParameter('project', $project)
            ->getQuery()
            ->getScalarResult();

        $spread = array(
            '10' => array('page' => 0, 'domain' => 0),
            '20' => array('page' => 0, 'domain' => 0),
            '30' => array('page' => 0, 'domain' => 0),
            '40' => array('page' => 0, 'domain' => 0),
            '50' => array('page' => 0, 'domain' => 0),
            '60' => array('page' => 0, 'domain' => 0),
            '70' => array('page' => 0, 'domain' => 0),
            '80' => array('page' => 0, 'domain' => 0),
            '90' => array('page' => 0, 'domain' => 0),
            '100' => array('page' => 0, 'domain' => 0),
        );

        foreach ($pageAuthorities as $item) {
            $bucket = 10 * ceil($item['authority'] / 10);
            if ($bucket == 0) {
                $bucket = '10';
            }
            $spread[$bucket]['page']++;
        }
        foreach ($domainAuthorities as $item) {
            $bucket = 10 * ceil($item['authority'] / 10);
            if ($bucket == 0) {
                $bucket = '10';
            }
            $spread[$bucket]['domain']++;
        }

        $result = array('page' => array(), 'domain' => array());
        foreach ($spread as $item) {
            $result['page'][] = $item['page'];
            $result['domain'][] = $item['domain'];
        }

        return $result;
    }

    public function getTopLinkTargets(Project $project, $limit)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id) AS number, b.url
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.crawlType = 'DOM'
                 GROUP BY b.url
                 ORDER BY number DESC, b.url ASC"
            )
            ->setMaxResults($limit)
            ->setParameter('project', $project)
            ->getScalarResult();
    }

    public function getTopAnchorTexts(Project $project, $limit)
    {
        return $this->getEntityManager()
            ->createQuery(
                "SELECT COUNT(b.id) AS number, b.anchor
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.type = 't' AND b.crawlType = 'DOM' AND b.anchor <> ''
                 GROUP BY b.anchor
                 ORDER BY number DESC, b.anchor ASC"
            )
            ->setMaxResults($limit)
            ->setParameter('project', $project)
            ->getScalarResult();
    }

    public function getOneTimeCosts(Project $project)
    {
        $value = $this->getEntityManager()
            ->createQuery(
                "SELECT SUM(b.price)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.costType = 1"
            )->setParameter('project', $project)
            ->getSingleScalarResult();

        if (!$value) {
            $value = 0;
        }

        return $value;
    }

    public function getMonthlyCosts(Project $project)
    {
        $monthly = $this->getEntityManager()
            ->createQuery(
                "SELECT SUM(b.price)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.costType = 2"
            )->setParameter('project', $project)
            ->getSingleScalarResult();
        if (!$monthly) {
            $monthly = 0;
        }

        $yearly = $this->getEntityManager()
            ->createQuery(
                "SELECT SUM(b.price)
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.costType = 3"
            )->setParameter('project', $project)
            ->getSingleScalarResult();
        if (!$yearly) {
            $yearly = 0;
        }

        return round($monthly + $yearly/12);
    }

    public function getCostsToToday(Project $project)
    {
        $backlinks = $this->getEntityManager()
            ->createQuery(
                "SELECT b
                 FROM PoolLinkmotorBundle:Backlink b
                 WHERE b.project = :project AND b.isOffline = 0 AND b.costType IN (1, 2, 3)"
            )->setParameter('project', $project)
            ->getResult();

        $value = 0;
        $today = new \DateTime();
        foreach ($backlinks as $backlink) {
            switch ($backlink->getCostType()) {
                case 1: // einmalig
                    $value += $backlink->getPrice();
                    break;
                case 2: // monatlich
                    $diff = $backlink->getCreatedAt()->diff($today);
                    $months = $diff->m + 12 * $diff->y;
                    $value += ($months + 1) * $backlink->getPrice(); // +1, dmit jeder angefangene Monat zählt
                    break;
                case 3: // jährlich
                    $diff = $backlink->getCreatedAt()->diff($today);
                    $value += ($diff->y + 1) * $backlink->getPrice(); // +1, dmit jedes angefangene Jahr zählt
                    break;
            }
        }

        return $value;
    }
}
