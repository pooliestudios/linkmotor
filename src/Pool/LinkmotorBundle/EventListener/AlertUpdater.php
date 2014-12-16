<?php

namespace Pool\LinkmotorBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use \Doctrine\ORM\EntityManager;
use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Alert;
use Symfony\Bundle\TwigBundle\TwigEngine;

class AlertUpdater
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    private $translator;

    /**
     * @var \Swift_Mailer
     */
    private $mailer;

    private $noreplyAddress;

    private $container;

    /**
     * Übergabe des containers ist notwendig, weil es bei der Übergabe von @templating zu
     * einer CircularReferenceException kam.
     *
     * @param $container
     */
    public function __construct($container)
    {
        $this->container = $container;
        $this->translator = $container->get('translator');
        $this->mailer = $container->get('mailer');
        $this->noreplyAddress = $container->getParameter('linkmotor.noreplyAddress');
    }

    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->postUpdateOrPersist($args);
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $this->postUpdateOrPersist($args);
    }

    private function postUpdateOrPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $this->entityManager = $args->getEntityManager();

        if ($entity instanceof Backlink) {
            $this->checkPageStatus($entity);
            $this->updateAlerts($entity);
        }
    }

    public function checkPageStatus(Backlink $backlink)
    {
        if ($backlink->getIsOffline() && $backlink->getPage()->getStatus()->getId() != 7) {
            $status = $this->entityManager->getRepository('PoolLinkmotorBundle:Status')->find(7);
            $backlink->getPage()->setStatus($status);
            $backlink->getPage()->setLastModifiedAt(new \DateTime());
            $this->entityManager->persist($backlink);
            $this->entityManager->flush();
        } elseif (!$backlink->getIsOffline() && $backlink->getPage()->getStatus()->getId() == 7) {
            $status = $this->entityManager->getRepository('PoolLinkmotorBundle:Status')->find(6);
            $backlink->getPage()->setStatus($status);
            $backlink->getPage()->setLastModifiedAt(new \DateTime());
            $this->entityManager->persist($backlink);
            $this->entityManager->flush();
        }
    }

    /**
     * @param Backlink $backlink
     */
    public function updateAlerts(Backlink $backlink)
    {
        if ($backlink->getLastCrawledAt() === null) {
            return;
        }

        $status = 'ok';

        if (!$backlink->getXPathOk() || !$backlink->getStatusCodeOk()) {
            $status = 'warning';
        }
        if (!$backlink->getUrlOk() || !$backlink->getAnchorOk()
            || !$backlink->getTypeOk() || !$backlink->getFollowOk()
            || !$backlink->getMetaFollowOk() || !$backlink->getMetaIndexOk()
            || !$backlink->getXRobotsFollowOk() || !$backlink->getXRobotsIndexOk()
            || !$backlink->getRobotsGoogleOk()
        ) {
            $status = 'danger';
        }

        $alerts = $this->entityManager->getRepository('PoolLinkmotorBundle:Alert')
            ->findBy(array('backlink' => $backlink));

        if (($status == 'ok' && $alerts) || $backlink->getIsOffline()) {
            foreach ($alerts as $alert) {
                $this->entityManager->remove($alert);
            }
            $this->entityManager->flush();
        } elseif ($status != 'ok' && !$alerts) {
            // Neuen Alert anlegen
            $alert = new Alert();
            $alert->setBacklink($backlink);
            $alert->setProject($backlink->getProject());
            if ($status == 'warning') {
                $alert->setType('w');
            } else {
                $alert->setType('e');
            }
            $alert->setUser($backlink->getAssignedTo());
            $backlink->addAlert($alert);

            $this->entityManager->persist($backlink);
            $this->entityManager->persist($alert);
            $this->entityManager->flush();

            $this->sendNotifications($alert);
        } elseif ($status == 'danger' && $alerts) {
            // Prüfen, ob Alert von Warning zu Error hochgestuft werden muss
            $em = $this->entityManager;
            foreach ($alerts as $alert) {
                if ($alert->getType() == 'w') {
                    $alert->setType('e');
                    $alert->setHideUntil(null);
                    $em->persist($alert);
                    $em->flush();

                    $this->sendNotifications($alert);
                }
            }
        }
    }

    protected function sendNotifications(Alert $alert)
    {
        $project = $alert->getProject();
        if (!$project) {
            // Passiert zumindest im Test
            return;
        }

        $users = $this->entityManager
            ->getRepository('PoolLinkmotorBundle:NotificationSetting')
            ->getUsersToNotify($alert, 0); // 0 == 'at once'

        $templating = $this->container->get('templating');
        foreach ($users as $user) {
            $subject = 'Linkmotor: ';
            if ($alert->getType() == 'w') {
                $subject .= $this->translator->trans('New warning for');
            } else {
                $subject .= $this->translator->trans('New error for');
            }
            $subject .= ' ' . $alert->getProject()->getName();

            $locale = $user->getLocale();
            $this->translator->setLocale($locale);
            $message = \Swift_Message::newInstance()
                ->setSubject($subject)
                ->setFrom($this->noreplyAddress)
                ->setTo($user->getEmail());

            $message
                ->setBody(
                    $templating->render(
                        'PoolLinkmotorBundle:NotificationSetting:mailSingle.html.twig',
                        array('alert' => $alert, 'locale' => $locale)
                    ),
                    'text/html'
                )
                ->addPart(
                    $templating->render(
                        'PoolLinkmotorBundle:NotificationSetting:mailSingle.txt.twig',
                        array('alert' => $alert, 'locale' => $locale)
                    ),
                    'text/plain'
                );
            $this->mailer->send($message);
        }
    }
}
