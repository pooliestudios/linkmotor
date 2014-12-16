<?php
namespace Pool\LinkmotorBundle\Tests\Entity;

use Pool\LinkmotorBundle\Entity\Domain;

class DomainTest extends \PHPUnit_Framework_TestCase
{
    public function testUrlMatchesIssue70()
    {
        $domain = new Domain();
        $domain->setName('pooliestudios.com');

        $url = '//pooliestudios.com/';

        $this->assertTrue($domain->urlMatches($url));
    }

    public function testUrlMatchesTrue()
    {
        $domain = new Domain();
        $domain->setName('pooliestudios.com');

        $url = 'http://pooliestudios.com/';

        $this->assertTrue($domain->urlMatches($url));
    }

    public function testUrlMatchesHttpsTrue()
    {
        $domain = new Domain();
        $domain->setName('pooliestudios.com');

        $url = 'https://pooliestudios.com/';

        $this->assertTrue($domain->urlMatches($url));
    }

    public function testUrlMatchesFalse()
    {
        $domain = new Domain();
        $domain->setName('blogger.de');

        $url = 'http://pooliestudios.com/';

        $this->assertFalse($domain->urlMatches($url));
    }

    /**
     * @todo Klären, ob das wirklich das gewünschte Verhalten ist
     */
    public function testUrlMatchesSubdomain()
    {
        $domain = new Domain();
        $domain->setName('blogger.de');

        $url = 'http://www.blogger.de/';

        $this->assertTrue($domain->urlMatches($url));
    }

    public function testGetNameForDisplayIdn()
    {
        $domain = new Domain();
        $domain->setName('täst.de');

        $this->assertEquals($domain->getNameForDisplay(), 'täst.de');
    }

    public function testGetNameForDisplayAce()
    {
        $domain = new Domain();
        $domain->setName('xn--tst-qla.de');

        $this->assertEquals($domain->getNameForDisplay(), 'täst.de');
    }

    public function testGetNameIdn()
    {
        $domain = new Domain();
        $domain->setName('täst.de');

        $this->assertEquals($domain->getName(), 'xn--tst-qla.de');
    }

    public function testGetNameAce()
    {
        $domain = new Domain();
        $domain->setName('xn--tst-qla.de');

        $this->assertEquals($domain->getName(), 'xn--tst-qla.de');
    }
}
