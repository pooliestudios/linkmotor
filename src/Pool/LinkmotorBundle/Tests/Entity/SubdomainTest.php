<?php
namespace Pool\LinkmotorBundle\Tests\Entity;

use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Subdomain;

class SubDomainTest extends \PHPUnit_Framework_TestCase
{
    public function testUrlMatches()
    {
        $domain = new Domain();
        $domain->setName('ausbildung.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);

        $url = 'http://www.ausbildung.de/';

        $this->assertTrue($domain->urlMatches($url));
    }

    public function testUrlMatchesWithoutProtocol()
    {
        $domain = new Domain();
        $domain->setName('ausbildung.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);

        $url = 'www.ausbildung.de/';

        $this->assertFalse($domain->urlMatches($url));
    }
}
