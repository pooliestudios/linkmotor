<?php
namespace Pool\LinkmotorBundle\Tests\Service;

use Pool\LinkmotorBundle\Service\Domains;

class DomainsTest extends \PHPUnit_Framework_TestCase
{
    public function testIsDomain()
    {
        $domainsService = new Domains(null);

        $this->assertFalse($domainsService->isDomain('www.ausbildung.de'));
        $this->assertTrue($domainsService->isDomain('ausbildung.de'));
        $this->assertTrue($domainsService->isDomain('ausbildung.co.uk'));
        $this->assertFalse($domainsService->isDomain('ausbildung.co.zz'));
        $this->assertFalse($domainsService->isDomain('www'));
    }

    public function testIsSubDomain()
    {
        $domainsService = new Domains(null);

        $this->assertTrue($domainsService->isSubDomain('www.ausbildung.de'));
        $this->assertTrue($domainsService->isSubDomain('www.ausbildung.co.uk'));
        $this->assertFalse($domainsService->isSubDomain('ausbildung.de'));
        $this->assertFalse($domainsService->isSubDomain('www'));
    }

    public function testGetDomain()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('ausbildung.de', $domainsService->getDomain('www.ausbildung.de'));
        $this->assertEquals('ausbildung.de', $domainsService->getDomain('ausbildung.de'));
    }

    public function testGetDomainWithSubSubDomain()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('bayern.de', $domainsService->getDomain('www.km.bayern.de'));
    }

    public function testGetSubDomain()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('www', $domainsService->getSubDomain('www.ausbildung.de'));
        $this->assertEquals('', $domainsService->getSubDomain('ausbildung.de'));
    }

    public function testGetSubDomainWithSubSubDomain()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('www.km', $domainsService->getSubDomain('www.km.bayern.de'));
    }

    // Multi-Part Domain
    public function testGetDomainWithMultiPartFLD()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('ausbildung.co.uk', $domainsService->getDomain('www.ausbildung.co.uk'));
        $this->assertEquals('ausbildung.co.uk', $domainsService->getDomain('ausbildung.co.uk'));
    }

    public function testGetDomainWithSubSubDomainAndMultiPartFLD()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('bayern.co.uk', $domainsService->getDomain('www.km.bayern.co.uk'));
    }

    public function testGetSubDomainWithMultiPartFLD()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('www', $domainsService->getSubDomain('www.ausbildung.co.uk'));
        $this->assertEquals('', $domainsService->getSubDomain('ausbildung.co.uk'));
    }

    public function testGetSubDomainWithSubSubDomainAndMultiPartFLD()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('www.km', $domainsService->getSubDomain('www.km.bayern.co.uk'));
    }

    public function testGetDomainWithFakeMultiPartFLD()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('co.zz', $domainsService->getDomain('www.ausbildung.co.zz'));
        $this->assertEquals('co.zz', $domainsService->getDomain('ausbildung.co.zz'));
    }

    public function testGetSubDomainWithSubSubDomainAndFakeMultiPartFLD()
    {
        $domainsService = new Domains(null);

        $this->assertEquals('www.km.bayern', $domainsService->getSubDomain('www.km.bayern.co.zz'));
    }
}
