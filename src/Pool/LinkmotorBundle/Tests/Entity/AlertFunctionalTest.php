<?php

namespace Pool\LinkmotorBundle\Tests\Entity;

use Pool\LinkmotorBundle\Entity\Alert;
use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Page;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AlertFunctionalTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        /*
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $this->em = static::$kernel->getContainer()
            ->get('doctrine')
            ->getManager();

        $this->truncate('alerts');
        $this->truncate('backlinks');
        $this->truncate('pages');

        $page = new Page();
        $page->setUrl('/');
        $page->setScheme('http');
        $page->setStatus($this->em->getRepository('PoolLinkmotorBundle:Status')->find(6));
        $this->em->persist($page);

        $backlink = new Backlink();
        $backlink->setPage($page);

        $backlink->setUrl('http://pooliestudios.com');
        $backlink->setFollow(true);
        $backlink->setUrlOk(true);
        $backlink->setFollowOk(true);
        $backlink->setAnchorOk(true);
        $backlink->setTypeOk(true);
        $backlink->setStatusCodeOk(true);
        $backlink->setMetaFollowOk(true);
        $backlink->setMetaIndexOk(true);
        $backlink->setXPathOk(false);
        $backlink->setMetaFollowOk(true);
        $backlink->setMetaIndexOk(true);
        $backlink->setXRobotsFollowOk(true);
        $backlink->setXRobotsIndexOk(true);
        $backlink->setRobotsGoogleOk(true);
        $backlink->setLastCrawledAt(new \DateTime());
        $this->em->persist($backlink);
        $this->em->flush();

        // Nun wurde bereits automatisch ein Warning-Alert angelegt
        $alerts = $backlink->getAlerts();
        $alert = $alerts[0];
        $alert->setHideUntil(new \DateTime('+1 month'));
        $this->em->persist($alert);

        $this->em->flush();
        */
    }

    public function testChangingStatusFromWarningToAlertShouldResetHideUntil()
    {
        /*
        $backlinks = $this->em->getRepository('PoolLinkmotorBundle:Backlink')->findAll();
        $backlink = $backlinks[0];
        // Es existiert ein Alert mit Status "Warnung" und einem HideUntil in der Zukunft
        $alerts = $backlink->getAlerts();
        $alert = $alerts[0];

        $this->assertCount(1, $alerts);
        $this->assertEquals('w', $alert->getType());
        $this->assertTrue($alert->getHideUntil()->format('Y-m-d') > date('Y-m-d'));

        // Der Backlink wird verändert, so dass der Alert nicht mehr in Ordnung sein wird
        $backlink->setFollowOk(false);
        $this->em->persist($backlink);
        $this->em->flush();

        $alerts = $backlink->getAlerts();
        $this->assertCount(1, $alerts);
        $alert = $alerts[0];

        $this->assertEquals('e', $alert->getType());
        $this->assertNull($alert->getHideUntil());
        */
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        /*
        $this->truncate('backlinks');
        $this->truncate('alerts');
        $this->truncate('pages');

        parent::tearDown();
        $this->em->close();
        */
    }

    protected function truncate($tableName)
    {
        #$this->em->getConnection()->executeUpdate("DELETE FROM {$tableName}");
    }
}
