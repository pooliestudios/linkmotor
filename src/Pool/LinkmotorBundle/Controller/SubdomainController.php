<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class SubdomainController extends BaseController
{
    /**
     * @Route("/{_locale}/app/subdomains/{id}/backlinks/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_subdomain")
     * @Method("GET")
     * @Template()
     */
    public function backlinkAction(Subdomain $subdomain)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn Subdomain nur in einem Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->getQueryForBacklinkIndex($project, null, array('subdomain' => $subdomain->getId()));

        $paginator  = $this->get('knp_paginator');
        $backlinks = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('subdomain' => $subdomain, 'backlinks' => $backlinks, 'type' => 'subdomain');
    }

    /**
     * @Route("/{_locale}/app/subdomains/{id}/pages/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_pages_subdomain")
     * @Method("GET")
     * @Template()
     */
    public function pageAction(Subdomain $subdomain)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn die Subdomain nur in einem Projekt verwendet wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->getQueryForPagesIndex($project, null, array('subdomain' => $subdomain->getId()));

        $paginator  = $this->get('knp_paginator');
        $pages = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('subdomain' => $subdomain, 'pages' => $pages, 'type' => 'subdomain');
    }
}
