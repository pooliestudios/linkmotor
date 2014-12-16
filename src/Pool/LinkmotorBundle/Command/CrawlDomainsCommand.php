<?php
namespace Pool\LinkmotorBundle\Command;

use Pool\LinkmotorBundle\Entity\Domain;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pool\LinkmotorBundle\Service\Crawler;
use Pool\LinkmotorBundle\Service\SeoServices;

class CrawlDomainsCommand extends ContainerAwareCommand
{
    /**
     * @var Crawler
     */
    private $crawler;

    /**
     * @var SeoServices
     */
    private $seoservices;

    private $em;

    protected function configure()
    {
        $this->setName('seo:crawl:domains')
            ->setDescription('Crawl domains (SEO-Tool)')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID of a specific domain to crawl');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $domainRepository = $doctrine->getRepository('PoolLinkmotorBundle:Domain');
        $this->em = $doctrine->getManager();
        $this->crawler = $this->getContainer()->get('crawler');
        $this->seoservices = $this->getContainer()->get('seoservices');

        $id = $input->getArgument('id');
        if ($id) {
            $domainToCrawl = $domainRepository->find($id);
            if (!$domainToCrawl) {
                $output->writeln('<error>Domain not found!</error>');

                return;
            }
            $this->crawl($output, $domainToCrawl);
        } else {
            $interval = $this->getContainer()->getParameter('crawler.domain.interval');
            $worker = $this->getContainer()->get('worker');
            if (!$worker->start('crawl.domains')) {
                return;
            }

            while (1) {
                $domainToCrawl = $domainRepository->getNextDomainToCrawl($interval);
                if (!$domainToCrawl) {
                    break;
                }

                $this->crawl($output, $domainToCrawl);

                if (!$worker->update()) {
                    break;
                }

                if ($worker->getUpdates() > 1000) {
                    break;
                }
            }
        }
    }

    /**
     * @param OutputInterface $output
     * @param Domain $domain
     */
    protected function crawl(OutputInterface $output, Domain $domain)
    {
        $output->writeln('Getting domain authority for <info>' . $domain->getName() . '</info>');
        $authority = $this->seoservices->getDomainAuthority($domain->getName());
        if ($authority !== false) {
            $output->writeln('Result: ' . $authority);
            $domain->setAuthority($authority);
        }
        $output->writeln('Getting domain popvalues for <info>' . $domain->getName() . '</info>');
        $popValues = $this->seoservices->getDomainPopValues($domain->getName());
        if (isset($popValues['domainLinkPop']) && $popValues['domainLinkPop'] !== false) {
            $output->writeln('linkPop: ' . $popValues['domainLinkPop']);
            $domain->setLinkPop($popValues['domainLinkPop']);
        }
        if (isset($popValues['domainDomainPop']) && $popValues['domainDomainPop'] !== false) {
            $output->writeln('domainPop: ' . $popValues['domainDomainPop']);
            $domain->setDomainPop($popValues['domainDomainPop']);
        }
        if (isset($popValues['domainNetPop']) && $popValues['domainNetPop'] !== false) {
            $output->writeln('netPop: ' . $popValues['domainNetPop']);
            $domain->setNetPop($popValues['domainNetPop']);
        }
        if (!$domain->getFirstDay()) {
            $output->writeln('Getting domain age for <info>' . $domain->getName() . '</info>');
            $firstDay = $this->seoservices->getArchiveOrgFirstDay($domain);
            if ($firstDay !== null) {
                $output->writeln('Result: ' . $firstDay->format('Y-m-d'));
                $domain->setFirstDay($firstDay);
            }
        }

        $domain->setLastCrawledAt(new \DateTime());
        $this->em->persist($domain);
        $this->em->flush();
    }
}
