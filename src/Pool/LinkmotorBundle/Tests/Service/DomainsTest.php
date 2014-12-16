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
        $this->assertFalse($domainsService->isDomain('www'));
    }

    public function testIsSubDomain()
    {
        $domainsService = new Domains(null);

        $this->assertTrue($domainsService->isSubDomain('www.ausbildung.de'));
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
}
