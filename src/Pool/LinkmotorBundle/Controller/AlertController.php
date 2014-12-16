<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Alert;
use Pool\LinkmotorBundle\Form\Type\AlertHideUntilType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AlertController extends BaseController
{
    /**
     * @Route("/{_locale}/app/alerts/{id}/hide-until/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_alerts_hide_until")
     * @Template()
     */
    public function hideUntilAction(Request $request, Alert $alert)
    {
        $alert->setHideUntil(new \DateTime('+24 hours'));
        $form = $this->createForm(new AlertHideUntilType(), $alert);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($alert);
                $em->flush($alert);

                $type = $this->getUser()->getOptionsDashboardType();
                return $this->redirect(
                    $this->generateUrl(
                        'pool_linkmotor_project_dashboard',
                        array('id' => $alert->getProject()->getId(), 'type' => $type)
                    )
                );
            }
        }

        return array('alert' => $alert, 'form' => $form->createView());
    }

    /**
     * @Route("/ajax/alerts/hide-until/form-row/", name="pool_linkmotor_ajax_alerts_hide_until_form_row")
     * @Template()
     */
    public function hideUntilFormRowAction(Request $request)
    {
        $alert = new Alert();
        $alert->setHideUntil(new \DateTime('+' . $request->get('value') . ' hours'));
        $form = $this->createForm(new AlertHideUntilType(), $alert);

        return array('form' => $form->createView());
    }
}
