<?php

namespace Pool\LinkmotorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class LimitController extends BaseController
{
    /**
     * @Route("/{_locale}/account/limited/", name="pool_linkmotor_account_limited")
     * @Method("GET")
     * @Template()
     */
    public function accountLimitedPageAction(Request $request)
    {
        $this->get('security.context')->setToken(null);
        $request->getSession()->invalidate();

        return array();
    }

    /**
     * @Route("/{_locale}/app/limits/reached/", name="pool_linkmotor_limits_reached")
     * @Method("GET")
     * @Template()
     */
    public function limitsReachedPageAction()
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_admin_account_edit'));
        }

        return array();
    }

    /**
     * @Route("/{_locale}/app/limits/overstepped/", name="pool_linkmotor_limits_overstepped")
     * @Method("GET")
     * @Template()
     */
    public function limitsOversteppedPageAction()
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_admin_account_edit'));
        }

        return array();
    }
}
