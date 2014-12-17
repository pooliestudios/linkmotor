<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\PageNote;
use Pool\LinkmotorBundle\Form\Type\PageNoteAddType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;

class PageNoteController extends BaseController
{

    /**
     * @Route("/{_locale}/app/page/{id}/notes/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_notes")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function indexAction(Request $request, Page $page)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $page->getProject()) {
            $project = $page->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $pageNote = new PageNote();
        $form = $this->createForm(new PageNoteAddType(), $pageNote);

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $pageNote->setPage($page);
                $pageNote->setUser($this->getUser());

                $em = $this->getDoctrine()->getManager();
                $em->persist($pageNote);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your note has been added'
                );
                return $this->redirect($this->generateUrl('pool_linkmotor_pages_notes', array('id' => $page->getId())));
            }
        }

        return array('page' => $page, 'project' => $project, 'form' => $form->createView());
    }
}
