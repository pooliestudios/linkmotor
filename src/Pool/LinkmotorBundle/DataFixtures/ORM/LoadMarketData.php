<?php

namespace Pool\LinkmotorBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Pool\LinkmotorBundle\Entity\Market;

class LoadMarketData implements FixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $markets = array(
            'de' => array('Germany', 'Deutschland'),
            'at' => array('Austria', 'Österreich')
        );
        foreach ($markets as $isoCode => $marketNames) {
            $market = new Market();
            $market->setIsoCode($isoCode);
            $market->setNameEn($marketNames[0]);
            $market->setNameDe($marketNames[1]);

            $manager->persist($market);
            $manager->flush();
        }
    }
}
