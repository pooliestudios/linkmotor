<?php
namespace Pool\LinkmotorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SetOptionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('seo:options:set')
            ->setDescription('Sets an option (SEO-Tool)')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the option to set')
            ->addArgument('value', InputArgument::REQUIRED, 'New value of the option');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $options = $this->getContainer()->get('linkmotor.options');

        $name = $input->getArgument('name');
        $value = $input->getArgument('value');
        $options->set($name, $value);

        $output->writeln('Done.');
    }
}
