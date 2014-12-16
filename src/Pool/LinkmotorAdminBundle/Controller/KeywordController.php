<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorBundle\Entity\Keyword;
use Pool\LinkmotorAdminBundle\Form\Type\KeywordAddType;
use Pool\LinkmotorAdminBundle\Form\Type\KeywordEditType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class KeywordController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/explorer/keywords/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_keyword_index")
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
            return $this->redirect($this->generateUrl('pool_linkmotor_keyword_index'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Keyword')
            ->getQueryForKeywordIndex($project);
        $paginator  = $this->get('knp_paginator');
        $keywords = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('keywords' => $keywords);
    }

    /**
     * @Route("/{_locale}/admin/explorer/keywords/add/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_keyword_add")
     * @Template()
     */
    public function addAction($_locale, Request $request)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('keyword-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        $keyword = new Keyword();
        $keywordAddType = new KeywordAddType();
        $keywordAddType->setLocale($_locale);
        $form = $this->createForm($keywordAddType, $keyword);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $keywordFound = $this->getDoctrine()
                ->getRepository('PoolLinkmotorBundle:Keyword')
                ->findBy(array('keyword' => $keyword->getKeyword(), 'project' => $project->getId()));
            if ($keywordFound) {
                $errorMessage = $this->get('translator')
                    ->trans('This keyword has already been added', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            if ($form->isValid() && !$keywordFound) {
                $keyword->setProject($project);

                $em = $this->getDoctrine()->getManager();
                $em->persist($keyword);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'The keyword has been added'
                );

                // Initial import after adding
                $numImported = $this->get('linkmotor.pages')->importFromKeyword($keyword);
                if (!$numImported) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'The import was not successful'
                    );
                    return $this->redirect($this->generateUrl('pool_linkmotor_explorer_keyword_index'));
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'success',
                        'The import was successful'
                    );
                    return $this->redirect(
                        $this->generateUrl(
                            'pool_linkmotor_explorer_keyword_view',
                            array('id' => $keyword->getId())
                        )
                    );
                }


            }
        }
        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/explorer/keywords/{id}/edit/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_keyword_edit")
     * @Template()
     */
    public function editAction($_locale, Request $request, Keyword $keyword)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('keyword-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project || $project != $keyword->getProject()) {
            $project = $keyword->getProject();
            $this->setSelectedProject($project);
        }

        $keywordEditType = new KeywordEditType();
        $keywordEditType->setLocale($_locale);
        $form = $this->createForm($keywordEditType, $keyword);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($keyword);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                return $this->redirect($this->generateUrl('pool_linkmotor_explorer_keyword_index'));
            }
        }
        return array('keyword' => $keyword, 'form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/explorer/keywords/{id}/view/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_keyword_view")
     * @Template()
     */
    public function viewAction(Keyword $keyword)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('keyword-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project || $project != $keyword->getProject()) {
            $project = $keyword->getProject();
            $this->setSelectedProject($project);
        }

        $paginator  = $this->get('knp_paginator');
        $pages = $paginator->paginate(
            $keyword->getPages(),
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('keyword' => $keyword, 'pages' => $pages);
    }

    /**
     * @Route("/{_locale}/admin/explorer/keywords/{id}/delete/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_keyword_delete")
     * @Template()
     */
    public function deleteAction(Request $request, Keyword $keyword)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('keyword-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            foreach ($keyword->getPages() as $page) {
                $page->setSourceKeyword(null);
                $em->persist($page);
            }

            $em->remove($keyword);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The keyword has been deleted'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_explorer_keyword_index'));
        }

        return array('keyword' => $keyword);
    }

    /**
     * @Route("/{_locale}/admin/explorer/keywords/{id}/import/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_explorer_keyword_import")
     * @Template()
     */
    public function importAction(Keyword $keyword)
    {
        if (!$this->get('linkmotor.limits')->isAvailable('keyword-explorer')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }

        $numImported = $this->get('linkmotor.pages')->importFromKeyword($keyword);
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

        return $this->redirect($this->generateUrl('pool_linkmotor_explorer_keyword_index'));
    }
}
