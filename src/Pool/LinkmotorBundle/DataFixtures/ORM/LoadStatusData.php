<?php

namespace Pool\LinkmotorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pool\LinkmotorBundle\Entity\Status;

class LoadStatusData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $statusTexts = array(
            1 => 'New',
            2 => 'Relevant',
            3 => 'Not relevant',
            4 => '1. Contact',
            5 => '2. Contact',
            6 => 'Linked',
            7 => 'Offline',
            8 => 'In progress'
        );
        foreach ($statusTexts as $id => $statusText) {
            $status = new Status();
            $status->setId($id);
            $status->setName($statusText);
            if ($id == 8) {
                $status->setSortOrder(55);
            } else {
                $status->setSortOrder($id * 10);
            }

            $manager->persist($status);
            $manager->flush();
        }
    }
}
