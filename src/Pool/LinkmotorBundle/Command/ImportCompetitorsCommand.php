<?php
namespace Pool\LinkmotorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportCompetitorsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('seo:explorer:import-competitors')
            ->setDescription('Crawl explorer competitors (SEO-Tool)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $worker = $this->getContainer()->get('worker');
        if (!$worker->start('explorer.competitors')) {
            return;
        }

        $em = $this->getContainer()->get('doctrine')->getManager();
        $competitorRepository = $em->getRepository('PoolLinkmotorBundle:Competitor');
        $pages = $this->getContainer()->get('linkmotor.pages');

        while (1) {
            $competitorToImport = $competitorRepository->getNextCompetitorToImport();
            if (!$competitorToImport) {
                break;
            }

            $output->writeln($competitorToImport->getDomain()->getName() . '...');
            $numImported = $pages->importFromCompetitor($competitorToImport);
            $output->writeln("\t<info>{$numImported} imported</info>");

            $competitorToImport->setLastImportAt(new \DateTime());
            $em->persist($competitorToImport);
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
