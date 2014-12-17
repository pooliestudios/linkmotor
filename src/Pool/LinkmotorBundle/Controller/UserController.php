<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\ForgotPasswordToken;
use Pool\LinkmotorBundle\Entity\NotificationSetting;
use Pool\LinkmotorBundle\Form\Type\ChangePasswordType;
use Pool\LinkmotorBundle\Form\Type\ForgotPasswordType;
use Pool\LinkmotorBundle\Form\Type\ResetPasswordType;
use Pool\LinkmotorBundle\Form\Type\UserNotificationSettingsType;
use Pool\LinkmotorBundle\Form\Type\UserSettingsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    /**
     * @Template()
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(
                SecurityContext::AUTHENTICATION_ERROR
            );
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        $language = $request->getPreferredLanguage();
        $locale = strtolower(\Locale::getPrimaryLanguage($language));

        if (!in_array($locale, $this->supportedLanguages)) {
            $locale = 'en';
        }
        $this->get('translator')->setLocale($locale);

        return array(
            // last username entered by the user
            'last_username' => $session->get(SecurityContext::LAST_USERNAME),
            'error'         => $error,
            'locale'        => $locale
        );
    }

    /**
     * @Route("/{_locale}/forgot-password/", defaults={"_locale" = "en"}, name="pool_linkmotor_forgot_password")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function forgotPasswordAction(Request $request)
    {
        $data = array();
        if ($request->get('username')) {
            $data['email'] = $request->get('username');
        }

        $form = $this->createForm(new ForgotPasswordType(), $data);

        if ($request->isMethod('POST')) {
            $form->submit($request);
            $email = $form['email']->getData();
            if (!$email) {
                $message = $this->get('translator')->trans('You need to enter an e-mail address!');
                $form->addError(new FormError($message));
            } else {
                $em = $this->getDoctrine()->getManager();
                $user = $em->getRepository('PoolLinkmotorBundle:User')
                    ->findByEmail($email);
                if (count($user) != 1) {
                    $message = $this->get('translator')->trans('The message could not be sent!');
                    $form->addError(new FormError($message));
                } else {
                    $salt = $this->container->getParameter('secret');

                    $user = $user[0];
                    // go through all old tokens and remove those which are no longer valid
                    foreach ($user->getForgotPasswordTokens() as $oldToken) {
                        if ($oldToken->getValidUntil()->format('Y-m-d H:i:s') < date('Y-m-d H:i:s')) {
                            $em->remove($oldToken);
                        }
                    }

                    // Now Create a new token
                    $forgotPasswordToken = new ForgotPasswordToken();
                    $validationId = $forgotPasswordToken->generateValidationId();
                    $hash = $forgotPasswordToken->generateHash($validationId, $salt);
                    $forgotPasswordToken->setUser($user);
                    $forgotPasswordToken->setValidUntil(new \DateTime('+1 hour'));
                    $forgotPasswordToken->setHash($hash);
                    $em->persist($forgotPasswordToken);
                    $em->flush();

                    $noreplyAddress = $this->container->getParameter('linkmotor.noreplyAddress');

                    $message = \Swift_Message::newInstance()
                        ->setSubject('Linkmotor: ' . $this->get('translator')->trans('Password reset'))
                        ->setFrom($noreplyAddress)
                        ->setTo($email);

                    $message
                        ->setBody(
                            $this->renderView(
                                'PoolLinkmotorBundle:User:forgotPasswordEmail.html.twig',
                                array('validationId' => $validationId, 'user' => $user)
                            ),
                            'text/html'
                        )
                        ->addPart(
                            $this->renderView(
                                'PoolLinkmotorBundle:User:forgotPasswordEmail.txt.twig',
                                array('validationId' => $validationId, 'user' => $user)
                            ),
                            'text/plain'
                        );
                    $this->get('mailer')->send($message);

                    return $this->redirect(
                        $this->generateUrl('pool_linkmotor_forgot_password_sent', array('email' => $email))
                    );
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/forgot-password-sent/", defaults={"_locale" = "en"}, name="pool_linkmotor_forgot_password_sent")
     * @Method("GET")
     * @Template()
     */
    public function forgotPasswordSentAction(Request $request)
    {
        $email = $request->get('email');

        return array('email' => $email);
    }

    /**
     * @Route("/{_locale}/reset-password/{userId}/{validationId}/",
     *        defaults={"_locale" = "en"}, name="pool_linkmotor_reset_password")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function resetPasswordAction(Request $request, $userId, $validationId)
    {
        $validToken = null;

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('PoolLinkmotorBundle:User')->find($userId);
        if ($user) {
            $salt = $this->container->getParameter('secret');
            foreach ($user->getForgotPasswordTokens() as $token) {
                if ($token->verifyValidationId($validationId, $salt)) {
                    $validToken = $token;
                    break;
                }
            }
        }

        $form = $this->createForm(new ResetPasswordType());
        if ($validToken && $request->isMethod('POST')) {
            $form->submit($request);
            if ($form->isValid()) {
                $isValid = true;
                $newPassword = $form['new']->getData();
                $newPassword2 = $form['new2']->getData();
                if ($newPassword != $newPassword2) {
                    $message = $this->get('translator')->trans('Please enter the same new password twice');
                    $form->addError(new FormError($message));
                    $isValid = false;
                }
                if (strlen($newPassword) < 6 && strlen($newPassword2) < 6) {
                    $message = $this->get('translator')->trans('Your new password needs to be at least 6 characters long');
                    $form->addError(new FormError($message));
                    $isValid = false;
                }

                if ($isValid) {
                    $factory = $this->get('security.encoder_factory');
                    $encoder = $factory->getEncoder($user);
                    $user->setNewSalt();
                    $encodedNewPassword = $encoder->encodePassword($newPassword, $user->getSalt());
                    $user->setPassword($encodedNewPassword);
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    $this->get('session')->getFlashBag()->add('success', 'Your new password has been set');

                    // remove all tokens from this user
                    foreach ($user->getForgotPasswordTokens() as $oldToken) {
                        $em->remove($oldToken);
                    }
                    $em->flush();

                    return $this->redirect($this->generateUrl('login'));
                }
            }
        }

        return array('validationId' => $validationId, 'validToken' => $validToken, 'form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/settings/", defaults={"_locale" = "en"}, name="pool_linkmotor_user_settings")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function settingsAction(Request $request)
    {
        $user = $this->getUser();
        $form =  $this->createForm(new UserSettingsType(), $user);
        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $request->setLocale($user->getLocale());

                $this->get('session')->getFlashBag()->add('success', 'Your changes have been saved');

                return $this->redirect($this->generateUrl('pool_linkmotor_user_settings', array('_locale' => $user->getLocale())));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/notification-settings/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_user_notification_settings")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function notificationSettingsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $projects = $em->getRepository('PoolLinkmotorBundle:Project')->findAll();
        $user = $this->getUser();

        $defaultNotificationSettings = $em->getRepository('PoolLinkmotorBundle:NotificationSetting')
            ->getDefaultForUser($user);
        if (!$defaultNotificationSettings) {
            $defaultNotificationSettings = new NotificationSetting();
            $defaultNotificationSettings->setUser($user);
        }
        $notificationSettings = $defaultNotificationSettings->toArray();

        foreach ($projects as $project) {
            $notificationSetting = $em->getRepository('PoolLinkmotorBundle:NotificationSetting')
                ->getForUserAndProject($user, $project);
            if ($notificationSetting) {
                $notificationSettings["customSettings-{$project->getId()}"] = true;
                $notificationSettings = array_merge(
                    $notificationSettings,
                    $notificationSetting->toArray($project->getId())
                );
            }
        }

        $userNotificationSettingsType = new UserNotificationSettingsType();
        $userNotificationSettingsType
            ->setProjects($projects)
            ->setIsAdmin($user->getIsAdmin());
        $form =  $this->createForm($userNotificationSettingsType, $notificationSettings);
        if ($request->isMethod('POST')) {
            $form->submit($request);

            if ($form->isValid()) {
                $defaultNotificationSettings->setWarnings($form['warningNotificationOn']->getData() == 1);
                if ($defaultNotificationSettings->getWarnings()) {
                    if ($user->isAdmin()) {
                        $defaultNotificationSettings->setAllWarnings($form['warningFor']->getData() == 1);
                    }
                    $defaultNotificationSettings->setWarningsWhen($form['warningWhen']->getData());
                }
                $defaultNotificationSettings->setErrors($form['errorNotificationOn']->getData() == 1);
                if ($defaultNotificationSettings->getErrors()) {
                    if ($user->isAdmin()) {
                        $defaultNotificationSettings->setAllErrors($form['errorFor']->getData() == 1);
                    }
                    $defaultNotificationSettings->setErrorsWhen($form['errorWhen']->getData());
                }

                $em->persist($defaultNotificationSettings);

                foreach ($projects as $project) {
                    $postfix = "-{$project->getId()}";
                    $notificationSetting = $em->getRepository('PoolLinkmotorBundle:NotificationSetting')
                        ->getForUserAndProject($user, $project);
                    if ($notificationSetting && !$form["customSettings{$postfix}"]->getData()) {
                        $em->remove($notificationSetting);
                        continue;
                    }
                    if (!$notificationSetting && !$form["customSettings{$postfix}"]->getData()) {
                        continue;
                    }
                    if (!$notificationSetting) {
                        $notificationSetting = new NotificationSetting();
                        $notificationSetting->setUser($user);
                        $notificationSetting->setProject($project);
                    }
                    $notificationSetting->setWarnings($form["warningNotificationOn{$postfix}"]->getData() == 1);
                    if ($notificationSetting->getWarnings()) {
                        if ($user->isAdmin()) {
                            $notificationSetting->setAllWarnings($form["warningFor{$postfix}"]->getData() == 1);
                        }
                        $notificationSetting->setWarningsWhen($form["warningWhen{$postfix}"]->getData());
                    }
                    $notificationSetting->setErrors($form["errorNotificationOn{$postfix}"]->getData() == 1);
                    if ($notificationSetting->getErrors()) {
                        if ($user->isAdmin()) {
                            $notificationSetting->setAllErrors($form["errorFor{$postfix}"]->getData() == 1);
                        }
                        $notificationSetting->setErrorsWhen($form["errorWhen{$postfix}"]->getData());
                    }

                    $em->persist($notificationSetting);
                }
                $em->flush();

                $this->get('session')->getFlashBag()->add('success', 'Your changes have been saved');
            }
        }

        return array('form' => $form->createView(), 'projects' => $projects);
    }

    /**
     * @Route("/{_locale}/app/settings/change-password/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_user_change_password")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function changePasswordAction(Request $request)
    {
        $form =  $this->createForm(new ChangePasswordType());
        if ($request->isMethod('POST')) {
            $form->submit($request);

            $currentPassword = $form['current']->getData();
            $user = $this->getUser();
            $factory = $this->get('security.encoder_factory');
            $encoder = $factory->getEncoder($user);
            $encodedCurrentPassword = $encoder->encodePassword($currentPassword, $user->getSalt());
            $isValid = true;
            $verifyUser = $this->getDoctrine()
                ->getRepository('PoolLinkmotorBundle:User')
                ->find($user->getId());
            if ($verifyUser->getPassword() != $encodedCurrentPassword) {
                $message = $this->get('translator')->trans('Please enter the correct current password');
                $form->addError(new FormError($message));
                $isValid = false;
            }

            $newPassword = $form['new']->getData();
            $newPassword2 = $form['new2']->getData();
            if ($newPassword != $newPassword2) {
                $message = $this->get('translator')->trans('Please enter the same new password twice');
                $form->addError(new FormError($message));
                $isValid = false;
            }
            if (strlen($newPassword) < 6 && strlen($newPassword2) < 6) {
                $message = $this->get('translator')->trans('Your new password needs to be at least 6 characters long');
                $form->addError(new FormError($message));
                $isValid = false;
            }

            if ($isValid) {
                $user->setNewSalt();
                $encodedNewPassword = $encoder->encodePassword($newPassword, $user->getSalt());
                $user->setPassword($encodedNewPassword);
                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();
                $this->get('session')->getFlashBag()->add('success', 'Your new password has been set');

                return $this->redirect($this->generateUrl('pool_linkmotor_user_settings'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/ajax/table-options/{which}/{action}/", name="pool_linkmotor_ajax_users_table_options")
     * @Method("POST")
     * @Template()
     */
    public function tableOptionsAction(Request $request, $which, $action)
    {
        $user = $this->getUser();
        $value = $request->request->get('value');

        if ($which == 'pages') {
            $tableOptions = $user->getTableOptionsPages();
        } elseif ($which == 'backlinks') {
            $tableOptions = $user->getTableOptionsBacklinks();
        }

        if ($action == 'show' || $action == 'hide') {
            foreach ($tableOptions as $idx => $tableOption) {
                if ($tableOption['id'] == $value) {
                    $tableOptions[$idx]['class'] = $action == 'show' ? '' : 'hidden';
                }
            }
        } elseif ($action == 'sort-order') {
            $newTableOptions = $tableOptions;
            foreach ($value as $id => $idx) {
                $id = str_replace('row-', '', $id);
                foreach ($tableOptions as $tableOption) {
                    if ($tableOption['id'] == $id) {
                        $newTableOptions[$idx] = $tableOption;
                    }
                }
            }
            $tableOptions = $newTableOptions;
        }

        if ($which == 'pages') {
            $user->setTableOptionsPages($tableOptions);
        } elseif ($which == 'backlinks') {
            $user->setTableOptionsBacklinks($tableOptions);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        return new Response('OK');
    }
}
