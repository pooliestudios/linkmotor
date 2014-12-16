<?php
namespace Pool\LinkmotorBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddAdminUserCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('seo:admin:create')
            ->setDescription('Add Admin User (SEO-Tool)')
            ->addArgument('email', InputArgument::REQUIRED, 'E-Mail address of new user')
            ->addArgument('name', InputArgument::REQUIRED, 'Name of new user')
            ->addArgument('password', InputArgument::REQUIRED, 'Password for new user');
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $dialog = $this->getHelper('dialog');

        if (!$input->getArgument('email')) {
            $input->setArgument('email', $dialog->ask($output, 'E-Mail address: '));
        }
        if (!$input->getArgument('name')) {
            $input->setArgument('name', $dialog->ask($output, 'Name: '));
        }
        if (!$input->getArgument('password')) {
            $input->setArgument('password', $dialog->ask($output, 'Password: '));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $email = $input->getArgument('email');
        $name = $input->getArgument('name');
        $password = $input->getArgument('password');

        $factory = $this->getContainer()->get('security.encoder_factory');
        $user = new \Pool\LinkmotorBundle\Entity\User();
        $user->setEmail($email);
        $user->setName($name);
        $user->setIsAdmin(true);
        $user->setIsInactive(false);

        $encoder = $factory->getEncoder($user);
        $encodedPassword = $encoder->encodePassword($password, $user->getSalt());
        $user->setPassword($encodedPassword);

        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $em->persist($user);
        $em->flush($user);

        $output->writeln('Done.');
    }
}
