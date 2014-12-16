<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Import;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Pool\LinkmotorBundle\Entity\Vendor;
use Pool\LinkmotorBundle\Form\Type\BacklinkAddType;
use Pool\LinkmotorBundle\Form\Type\BacklinkImportType;
use Pool\LinkmotorBundle\Form\Type\BacklinkType;
use Pool\LinkmotorBundle\Form\Type\LinkbirdImportStep2Type;
use Pool\LinkmotorBundle\Form\Type\LinkbirdImportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ImportController extends BaseController
{
    /**
     * @Route("/{_locale}/app/imports/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_imports_index")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $imports = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Import')
            ->findByProject($project);

        return array('imports' => $imports);
    }

    /**
     * @Route("/{_locale}/app/imports/add/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_imports_add")
     * @Template()
     */
    public function addAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $form = $this->createForm(new LinkbirdImportType());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $uploadedFile = $form['file']->getData();
            $isValid = true;
            if ($uploadedFile == null) {
                $isValid = false;
                $errorMessage = $this->get('translator')
                    ->trans('You need to specify a file to upload', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            if ($isValid) {
                $import = new Import();
                $import->setType(Import::TYPE_LINKBIRD);
                $import->setProject($project);
                $import->setCreatedBy($this->getUser());
                $import->setFilename($uploadedFile->getClientOriginalName());

                $uploadedFile->move($import->getUploadRootDir(), $import->getImportFilename());

                $em = $this->getDoctrine()->getManager();
                $em->persist($import);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'The import has been started'
                );

                return $this->redirect($this->generateUrl('pool_linkmotor_imports_index'));
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/imports/{id}/step-2/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_import_step2")
     * @Template()
     */
    public function step2Action(Request $request, Import $import)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $import->getProject()) {
            $project = $import->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $linkbirdAnalyzeData = $import->getData();

        $linkbirdImportStep2Type = new LinkbirdImportStep2Type(
            $linkbirdAnalyzeData['users'],
            $linkbirdAnalyzeData['projects']
        );
        $form = $this->createForm($linkbirdImportStep2Type);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $linkbirdProject = $form['project']->getData();
            $userMappingEmpty = $form['userMappingEmpty']->getData()->getId();
            $userMapping = array();
            foreach ($linkbirdAnalyzeData['users'] as $idx => $user) {
                $userMapping[$idx] = $form['userMapping' . $idx]->getData()->getId();
            }

            $data = $import->getData();
            $data['project'] = $linkbirdAnalyzeData['projects'][$linkbirdProject];
            $data['userMappingEmpty'] = $userMappingEmpty;
            $data['userMapping'] = $userMapping;
            $import->setData($data);
            $import->setStep(3);
            $em = $this->getDoctrine()->getManager();
            $em->persist($import);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The import will continue shortly'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_imports_index'));
        }

        return array('form' => $form->createView(), 'import' => $import);
    }

    /**
     * @todo Dafür sorgen, dass die Dateien auch gelöscht werden, wenn
     * @todo die Entity durch das Löschen des Projektes entfernt wird
     * @Route("/{_locale}/app/imports/{id}/delete/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_import_delete")
     * @Template()
     */
    public function deleteAction(Request $request, Import $import)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $import->getProject()) {
            $project = $import->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($request->getMethod() == 'POST') {
            $uploadRootDir = $import->getUploadRootDir();
            @unlink($uploadRootDir . '/' . $import->getImportFilename());
            @unlink($uploadRootDir . '/' . $import->getTranscriptFilename());

            $em = $this->getDoctrine()->getManager();
            $em->remove($import);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The import has been deleted'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_imports_index'));
        }

        return array('import' => $import);
    }

    /**
     * @Route("/{_locale}/app/imports/{id}/download-transcript/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_import_download_transcript")
     */
    public function downloadTranscriptAction(Import $import)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $import->getProject()) {
            $project = $import->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $response = new Response();

        $uploadRootDir = $import->getUploadRootDir();
        $response->setContent(@file_get_contents($uploadRootDir . '/' . $import->getTranscriptFilename()));

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="transcript.csv"');

        return $response;
    }

    /**
     * @Route("/{_locale}/app/imports/{id}/ajax-refresh/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_import_ajax_refresh")
     * @Template()
     */
    public function ajaxRefreshAction(Import $import)
    {
        return array('import' => $import);
    }
}
