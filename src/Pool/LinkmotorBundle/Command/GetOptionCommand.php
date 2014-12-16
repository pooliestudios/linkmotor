<?php
namespace Pool\LinkmotorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class GetOptionCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('seo:options:get')
            ->setDescription('Gets an option (SEO-Tool)')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of the option to get');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $value = $this->getContainer()->get('linkmotor.options')->get($name);
        $output->writeln("{$name} = '{$value}'");
    }
}
