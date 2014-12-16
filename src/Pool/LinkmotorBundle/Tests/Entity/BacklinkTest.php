<?php
namespace Pool\LinkmotorBundle\Tests\Entity;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\Subdomain;

class BacklinkTest extends \PHPUnit_Framework_TestCase
{
    public function testCheckProjectDomainOrSubdomainDifferentDomainSameSubdomain()
    {
        $domain = new Domain();
        $domain->setName('test.de');

        $subdomain = new Subdomain();
        $subdomain->setDomain($domain);
        $subdomain->setName('www');

        $project = new Project();
        $project->setSubdomain($subdomain);

        $backlink = new Backlink();
        $backlink->setProject($project);
        $backlink->setUrl('http://www.beispiel.de/index.html');
        $domainMatchingError = $backlink->checkProjectDomainOrSubdomain();

        $this->assertEquals($domainMatchingError, "The url's domain does not match the project's domain");
    }

    public function testCheckProjectDomainOrSubdomainDifferentSubdomainSameDomain()
    {
        $domain = new Domain();
        $domain->setName('beispiel.de');

        $subdomain = new Subdomain();
        $subdomain->setDomain($domain);
        $subdomain->setName('intern');

        $project = new Project();
        $project->setSubdomain($subdomain);

        $backlink = new Backlink();
        $backlink->setProject($project);
        $backlink->setUrl('http://www.beispiel.de/index.html');
        $domainMatchingError = $backlink->checkProjectDomainOrSubdomain();

        $this->assertEquals($domainMatchingError, "The url's subdomain does not match the project's subdomain");
    }

    public function testCheckProjectDomainOrSubdomainSameSubdomainSameDomain()
    {
        $domain = new Domain();
        $domain->setName('beispiel.de');

        $subdomain = new Subdomain();
        $subdomain->setDomain($domain);
        $subdomain->setName('www');

        $project = new Project();
        $project->setSubdomain($subdomain);

        $backlink = new Backlink();
        $backlink->setProject($project);
        $backlink->setUrl('http://www.beispiel.de/index.html');
        $domainMatchingError = $backlink->checkProjectDomainOrSubdomain();

        $this->assertEquals($domainMatchingError, '');
    }

    public function testCheckProjectDomainOrSubdomainSameDomain()
    {
        $domain = new Domain();
        $domain->setName('beispiel.de');

        $project = new Project();
        $project->setDomain($domain);

        $backlink = new Backlink();
        $backlink->setProject($project);
        $backlink->setUrl('http://www.beispiel.de/index.html');
        $domainMatchingError = $backlink->checkProjectDomainOrSubdomain();

        $this->assertEquals($domainMatchingError, '');
    }
}
