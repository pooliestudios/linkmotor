<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Form\Type\BookmarkletSelect;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class BookmarkletController extends BaseController
{
    /**
     * @Route("/{_locale}/app/bookmarklet/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_bookmarklet")
     * @Template()
     */
    public function selectAction(Request $request)
    {
        $url = $request->get('url');

        $defaultData = array(
            'project' => $project = $this->getSelectedProject(),
            'url' => $url
        );

        $form = $this->createForm(new BookmarkletSelect(), $defaultData);

        if ($request->getMethod() == 'POST') {
            $translator = $this->get('translator');

            $form->submit($request);
            $isValid = true;
            $whatToDo = $form['whatToDo']->getData();
            $url = $form['url']->getData();
            if (!$whatToDo) {
                $errorMessage = $translator->trans('Please specify what to do with this url', array(), 'validators');
                $form->addError(new FormError($errorMessage));
                $isValid = false;
            }
            if (!$url) {
                $errorMessage = $translator->trans('Please enter a valid url', array(), 'validators');
                $form->addError(new FormError($errorMessage));
                $isValid = false;
            }
            if ($isValid) {
                $project = $form['project']->getData();
                $this->setSelectedProject($project);
                if ($whatToDo == 1) {
                    $token = substr(md5(uniqid()), 0, 8);
                    $request->getSession()->set('bookmarklet-submit-token', $token);
                    return $this->redirect(
                        $this->generateUrl('pool_linkmotor_pages_add', array('url' => $url, 'submit' => $token))
                    );
                } else {
                    return $this->redirect(
                        $this->generateUrl('pool_linkmotor_pages_search_backlinks', array('url' => $url))
                    );
                }
            }
        }

        return array('url' => $url, 'form' => $form->createView());
    }
}
