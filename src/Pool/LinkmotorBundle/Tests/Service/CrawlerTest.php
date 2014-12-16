<?php
namespace Pool\LinkmotorBundle\Tests\Service;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Service\Crawler;

class CrawlerTest extends \PHPUnit_Framework_TestCase
{
    public function testFindBestMatchingResult()
    {
        $crawler = new Crawler(null, null, null);

        $backlink = new Backlink();
        $backlink->setAnchor('test');
        $backlink->setFollow(true);
        $backlink->setType('t');
        $backlink->setIgnorePosition(true);

        $results = array(
            array(
                'anchor' => 'test',
                'follow' => false,
                'type' => 't',
                'xpath' => ''
            ),
            array(
                'anchor' => 'test',
                'follow' => false,
                'type' => 'i',
                'xpath' => ''
            )
        );

        $bestMatch = $crawler->findBestMatchingResult($results, $backlink);

        $this->assertEquals('t', $bestMatch['type']);
    }

    public function testGetImageAltText()
    {
        $content = '<img src="image_gl/sofort_180_normal.png" style="padding-left:6px; border:0px;"
                    alt="Sofortueberweisung - direct e-payment" id="sofortueberweisung" />';

        $crawler = new Crawler(null, null, null);
        $this->assertEquals($crawler->getImageAltText($content), 'Sofortueberweisung - direct e-payment');
    }

    public function testAnchorForImageLinks()
    {
        $content = '<div><a href="http://payment-network.com/sue_de" target="_new" title="sofortueberweisung.de">
                    <img src="image_gl/sofort_180_normal.png" style="padding-left:6px; border:0px;"
                    alt="Sofortueberweisung - direct e-payment" id="sofortueberweisung" /></a></div><div>
                    <img src="image_gl/rechnungbysofort_180.png" alt="cards"
                    style="padding-left:10px; padding-top:6px;" /></div>';
        $backlinkUrl = 'http://payment-network.com/sue_de';

        $crawler = new Crawler(null, null, '');
        $result = $crawler->searchBacklinkInContent($backlinkUrl, $content);

        $this->assertEquals($result[0]['anchor'], 'Sofortueberweisung - direct e-payment');
    }

    public function testConvertDomainToAscii()
    {
        $crawler = new Crawler(null, null, '');

        $url = 'http://möbel-onlineshop.net/designermoebel-schaffen-individualitaet/';
        $expected = 'http://xn--mbel-onlineshop-8sb.net/designermoebel-schaffen-individualitaet/';
        $this->assertEquals($crawler->convertDomainToAscii($url), $expected);
    }

    public function testConvertDomainToAsciiNoPath()
    {
        $crawler = new Crawler(null, null, '');

        $url = 'http://olbertz.de';
        $expected = 'http://olbertz.de';
        $this->assertEquals($crawler->convertDomainToAscii($url), $expected);
    }

    public function testConvertDomainToAsciiNoSchemeNoPath()
    {
        $crawler = new Crawler(null, null, '');

        $url = 'olbertz.de';
        $expected = 'http://olbertz.de';
        $this->assertEquals($crawler->convertDomainToAscii($url), $expected);
    }

    public function testConvertDomainToAsciiNoSchemeNoPathIdn()
    {
        $crawler = new Crawler(null, null, '');

        $url = 'möbel-onlineshop.net';
        $expected = 'http://xn--mbel-onlineshop-8sb.net';
        $this->assertEquals($crawler->convertDomainToAscii($url), $expected);
    }

    public function testConvertDomainToAsciiNoScheme()
    {
        $crawler = new Crawler(null, null, '');

        $url = 'olbertz.de/test';
        $expected = 'http://olbertz.de/test';
        $this->assertEquals($crawler->convertDomainToAscii($url), $expected);
    }

    public function testConvertDomainToAsciiWithParameters()
    {
        $crawler = new Crawler(null, null, '');

        $url = 'olbertz.de/test?test=1&test2=2';
        $expected = 'http://olbertz.de/test?test=1&test2=2';
        $this->assertEquals($crawler->convertDomainToAscii($url), $expected);
    }
}
