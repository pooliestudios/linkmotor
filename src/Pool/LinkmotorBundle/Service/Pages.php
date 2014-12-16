<?php

namespace Pool\LinkmotorBundle\Service;

use Pool\LinkmotorBundle\Entity\Competitor;
use Pool\LinkmotorBundle\Entity\Keyword;

class Pages
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    /**
     * @var PageCreator
     */
    private $pageCreator;

    /**
     * @var
     */
    private $seoServices;

    /**
     * Maximale Anzahl von Seiten, die vom Service angefragt werden, damit eine Endlosschleife ausgeschlossen wird.
     *
     * @var int $maxPages
     */
    private $maxPages;

    public function __construct($doctrine, PageCreator $pageCreator, SeoServices $seoServices)
    {
        $this->doctrine = $doctrine;
        $this->pageCreator = $pageCreator;
        $this->seoServices = $seoServices;

        $this->maxPages = 25;
    }

    /**
     * @param Competitor $competitor
     * @return int
     */
    public function importFromCompetitor(Competitor $competitor)
    {
        return $this->importFromKeywordOrCompetitor($competitor);
    }

    /**
     * @param Keyword $keyword
     * @return int
     */
    public function importFromKeyword(Keyword $keyword)
    {
        return $this->importFromKeywordOrCompetitor($keyword);
    }

    /**
     * // @todo interface einführen
     *
     * @param $object
     * @return int
     */
    private function importFromKeywordOrCompetitor($object)
    {
        if (get_class($object) == 'Pool\LinkmotorBundle\Entity\Competitor') {
            $which = 'competitor';
            $modus = 'new';
        } elseif (get_class($object) == 'Pool\LinkmotorBundle\Entity\Keyword') {
            $which = 'keyword';
            $modus = 'top';
        } else {
            return 0;
        }

        $em = $this->doctrine->getManager();
        if (!$object->getAssignedTo()) {
            $users = $this->doctrine->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        }

        $numReceived = 0;
        for ($page = 1; $page <= $this->maxPages; $page++) {
            if ($which == 'keyword') {
                $result = $this->seoServices->getUrlsByKeyword(
                    $object->getKeyword(),
                    $object->getMarket()->getIsoCode(),
                    $page
                );
            } elseif ($which == 'competitor') {
                $result = $this->seoServices->getUrlsByDomain($object->getDomain(), $page);
            } else {
                $result = array('data' => null);
            }

            $urls = $result['data'];
            if ($urls) {
                $project = $object->getProject();

                $selectedUserIndex = 0;
                foreach ($urls as $url) {
                    if ($modus == 'top') {
                        $numReceived++;
                    }
                    if ($numReceived >= $object->getImportLimit()) {
                        break;
                    }
                    if ($this->pageCreator->checkIfPageExists($project, $url) == true) {
                        continue;
                    }
                    $result = $this->pageCreator->checkIfSubdomainHasPages($project, $url);
                    if ($result['subdomain'] && $result['hasPages']) {
                        continue;
                    }
                    $user = $object->getAssignedTo();
                    if (!$user) {
                        $user = $users[$selectedUserIndex];
                        $selectedUserIndex++;
                        if ($selectedUserIndex >= count($users)) {
                            $selectedUserIndex = 0;
                        }
                    }
                    $createdPage = $this->pageCreator->addPage($project, $url, $user);
                    if ($createdPage) { // Domain could be blacklisted
                        if ($which == 'keyword') {
                            $createdPage->setSourceKeyword($object);
                        } else {
                            $createdPage->setSourceCompetitor($object);
                        }
                        $em->persist($createdPage);
                        $em->flush();

                        if ($modus == 'new') {
                            $numReceived++;
                        }
                    }
                }
            }

            if (!$urls || $numReceived >= $object->getImportLimit()) {
                break;
            }
        }

        $object->setLastImportAt(new \DateTime());

        $em->persist($object);
        $em->flush();

        return $numReceived;
    }
}
