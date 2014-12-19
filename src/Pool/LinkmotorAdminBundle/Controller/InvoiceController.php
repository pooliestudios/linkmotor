<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorAdminBundle\Form\Type\InvoiceInformationType;
use Pool\LinkmotorBundle\Controller\BaseController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class InvoiceController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/invoices/information/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_admin_invoice_information_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request)
    {
        $options = $this->get('linkmotor.options')->getAll();
        $form = $this->createForm(new InvoiceInformationType(), $options);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $this->get('linkmotor.options')->setAll($form->getData());

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );
            }

            $this->redirect($this->generateUrl('pool_linkmotor_admin_invoice_information_edit'));
        }

        return array('form' => $form->createView());
    }
}
