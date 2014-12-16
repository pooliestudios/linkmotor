<?php
namespace Pool\LinkmotorBundle\Tests\Service;

use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Pool\LinkmotorBundle\Entity\User;
use Pool\LinkmotorBundle\Service\PageCreator;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PageCreatorFunctionalTest extends WebTestCase
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    public function testAddPageLeavesUrlCaseIntact()
    {
        /*
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $doctrine = static::$kernel->getContainer()->get('doctrine');
        $this->em = $doctrine->getManager();

        $pageCreator = new PageCreator($doctrine);

        $domain = new Domain();
        $domain->setName('test.de');
        $this->em->persist($domain);

        $project = new Project();
        $project->setDomain($domain);
        $this->em->persist($project);
        $user = new User();
        $user->setEmail('test@test.de');
        $this->em->persist($user);
        $this->em->flush();

        $url = 'http://www.marken-moebel.com/Design-Bestseller.html?a=Test';
        $page = $pageCreator->addPage($project, $url, $user);

        $this->assertEquals('/Design-Bestseller.html?a=Test', $page->getUrl());
        */
    }

    public function testAddPageDeniedForSameDomainUrls()
    {
        /*
        static::$kernel = static::createKernel();
        static::$kernel->boot();
        $doctrine = static::$kernel->getContainer()->get('doctrine');
        $this->em = $doctrine->getManager();

        $pageCreator = new PageCreator($doctrine);

        $domain = new Domain();
        $domain->setName('connox.de');
        $this->em->persist($domain);

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $this->em->persist($subdomain);

        $project = new Project();
        $project->setSubdomain($subdomain);
        $this->em->persist($project);
        $user = new User();
        $user->setEmail('test@test.de');
        $this->em->persist($user);
        $this->em->flush();

        $url = 'http://www.connox.de/index.html';
        $page = $pageCreator->addPage($project, $url, $user);

        $this->assertEquals(false, $page);
        */
    }

    /**
     * {@inheritDoc}
     */
    protected function tearDown()
    {
        /*
        $this->deleteAll('Page');
        $this->deleteAll('User');
        $this->deleteAll('Project');
        $this->deleteAll('Subdomain');
        $this->deleteAll('Domain');

        parent::tearDown();
        $this->em->close();
        */
    }

    protected function deleteAll($entityName)
    {
        /*
        $items = $this->em->getRepository("PoolLinkmotorBundle:{$entityName}")->findAll();
        foreach ($items as $item) {
            $this->em->remove($item);
        }
        $this->em->flush();
        */
    }
}
