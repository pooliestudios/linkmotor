<?php

namespace Pool\LinkmotorBundle\Tests\Entity;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class StatusFunctionalTest extends WebTestCase
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
        */
    }

    public function testStatusFixturesWorked()
    {
        #$status = $this->em->getRepository('PoolLinkmotorBundle:Status')->findAll();

        #$this->assertCount(8, $status);
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        #parent::tearDown();
        #$this->em->close();
    }
}
