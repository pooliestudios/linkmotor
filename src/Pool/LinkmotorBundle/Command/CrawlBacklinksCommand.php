<?php
namespace Pool\LinkmotorBundle\Command;

use Doctrine\ORM\EntityManager;
use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Service\Crawler;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CrawlBacklinksCommand extends ContainerAwareCommand
{
    /**
     * @var Crawler
     */
    protected $crawler;

    /**
     * @var EntityManager
     */
    protected $em;

    protected function configure()
    {
        $this->setName('seo:crawl:backlinks')
            ->setDescription('Crawl backlinks (SEO-Tool)')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID of a specific backlink to crawl');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->crawler = $this->getContainer()->get('crawler');
        $backlinkRepository = $this->em->getRepository('PoolLinkmotorBundle:Backlink');
        $id = $input->getArgument('id');
        if ($id) {
            $backlinkToCrawl = $backlinkRepository->find($id);
            if (!$backlinkToCrawl) {
                $output->writeln('<error>Backlink not found!</error>');

                return;
            }
            $this->crawl($output, $backlinkToCrawl);
        } else {
            $interval = $this->getContainer()->getParameter('crawler.backlink.interval');
            $worker = $this->getContainer()->get('worker');
            if (!$worker->start('crawl.backlinks')) {
                return;
            }

            while (1) {
                $backlinkToCrawl = $backlinkRepository->getNextBacklinkToCrawl($interval);
                if (!$backlinkToCrawl) {
                    break;
                }

                $this->crawl($output, $backlinkToCrawl);

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
     * @param Backlink $backlink
     */
    protected function crawl(OutputInterface $output, Backlink $backlink)
    {
        $pageUrl = $backlink->getPage()->getFull();
        $output->writeln(
            'Crawling <info>' . $pageUrl . '</info> for <info>' . $backlink->getUrl() . '</info>'
        );
        $this->crawler->crawlBacklink($backlink);
    }
}
