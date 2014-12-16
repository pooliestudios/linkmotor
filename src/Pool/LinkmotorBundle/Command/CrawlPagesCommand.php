<?php
namespace Pool\LinkmotorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Pool\LinkmotorBundle\Service\Crawler;
use Pool\LinkmotorBundle\Service\SeoServices;
use Pool\LinkmotorBundle\Entity\Page;

class CrawlPagesCommand extends ContainerAwareCommand
{
    /**
     * @var crawler
     */
    private $crawler;

    /**
     * @var SeoServices
     */
    private $seoservices;

    private $em;

    protected function configure()
    {
        $this->setName('seo:crawl:pages')
            ->setDescription('Crawl pages (SEO-Tool)')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID of a specific page to crawl');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $pageRepository = $doctrine->getRepository('PoolLinkmotorBundle:Page');
        $this->em = $doctrine->getManager();
        $this->crawler = $this->getContainer()->get('crawler');
        $this->seoservices = $this->getContainer()->get('seoservices');

        $id = $input->getArgument('id');
        if ($id) {
            $pageToCrawl = $pageRepository->find($id);
            if (!$pageToCrawl) {
                $output->writeln('<error>Domain not found!</error>');

                return;
            }
            $this->crawl($output, $pageToCrawl);
        } else {
            $interval = $this->getContainer()->getParameter('crawler.page.interval');
            $worker = $this->getContainer()->get('worker');
            if (!$worker->start('crawl.pages')) {
                return;
            }

            while (1) {
                $pageToCrawl = $pageRepository->getNextPageToCrawl($interval);
                if (!$pageToCrawl) {
                    break;
                }

                $this->crawl($output, $pageToCrawl);

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
     * @param Page $page
     */
    protected function crawl(OutputInterface $output, Page $page)
    {
        $output->writeln('Getting page authority for <info>' . $page->getFull() . '</info>');
        $authority = $this->seoservices->getPageAuthority($page->getFull());
        if ($authority !== false) {
            $output->writeln('Result: ' . $authority);
            $page->setAuthority($authority);
        }
        $output->writeln('Getting page popvalues for <info>' . $page->getFullWithoutScheme() . '</info>');
        $popValues = $this->seoservices->getPagePopValues($page->getFullWithoutScheme());
        if (isset($popValues['pageLinkPop']) && $popValues['pageLinkPop'] !== false) {
            $output->writeln('linkPop: ' . $popValues['pageLinkPop']);
            $page->setLinkPop($popValues['pageLinkPop']);
        }
        if (isset($popValues['pageDomainPop']) && $popValues['pageDomainPop'] !== false) {
            $output->writeln('domainPop: ' . $popValues['pageDomainPop']);
            $page->setDomainPop($popValues['pageDomainPop']);
        }
        $output->writeln('Getting socialmedia values for <info>' . $page->getFull() . '</info>');
        $socialMediaValues = $this->seoservices->getSocialMediaInfoForUrl($page->getFull());
        if ($socialMediaValues) {
            $output->writeln(
                'twitter: ' . $socialMediaValues['data']['twitter']
                . ', facebook: ' . $socialMediaValues['data']['facebook']
                . ', gplus: ' . $socialMediaValues['data']['gplus']
            );
            $page->setTwitterCount($socialMediaValues['data']['twitter']);
            $page->setFacebookCount($socialMediaValues['data']['facebook']);
            $page->setGplusCount($socialMediaValues['data']['gplus']);
        }

        $page->setLastCrawledAt(new \DateTime());
        $this->em->persist($page);
        $this->em->flush();
    }
}
