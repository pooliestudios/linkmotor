<?php
namespace Pool\LinkmotorBundle\Tests\Service;

use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Pool\LinkmotorBundle\Service\RobotsTxt;

class RobotsTxtTest extends \PHPUnit_Framework_TestCase
{
    public function testIsGoogleBotAllowedForPageFalseAllBots()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('blogger.de');

        $subdomain = new Subdomain();
        $subdomain->setName('rebellmarkt');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: *
Disallow: /members/
Disallow: /referrers
Disallow: /mostread
Disallow: /20061231/');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/20061231/');

        $this->assertFalse($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForPageFalse()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('blogger.de');

        $subdomain = new Subdomain();
        $subdomain->setName('rebellmarkt');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: Googlebot
Disallow: /members/
Disallow: /referrers
Disallow: /mostread
Disallow: /20061231/');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/20061231/');

        $this->assertFalse($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForPageTrueAllBots()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('blogger.de');

        $subdomain = new Subdomain();
        $subdomain->setName('rebellmarkt');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: *
Disallow: /members/
Disallow: /referrers
Disallow: /mostread
Disallow: /20061231/');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/20061232/');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForPageTrue()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('blogger.de');

        $subdomain = new Subdomain();
        $subdomain->setName('rebellmarkt');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: Googlebot
Disallow: /members/
Disallow: /referrers
Disallow: /mostread
Disallow: /20061231/');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/20061232/');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIssue72()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('gutscheinaffe.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: *
Disallow: /gutschein/

Sitemap: http://www.gutscheinaffe.de/sitemap.xml

User-agent: msnbot
Crawl-delay: 3');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/design-bestseller');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForPageEmptyRobotsTxt()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('test.irregular'); // Just make sure no real robots.txt is fetched

        $subdomain = new Subdomain();
        $subdomain->setName('');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForPageLawblog()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('lawblog.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: *
Crawl-Delay: 20
Allow: /

User-agent: BacklinkCrawler
Disallow: /');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/index.php/archives/2013/09/04/jacke-wie-hose-3/');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForAusbildung()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('ausbildung.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('# See http://www.robotstxt.org/wc/norobots.html for documentation on how to use the robots.txt file
#
# To ban all spiders from the entire site uncomment the next two lines:
# User-Agent: *
# Disallow: /
            Sitemap: http://www.ausbildung.de/system/sitemap_index.xml.gz');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/unternehmen/pooliestudios/');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedForSchmunzelbiene()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('schmunzelbiene.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: ia_archiver
Disallow: /

User-agent: Googlebot-Image
Disallow: /

User-agent: psbot
Disallow: /


User-agent: *
Disallow: /wp-admin
Disallow: /wp-includes
Disallow: /wp-content/plugins
Disallow: /wp-content/cache
Disallow: /wp-content/themes
Disallow: /wp-content/upgrade
Disallow: /wp-content/languages
Disallow: /cgi-bin/
Disallow: /usage/
Disallow: /logs/
Disallow: /db_backup/
Disallow: *?replytocom');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/2014/03/lotto-online-spielen-die-chance-auf-den-grossen-gewinn/');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedWithCrawlDelay()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('offenburg.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: Googlebot
Crawl-delay: 5
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: msnbot
Crawl-delay: 5
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: Slurp
Crawl-delay: 5
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: EdithSolrCrawler
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: *
Disallow: /
');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);
        $page->setUrl('/html/partner/links532.html?kategorie=65');

        $this->assertTrue($robotsTxt->isGoogleBotAllowedForPage($page));
    }

    public function testIsGoogleBotAllowedMultipleGoogleSections()
    {
        $robotsTxt = new RobotsTxt(null);

        $domain = new Domain();
        $domain->setName('offenburg.de');

        $subdomain = new Subdomain();
        $subdomain->setName('www');
        $subdomain->setDomain($domain);
        $subdomain->setRobotsTxt('User-agent: Googlebot
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: msnbot
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: Googlebot
Disallow: /test/

User-agent: EdithSolrCrawler
Disallow: /admintools/
Disallow: /application/
Disallow: /awstats/
Disallow: /logs/
Disallow: /reports/
Disallow: /html/tiles/
Disallow: /html/layout/
Disallow: /html/templates/

User-agent: *
Disallow: /
');
        $subdomain->setRobotsTxtLastFetched(new \DateTime());

        $page = new Page();
        $page->setSubdomain($subdomain);

        $page->setUrl('/logs/links532.html?kategorie=65');
        $this->assertFalse($robotsTxt->isGoogleBotAllowedForPage($page));

        $page->setUrl('/test/links532.html?kategorie=65');
        $this->assertFalse($robotsTxt->isGoogleBotAllowedForPage($page));
    }
}
