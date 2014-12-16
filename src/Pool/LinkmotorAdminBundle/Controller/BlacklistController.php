<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorBundle\Entity\Blacklist;
use Pool\LinkmotorAdminBundle\Form\Type\BlacklistAddType;
use Pool\LinkmotorAdminBundle\Form\Type\BlacklistEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class BlacklistController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/explorer/blacklist/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_blacklist_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($this->hasFilterChanged($request)) {
            // Notwendig, da der Parameter sonst auch beim Paginieren angehängt wird
            return $this->redirect($this->generateUrl('pool_linkmotor_blacklist_index'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Blacklist')
            ->getQueryForBlacklistIndex($project);
        $paginator  = $this->get('knp_paginator');
        $blacklist = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('blacklist' => $blacklist);
    }

    /**
     * @Route("/{_locale}/admin/explorer/blacklist/add/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_blacklist_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        $blacklistItem = new Blacklist();
        $form = $this->createForm(new BlacklistAddType(), $blacklistItem);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $domainName = strtolower($form['domain']->getData());
            $domainIsValid = $domainName && !preg_match('/[\/:]/', $domainName);
            if (!$domainIsValid) {
                $errorMessage = $this->get('translator')
                    ->trans('The domain may not contain / or :', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            if ($domainName == $project->getDomainName()) {
                $domainIsValid = false;
                $errorMessage = $this->get('translator')
                    ->trans("You cannot add the project's domain to the blacklist", array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            $domainsService = $this->get('linkmotor.domains');
            if ($domainsService->isSubdomain($domainName)) {
                $domainIsValid = false;
                $errorMessage = $this->get('translator')
                    ->trans('You need to specify a domain, not a subdomain', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            if ($form->isValid() && $domainIsValid) {
                $domain = $domainsService->addDomain($domainName);

                $blacklistItemFound = $this->getDoctrine()
                    ->getRepository('PoolLinkmotorBundle:Blacklist')
                    ->findBy(array('domain' => $domain->getId(), 'project' => $project->getId()));
                if ($blacklistItemFound) {
                    $errorMessage = $this->get('translator')
                        ->trans('This domain has already been added to the blacklist', array(), 'validators');
                    $form->addError(new FormError($errorMessage));
                } else {
                    $blacklistItem->setDomain($domain);
                    $blacklistItem->setProject($project);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($blacklistItem);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'success',
                        'The domain has been added to the blacklist'
                    );

                    $domainsService->deleteAllNewPagesFor($domain, $project);

                    return $this->redirect($this->generateUrl('pool_linkmotor_explorer_blacklist_index'));
                }
            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/explorer/blacklist/{id}/edit/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_blacklist_edit")
     * @Template()
     */
    public function editAction(Request $request, Blacklist $blacklistItem)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $blacklistItem->getProject()) {
            $project = $blacklistItem->getProject();
            $this->setSelectedProject($project);
        }

        $form = $this->createForm(new BlacklistEditType(), $blacklistItem);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($blacklistItem);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                return $this->redirect($this->generateUrl('pool_linkmotor_explorer_blacklist_index'));
            }
        }
        return array('blacklistItem' => $blacklistItem, 'form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/explorer/blacklist/{id}/delete/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_blacklist_delete")
     * @Template()
     */
    public function deleteAction(Request $request, Blacklist $blacklist)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            $em->remove($blacklist);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The blacklist item has been deleted'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_explorer_blacklist_index'));
        }

        return array('blacklist' => $blacklist);
    }
}
