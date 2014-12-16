<?php
namespace Pool\LinkmotorBundle\Tests\Entity;

use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\Domain;

class ProjectTest extends \PHPUnit_Framework_TestCase
{
    public function testEqualsEmptyProjects()
    {
        $project1 = new Project();
        $project2 = new Project();

        $this->assertTrue($project1->equals($project2));
    }

    public function testEqualsSameDomainName()
    {
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);

        $project2 = new Project();
        $domain2 = new Domain();
        $domain2->setName('test.de');
        $project2->setDomain($domain2);

        $this->assertTrue($project1->equals($project2));
    }

    public function testEqualsSameDomain()
    {
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);

        $project2 = new Project();
        $project2->setDomain($domain1);

        $this->assertTrue($project1->equals($project2));
    }

    public function testEqualsDifferentDomainName()
    {
        $domain1 = new Domain();
        $domain1->setName('test.de');
        $project1 = new Project();
        $project1->setDomain($domain1);

        $project2 = new Project();
        $domain2 = new Domain();
        $domain2->setName('test.com');
        $project2->setDomain($domain2);

        $this->assertFalse($project1->equals($project2));
    }
}
