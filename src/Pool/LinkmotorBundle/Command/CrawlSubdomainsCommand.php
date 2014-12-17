<?php
namespace Pool\LinkmotorBundle\Command;

use Pool\LinkmotorBundle\Entity\Subdomain;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pool\LinkmotorBundle\Service\SeoServices;
use Symfony\Component\OptionsResolver\Options;

class CrawlSubdomainsCommand extends ContainerAwareCommand
{
    /**
     * @var SeoServices
     */
    private $seoservices;

    private $em;

    /**
     * @var Options
     */
    private $options;

    protected function configure()
    {
        $this->setName('seo:crawl:subdomains')
            ->setDescription('Crawl subdomains (SEO-Tool)')
            ->addArgument('id', InputArgument::OPTIONAL, 'ID of a specific subdomain to crawl');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $doctrine = $this->getContainer()->get('doctrine');
        $subdomainRepository = $doctrine->getRepository('PoolLinkmotorBundle:Subdomain');
        $this->em = $doctrine->getManager();
        $this->seoservices = $this->getContainer()->get('seoservices');
        $this->options = $this->getContainer()->get('linkmotor.options');

        /**
         * Im Moment werden nur OVI und Sichtbarkeitsindex gecrawlt. Bevor die Datenbank durchsucht wird, wird
         * erst einmal abgefragt, ob die beiden aktiviert sind.
         */
        $crawlNeccessary = false;
        if (($this->options->get('sistrix_active') && $this->options->get('sistrix_api_key'))
            || ($this->options->get('xovi_active') && $this->options->get('xovi_api_key'))
        ) {
            $crawlNeccessary = true;
        }
        if (!$crawlNeccessary) {
            $output->writeln('Neither SISTRIX nor XOVI are active.');
            return;
        }

        $id = $input->getArgument('id');
        if ($id) {
            $subdomainToCrawl = $subdomainRepository->find($id);
            if (!$subdomainToCrawl) {
                $output->writeln('<error>Subdomain not found!</error>');

                return;
            }
            $this->crawl($output, $subdomainToCrawl);
        } else {
            $interval = $this->getContainer()->getParameter('crawler.subdomain.interval');
            $worker = $this->getContainer()->get('worker');
            if (!$worker->start('crawl.subdomains')) {
                return;
            }

            while (1) {
                $subdomainToCrawl = $subdomainRepository->getNextSubdomainToCrawl($interval);
                if (!$subdomainToCrawl) {
                    break;
                }

                $this->crawl($output, $subdomainToCrawl);

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
     * @param Subdomain $subdomain
     */
    protected function crawl(OutputInterface $output, Subdomain $subdomain)
    {
        $crawled = false;
        if ($this->options->get('sistrix_active') && $this->options->get('sistrix_api_key')) {
            $output->writeln('Getting Sichtbarkeitsindex for <info>' . $subdomain->getFull() . '</info>');
            $value = $this->seoservices->getSistrixSichtbarkeitsIndex($subdomain->getFull());
            if ($value !== false) {
                $output->writeln('Result: ' . $value['value']);
                $subdomain->setSichtbarkeitsindex($value['value']);
                $crawled = true;
            }
        }
        if ($this->options->get('xovi_active') && $this->options->get('xovi_api_key')) {
            $output->writeln('Getting OVI for <info>' . $subdomain->getFull() . '</info>');
            $value = $this->seoservices->getOvi($subdomain->getFull());
            if ($value !== false) {
                $output->writeln('Result: ' . $value['value']);
                $subdomain->setOvi($value['value']);
                $crawled = true;
            }
        }
        if ($crawled) {
            $subdomain->setLastCrawledAt(new \DateTime());
            $this->em->persist($subdomain);
            $this->em->flush();
        }
    }
}
