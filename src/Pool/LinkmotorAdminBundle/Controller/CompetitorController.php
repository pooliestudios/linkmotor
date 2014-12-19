<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorBundle\Entity\Competitor;
use Pool\LinkmotorAdminBundle\Form\Type\CompetitorAddType;
use Pool\LinkmotorAdminBundle\Form\Type\CompetitorEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class CompetitorController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/explorer/competitors/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_competitor_index")
     * @Method("GET")
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
            return $this->redirect($this->generateUrl('pool_linkmotor_competitor_index'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Competitor')
            ->getQueryForCompetitorIndex($project);
        $paginator  = $this->get('knp_paginator');
        $competitors = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('project' => $project, 'competitors' => $competitors);
    }

    /**
     * @Route("/{_locale}/admin/explorer/competitors/add/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_competitor_add")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('competitor-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        $competitor = new Competitor();
        $form = $this->createForm(new CompetitorAddType(), $competitor);

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
                    ->trans("You cannot add the project's domain as competitor", array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            $domainsService = $this->get('linkmotor.domains');
            if ($domainsService->isSubdomain($domainName)) {
                $domainIsValid = false;
                $errorMessage = $this->get('translator')
                    ->trans('You need to specify a domain, not a subdomain.', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            if ($form->isValid() && $domainIsValid) {
                $domain = $domainsService->addDomain($domainName);

                $competitorFound = $this->getDoctrine()
                    ->getRepository('PoolLinkmotorBundle:Competitor')
                    ->findBy(array('domain' => $domain->getId(), 'project' => $project->getId()));
                if ($competitorFound) {
                    $errorMessage = $this->get('translator')
                        ->trans('This competitor has already been added', array(), 'validators');
                    $form->addError(new FormError($errorMessage));
                } else {
                    $competitor->setDomain($domain);
                    $competitor->setProject($project);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($competitor);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'success',
                        'The competitor has been added'
                    );

                    $domainsService->deleteAllNewPagesFor($domain, $project);

                    // Initial import after adding
                    $numImported = $this->get('linkmotor.pages')->importFromCompetitor($competitor);
                    if (!$numImported) {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'The import was not successful'
                        );
                        return $this->redirect($this->generateUrl('pool_linkmotor_explorer_competitor_index'));
                    } else {
                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'The import was successful'
                        );
                        return $this->redirect(
                            $this->generateUrl(
                                'pool_linkmotor_explorer_competitor_view',
                                array('id' => $competitor->getId())
                            )
                        );
                    }

                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/explorer/competitors/{id}/edit/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_competitor_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Competitor $competitor)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('competitor-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project || $project != $competitor->getProject()) {
            $project = $competitor->getProject();
            $this->setSelectedProject($project);
        }

        $form = $this->createForm(new CompetitorEditType(), $competitor);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $competitor->setProject($project); // @todo Ist das wirklich notwendig?

                $em = $this->getDoctrine()->getManager();
                $em->persist($competitor);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                return $this->redirect($this->generateUrl('pool_linkmotor_explorer_competitor_index'));
            }
        }
        return array('competitor' => $competitor, 'form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/explorer/competitors/{id}/view/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_competitor_view")
     * @Method("GET")
     * @Template()
     */
    public function viewAction(Competitor $competitor)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('competitor-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project || $project != $competitor->getProject()) {
            $project = $competitor->getProject();
            $this->setSelectedProject($project);
        }

        $paginator  = $this->get('knp_paginator');
        $pages = $paginator->paginate(
            $competitor->getPages(),
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('competitor' => $competitor, 'pages' => $pages);
    }

    /**
     * @Route("/{_locale}/admin/explorer/competitors/{id}/delete/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_competitor_delete")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function deleteAction(Request $request, Competitor $competitor)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('competitor-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            foreach ($competitor->getPages() as $page) {
                $page->setSourceCompetitor(null);
                $em->persist($page);
            }

            $em->remove($competitor);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The competitor has been deleted'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_explorer_competitor_index'));
        }

        return array('competitor' => $competitor);
    }

    /**
     * @Route("/{_locale}/admin/explorer/competitors/{id}/import/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_competitor_import")
     * @Method("GET")
     * @Template()
     */
    public function importAction(Competitor $competitor)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('competitor-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        $numImported = $this->get('linkmotor.pages')->importFromCompetitor($competitor);
        if (!$numImported) {
            $this->get('session')->getFlashBag()->add(
                'error',
                'The import was not successful'
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'success',
                'The import was successful'
            );
        }

        return $this->redirect($this->generateUrl('pool_linkmotor_explorer_competitor_index'));
    }
}
