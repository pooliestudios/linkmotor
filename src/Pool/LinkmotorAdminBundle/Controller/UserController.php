<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorBundle\Entity\User;
use Pool\LinkmotorAdminBundle\Form\Type\UserAddType;
use Pool\LinkmotorAdminBundle\Form\Type\UserEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class UserController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/users/", defaults={"_locale" = "en"}, name="pool_linkmotor_admin_users_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $query = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllUsersQueryBuilder()->getQuery();

        $paginator  = $this->get('knp_paginator');
        $users = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('users' => $users);
    }

    /**
     * @Route("/{_locale}/admin/users/add/", defaults={"_locale" = "en"}, name="pool_linkmotor_admin_users_add")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        if ($this->get('linkmotor.limits')->usersLimitReached()) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $user = new User();
        $form = $this->createForm(new UserAddType(), $user);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                $encodedPassword = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($encodedPassword);

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'The user has been added'
                );

                return $this->redirect($this->generateUrl('pool_linkmotor_admin_users_index'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/users/edit/{id}/", defaults={"_locale" = "en"}, name="pool_linkmotor_admin_users_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, User $user)
    {
        if ($user->getId() == $this->getUser()->getId()) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'You cannot edit yourself'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_admin_users_index'));
        }

        if ($user->isSupportUser()) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'This user cannot be edited'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_admin_users_index'));
        }

        $form = $this->createForm(new UserEditType(), $user);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                if ($form['newPassword']->getData()) {
                    $encoder = $this->get('security.encoder_factory')->getEncoder($user);
                    $user->setNewSalt();
                    $encodedPassword = $encoder->encodePassword($form['newPassword']->getData(), $user->getSalt());
                    $user->setPassword($encodedPassword);
                }

                $em = $this->getDoctrine()->getManager();
                $em->persist($user);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                return $this->redirect($this->generateUrl('pool_linkmotor_admin_users_index'));
            }
        }

        return array('user' => $user, 'form' => $form->createView());
    }
}
