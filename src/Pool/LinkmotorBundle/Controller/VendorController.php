<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Vendor;
use Pool\LinkmotorBundle\Form\Type\SetVendorType;
use Pool\LinkmotorBundle\Form\Type\VendorEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class VendorController extends BaseController
{
    /**
     * @Route("/{_locale}/app/vendors/", defaults={"_locale" = "en"}, name="pool_linkmotor_vendors_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Vendor nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($this->hasFilterChanged($request)) {
            // Notwendig, da der Parameter sonst auch beim Paginieren angehängt wird
            return $this->redirect($this->generateUrl('pool_linkmotor_vendors_index'));
        }

        $vendors = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Vendor')
            ->getForProject($project, $this->getFilter());

        $paginator  = $this->get('knp_paginator');
        $vendors = $paginator->paginate(
            $vendors,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('vendors' => $vendors);
    }

    /**
     * @Route("/{_locale}/app/vendor/{id}/view/", defaults={"_locale" = "en"}, name="pool_linkmotor_vendors_view")
     * @Method("GET")
     * @Template()
     */
    public function viewAction(Vendor $vendor)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Vendor nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        return array('vendor' => $vendor, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/vendor/{id}/delete/", defaults={"_locale" = "en"}, name="pool_linkmotor_vendors_delete")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function deleteAction(Request $request, Vendor $vendor)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Vendor nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($request->getMethod() == 'POST'
            && $vendor->getDomains()->count() == 0
            && $vendor->getSubdomains()->count() == 0
        ) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($vendor);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The contact has been deleted'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_vendors_index'));
        }

        return array('vendor' => $vendor);
    }

    /**
     * @Route("/{_locale}/app/vendor/{id}/changelog/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_vendors_changelog")
     * @Method("GET")
     * @Template()
     */
    public function changelogAction(Vendor $vendor)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            // @todo Wenn Vendor nur in ein Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $logEntries = $this->getDoctrine()
            ->getRepository('Gedmo\Loggable\Entity\LogEntry')
            ->getLogEntries($vendor);

        $paginator  = $this->get('knp_paginator');
        $logEntries = $paginator->paginate(
            $logEntries,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('logEntries' => $logEntries, 'vendor' => $vendor, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/vendor/{id}/edit/", defaults={"_locale" = "en"}, name="pool_linkmotor_vendors_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Vendor $vendor)
    {
        $form = $this->createForm(new VendorEditType(), $vendor);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($vendor);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                return $this->redirect(
                    $this->generateUrl('pool_linkmotor_vendors_view', array('id' => $vendor->getId()))
                );
            }
        }

        return array('vendor' => $vendor, 'form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/vendors/{type}/{id}/set-vendor/", defaults={"_locale" = "en", "type" = "domain"},
     *        name="pool_linkmotor_domains_set_vendor")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function setVendorAction(Request $request, $type, $id)
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
        
        $em = $this->getDoctrine()->getManager();

        if ($type == 'domain') {
            $object = $em->getRepository('PoolLinkmotorBundle:Domain')->find($id);
        } elseif ($type == 'subdomain') {
            $object = $em->getRepository('PoolLinkmotorBundle:Subdomain')->find($id);
        } else {
            throw $this->createNotFoundException('Page not found!');
        }
        $formData = array();
        $contactInfo = array();
        $contactInfoSearched = false;
        $form = $this->createForm(new SetVendorType(), $formData);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);

            if ($form->get('search')->isClicked()) {
                if ($type == 'domain') {
                    $contactInfo = $this->get('seoservices')->getContactInfoForDomain($object);
                } else {
                    $contactInfo = $this->get('seoservices')->getContactInfoForSubdomain($object);
                }
                $contactInfoSearched = true;
                if (isset($contactInfo['data']['email']) && $contactInfo['data']['email']) {
                    $formData['newVendorEmail'] = $contactInfo['data']['email'];
                }
                if (isset($contactInfo['data']['company']) && $contactInfo['data']['company']) {
                    $formData['newVendorCompany'] = $contactInfo['data']['company'];
                }
                if (isset($contactInfo['data']['contact']) && $contactInfo['data']['contact']) {
                    $formData['newVendorName'] = $contactInfo['data']['contact'];
                }
                if (isset($contactInfo['data']['phone']) && $contactInfo['data']['phone']) {
                    $formData['newVendorPhone'] = $contactInfo['data']['phone'];
                }
                if (isset($contactInfo['data']['street']) && $contactInfo['data']['street']) {
                    $formData['newVendorStreet'] = $contactInfo['data']['street'];
                }
                if (isset($contactInfo['data']['zip']) && $contactInfo['data']['zip']) {
                    $formData['newVendorZipcode'] = $contactInfo['data']['zip'];
                }
                if (isset($contactInfo['data']['city']) && $contactInfo['data']['city']) {
                    $formData['newVendorCity'] = $contactInfo['data']['city'];
                }

                // Damit die Daten nun im Formular sind
                $form = $this->createForm(new SetVendorType(), $formData);
            } else {
                $isValid = true;
                $vendor = null;
                if ((!$form['vendor']->getData() && !$form['newVendorEmail']->getData())
                    || ($form['vendor']->getData() && $form['newVendorEmail']->getData())
                ) {
                    $msg = $this->get('translator')
                        ->trans('You have to choose a vendor or enter the email address of a new one');
                    $form->addError(new FormError($msg));
                    $isValid = false;
                } elseif ($form['newVendorEmail']->getData()) {
                    $vendor = new Vendor();
                    $vendor->setEmail($form['newVendorEmail']->getData());
                    $vendor->setName($form['newVendorName']->getData());
                    $vendor->setCompany($form['newVendorCompany']->getData());
                    $vendor->setStreet($form['newVendorStreet']->getData());
                    $vendor->setZipcode($form['newVendorZipcode']->getData());
                    $vendor->setCity($form['newVendorCity']->getData());
                    $vendor->setPhone($form['newVendorPhone']->getData());
                    $em->persist($vendor);
                    $em->flush();
                } elseif ($form['vendor']->getData()) {
                    $vendor = $form['vendor']->getData();
                }

                if ($isValid) {
                    $object->setVendor($vendor);
                    $em->persist($object);
                    $em->flush();

                    $this->get('session')->getFlashBag()->add(
                        'success',
                        'The vendor has been set'
                    );

                    return $this->redirect(
                        $this->generateUrl('pool_linkmotor_vendors_view', array('id' => $vendor->getId()))
                    );
                }
            }
        }

        return array(
            'object' => $object,
            'type' => $type,
            'project' => $project,
            'form' => $form->createView(),
            'contactInfo' => $contactInfo,
            'contactInfoSearched' => $contactInfoSearched
        );
    }

    /**
     * @Template()
     */
    public function filterAction()
    {
        return array('filter' => $this->getFilter());
    }
}
