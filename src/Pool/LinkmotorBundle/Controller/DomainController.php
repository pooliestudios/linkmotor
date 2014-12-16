<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Domain;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DomainController extends BaseController
{
    /**
     * @Route("/{_locale}/app/domains/", defaults={"_locale" = "en"}, name="pool_linkmotor_domains_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Domain nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($this->hasFilterChanged($request)) {
            // Notwendig, da der Parameter sonst auch beim Paginieren angehängt wird
            return $this->redirect($this->generateUrl('pool_linkmotor_domains_index'));
        }

        $domains = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Domain')
            ->getForProject($project, $this->getFilter());

        $paginator  = $this->get('knp_paginator');
        $domains = $paginator->paginate(
            $domains,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('project' => $project, 'domains' => $domains);
    }

    /**
     * @Route("/{_locale}/app/domains/view/{id}/", defaults={"_locale" = "en"}, name="pool_linkmotor_domains_view")
     * @Template()
     */
    public function viewAction(Domain $domain)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Domain nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        return array('domain' => $domain, 'project' => $project);
    }

    /**
     * @Template()
     */
    public function filterAction()
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Domain nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $vendors = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Vendor')->getForProject($project);

        return array(
            'vendors' => $vendors,
            'filter' => $this->getFilter()
        );
    }
}
