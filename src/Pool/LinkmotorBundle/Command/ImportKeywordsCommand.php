<?php
namespace Pool\LinkmotorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ImportKeywordsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('seo:explorer:import-keywords')
            ->setDescription('Crawl explorer keywords (SEO-Tool)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = $this->getContainer()->get('worker');
        if (!$worker->start('explorer.keywords')) {
            return;
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        $keywordRepository = $em->getRepository('PoolLinkmotorBundle:Keyword');
        $pages = $this->getContainer()->get('linkmotor.pages');

        while (1) {
            $keywordToImport = $keywordRepository->getNextKeywordToImport();
            if (!$keywordToImport) {
                break;
            }

            $output->writeln($keywordToImport->getKeyword() . '...');
            $numImported = $pages->importFromKeyword($keywordToImport);
            $output->writeln("\t<info>{$numImported} imported</info>");

            $keywordToImport->setLastImportAt(new \DateTime());
            $em->persist($keywordToImport);
            $em->flush();

            if (!$worker->update()) {
                break;
            }

            if ($worker->getUpdates() > 1000) {
                break;
            }
        }
    }
}
