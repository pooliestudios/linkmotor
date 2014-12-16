<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorAdminBundle\Form\Type\AdminSettingsType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class SettingController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/settings/edit/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_admin_settings_edit")
     * @Template()
     */
    public function editAction(Request $request)
    {
        $options = $this->get('linkmotor.options')->getAll();
        $form = $this->createForm(new AdminSettingsType(), $options);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $formData = $form->getData();
                $options['sistrix_active'] = $formData['sistrix_active'];
                $options['sistrix_api_key'] = $formData['sistrix_api_key'];
                $options['xovi_active'] = $formData['xovi_active'];
                $options['xovi_api_key'] = $formData['xovi_api_key'];

                if ($this->get('linkmotor.options')->get('self_hosted')) {
                    $options['account_id'] = $formData['account_id'];
                    $options['account_secret_key'] = $formData['account_secret_key'];
                }
                $this->get('linkmotor.options')->setAll($options);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                if ($this->get('linkmotor.options')->get('self_hosted')) {
                    if (!$this->get('seoservices')->checkAccount()) {
                        $this->get('session')->getFlashBag()->add(
                            'error',
                            'Your SEO-Services credentials are invalid'
                        );
                    }
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/settings/create-seo-service-account/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_admin_settings_create_seo_service_account")
     * @Template()
     */
    public function createSeoServiceAccountAction()
    {
        $options = $this->get('linkmotor.options')->getAll();

        $newAccountData = $this->get('seoservices')->registerSelfHostedAccount();
        if ($newAccountData) {
            $options['account_id'] = $newAccountData['slug'];
            $options['account_secret_key'] = $newAccountData['secret'];

            $this->get('linkmotor.options')->setAll($options);

            $this->get('session')->getFlashBag()->add(
                'success',
                'The account has been created'
            );
        } else {
            $this->get('session')->getFlashBag()->add(
                'error',
                'The account could not be created. Please try again later and make sure a connection to linkmotor.de can be established.'
            );
        }

        return $this->redirect($this->generateUrl('pool_linkmotor_admin_settings_edit'));
    }
}
