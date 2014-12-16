<?php
namespace Pool\LinkmotorBundle\Command;

use Doctrine\ORM\EntityManager;
use Pool\LinkmotorBundle\Entity\NotificationSetting;
use Pool\LinkmotorBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\TwigBundle\TwigEngine;

class SendDailyNotificationsCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var \Swift_Mailer
     */
    protected $mailer;

    /**
     * @var TwigEngine
     */
    protected $templating;

    protected $translator;

    protected function configure()
    {
        $this->setName('seo:notifications:daily')
            ->setDescription('Send daily notifications (SEO-Tool)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $this->mailer = $this->getContainer()->get('mailer');
        $this->templating = $this->getContainer()->get('templating');
        $this->translator = $this->getContainer()->get('translator');
        $noreplyAddress = $this->getContainer()->getParameter('linkmotor.noreplyAddress');

        $when = date('N');
        $users = $this->em->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        $projects = $this->em->getRepository('PoolLinkmotorBundle:Project')->findAll();
        foreach ($users as $user) {
            $output->writeln('<info>' . $user->getDisplayName() . '</info>');
            $notificationData = array();
            foreach ($projects as $project) {
                $notificationSetting = $this->em->getRepository('PoolLinkmotorBundle:NotificationSetting')
                    ->getSettingForUserAndProject($user, $project);
                $dataItem = array(
                    'projectId' => $project->getId(),
                    'projectName' => $project->getName(),
                    'numWarnings' => 0,
                    'numNewWarnings' => 0,
                    'numErrors' => 0,
                    'numNewErrors' => 0
                );
                if ($notificationSetting->getWhenMatches($notificationSetting->getWarningsWhen(), $when)) {
                    $dataItem['numWarnings'] = $this->getNumAlerts('w', $notificationSetting, $project, 'all');
                    $dataItem['numNewWarnings'] = $this->getNumAlerts('w', $notificationSetting, $project, 'new');
                }
                if ($notificationSetting->getWhenMatches($notificationSetting->getErrorsWhen(), $when)) {
                    $dataItem['numErrors'] = $this->getNumAlerts('e', $notificationSetting, $project, 'all');
                    $dataItem['numNewErrors'] = $this->getNumAlerts('e', $notificationSetting, $project, 'new');
                }
                if ($dataItem['numWarnings'] || $dataItem['numErrors']) {
                    $notificationData[] = $dataItem;
                }
            }
            if (!$notificationData) {
                $output->writeln("\tNo matching errors or warnings");
            } else {
                $output->writeln("\tSending mail...");
                $locale = $user->getLocale();
                $this->translator->setLocale($locale);
                foreach ($notificationData as $dataItem) {
                    $output->writeln("\t{$dataItem['projectName']}");
                    $output->writeln(
                        "\t\t{$dataItem['numErrors']} errors ({$dataItem['numNewErrors']} new),"
                        . "{$dataItem['numWarnings']} warnings ({$dataItem['numNewWarnings']} new)"
                    );
                }
                $subject = 'Linkmotor: ' . $this->translator->trans('Alert-Report');
                $message = \Swift_Message::newInstance()
                    ->setSubject($subject)
                    ->setFrom($noreplyAddress)
                    ->setTo($user->getEmail());

                $message
                    ->setBody(
                        $this->templating->render(
                            'PoolLinkmotorBundle:NotificationSetting:mailReport.html.twig',
                            array('notificationData' => $notificationData, 'user' => $user, 'locale' => $locale)
                        ),
                        'text/html'
                    )
                    ->addPart(
                        $this->templating->render(
                            'PoolLinkmotorBundle:NotificationSetting:mailReport.txt.twig',
                            array('notificationData' => $notificationData, 'user' => $user, 'locale' => $locale)
                        ),
                        'text/plain'
                    );
                $this->mailer->send($message);
            }
        }
    }

    protected function getNumAlerts($type, NotificationSetting $notificationSetting, Project $project, $which)
    {
        return $this->em->getRepository('PoolLinkmotorBundle:Alert')
            ->getCountForNotification($type, $notificationSetting, $project, $which);
    }
}
