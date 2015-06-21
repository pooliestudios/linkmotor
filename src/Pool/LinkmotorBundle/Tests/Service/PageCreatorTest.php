<?php
namespace Pool\LinkmotorBundle\Tests\Service;

use Pool\LinkmotorBundle\Service\Domains;
use Pool\LinkmotorBundle\Service\PageCreator;

class PageCreatorTest extends \PHPUnit_Framework_TestCase
{
    public function testGetUrlParts()
    {
        $pageCreatorService = new PageCreator(null, new Domains());

        $urlParts = $pageCreatorService->getUrlParts('https://beratung.de/test/test2?a=1&b=2');

        $this->assertEquals('https', $urlParts['scheme']);
        $this->assertEquals('beratung.de', $urlParts['domain']);
        $this->assertEquals('', $urlParts['subdomain']);
        $this->assertEquals('/test/test2?a=1&b=2', $urlParts['path']);
    }

    public function testGetUrlPartsWithSubdomain()
    {
        $pageCreatorService = new PageCreator(null, new Domains());

        $urlParts = $pageCreatorService->getUrlParts('https://www.beratung.de/test/test2?a=1&b=2');

        $this->assertEquals('https', $urlParts['scheme']);
        $this->assertEquals('beratung.de', $urlParts['domain']);
        $this->assertEquals('www', $urlParts['subdomain']);
        $this->assertEquals('/test/test2?a=1&b=2', $urlParts['path']);
    }

    public function testGetUrlPartsMultipartTLD()
    {
        $pageCreatorService = new PageCreator(null, new Domains());

        $urlParts = $pageCreatorService->getUrlParts('http://expedia.co.uk/test/test2?a=1&b=2');

        $this->assertEquals('http', $urlParts['scheme']);
        $this->assertEquals('expedia.co.uk', $urlParts['domain']);
        $this->assertEquals('', $urlParts['subdomain']);
        $this->assertEquals('/test/test2?a=1&b=2', $urlParts['path']);
    }

    public function testGetUrlPartsWithSubdomainAndMultipartTLD()
    {
        $pageCreatorService = new PageCreator(null, new Domains());

        $urlParts = $pageCreatorService->getUrlParts('http://www.expedia.co.uk/test/test2?a=1&b=2');

        $this->assertEquals('http', $urlParts['scheme']);
        $this->assertEquals('expedia.co.uk', $urlParts['domain']);
        $this->assertEquals('www', $urlParts['subdomain']);
        $this->assertEquals('/test/test2?a=1&b=2', $urlParts['path']);
    }
}
