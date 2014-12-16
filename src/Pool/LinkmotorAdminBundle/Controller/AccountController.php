<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorAdminBundle\Form\Type\AccountType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class AccountController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/account/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_admin_account_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        $linkmotorOptions = $this->get('linkmotor.options');
        $options = $linkmotorOptions->getAll();
        $accountTypeBefore = $this->get('linkmotor.options')->get('account_type');
        $accountTypeForm = new AccountType();
        $accountTypeForm->setWithInvoiceInformation($accountTypeBefore == 0);
        $form = $this->createForm($accountTypeForm, $options);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $this->get('linkmotor.options')->setAll($form->getData());
                $accountTypeAfter = $this->get('linkmotor.options')->get('account_type');
                if ($accountTypeAfter != $accountTypeBefore) {
                    $updatedData = $this->get('seoservices')->updateAccountType($accountTypeAfter);
                    if ($updatedData) {
                        $linkmotorOptions->set('pro_account_until', $updatedData['proAccountUntil']['date']);
                        $linkmotorOptions->set('account_type', $updatedData['accountType']);
                    } else {
                        $linkmotorOptions->set('account_type', 0);
                    }
                }

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                // Redirect, damit die Account-Warnungen korrekt angezeigt werden
                return $this->redirect($this->generateUrl('pool_linkmotor_admin_account_edit'));
            }
        }

        return array('form' => $form->createView());
    }
}
