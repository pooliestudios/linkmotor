<?php

namespace Pool\LinkmotorAdminBundle\Controller;

use Pool\LinkmotorAdminBundle\Form\Type\ProjectSettingsType;
use Pool\LinkmotorBundle\Controller\BaseController;
use Pool\LinkmotorBundle\Entity\Competitor;
use Pool\LinkmotorBundle\Entity\Keyword;
use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorAdminBundle\Form\Type\ProjectAddType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;

class ProjectController extends BaseController
{
    /**
     * @Route("/{_locale}/admin/projects/", defaults={"_locale" = "en"}, name="pool_linkmotor_admin_projects_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $projects = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Project')->findAll();

        // @todo Richtige Query nutzen
        $paginator  = $this->get('knp_paginator');
        $projects = $paginator->paginate(
            $projects,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('projects' => $projects);
    }

    /**
     * @Route("/{_locale}/admin/export/", defaults={"_locale" = "en"}, name="pool_linkmotor_admin_export")
     * @Method("GET")
     * @Template()
     */
    public function exportAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/admin/projects/add/", defaults={"_locale" = "en"}, name="pool_linkmotor_admin_projects_add")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        if ($this->get('linkmotor.limits')->projectsLimitReached()) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $form = $this->createForm(new ProjectAddType());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            if ($form->isValid()) {
                $translator = $this->get('translator');
                $domainOrSubdomain = $form['domainOrSubdomain']->getData();
                $domainOrSubdomainIsValid = $domainOrSubdomain && !preg_match('/[\/:]/', $domainOrSubdomain);
                if (!$domainOrSubdomainIsValid) {
                    $errorMessage = $translator->trans(
                        'The domain or subdomain may not contain / or :',
                        array(),
                        'validators'
                    );
                    $form->get('domainOrSubdomain')->addError(new FormError($errorMessage));
                }
                $competitorDomain = $form['competitorDomain']->getData();
                $competitorDomainIsValid = !preg_match('/[\/:]/', $competitorDomain);
                if (!$competitorDomainIsValid) {
                    $errorMessage = $translator->trans('The domain may not contain / or :', array(), 'validators');
                    $form->get('competitorDomain')->addError(new FormError($errorMessage));
                }
                $domainsService = $this->get('linkmotor.domains');
                if ($competitorDomain && $domainsService->isSubdomain($competitorDomainIsValid)) {
                    $competitorDomainIsValid = false;
                    $errorMessage = $translator->trans(
                        'You need to specify a domain, not a subdomain.',
                        array(),
                        'validators'
                    );
                    $form->get('competitorDomain')->addError(new FormError($errorMessage));
                }
                if ($domainOrSubdomainIsValid && $competitorDomainIsValid) {
                    $em = $this->getDoctrine()->getManager();

                    $project = null;
                    $domain = null;
                    $subdomain = null;
                    $projectRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Project');
                    if ($domainsService->isDomain($domainOrSubdomain)) {
                        $domain = $domainsService->addDomain($domainOrSubdomain);
                        $project = $projectRepository->findOneByDomain($domain->getId());
                    } else {
                        $subdomain = $domainsService->addDomainAndSubdomain($domainOrSubdomain);
                        $project = $projectRepository->findOneBySubdomain($subdomain->getId());
                    }

                    if ($project) {
                        $errorMessage = $translator->trans(
                            'This project has already been created',
                            array(),
                            'validators'
                        );
                        $form->get('domainOrSubdomain')->addError(new FormError($errorMessage));
                    } else {
                        $project = new Project();
                        if ($subdomain) {
                            $project->setSubdomain($subdomain);
                        } else {
                            $project->setDomain($domain);
                        }
                        $em->persist($project);
                        $em->flush();

                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'The project has been added'
                        );

                        $linkmotorPages = $this->get('linkmotor.pages');
                        if ($competitorDomain) {
                            $competitorDomainObject = $domainsService->addDomain($competitorDomain);
                            $competitor = new Competitor();
                            $competitor->setProject($project);
                            $competitor->setDomain($competitorDomainObject);
                            $em->persist($competitor);
                            $em->flush();

                            $linkmotorPages->importFromCompetitor($competitor);
                        }

                        $market = $this->getDoctrine()
                            ->getRepository('PoolLinkmotorBundle:Market')
                            ->findOneByIsoCode('de');
                        for ($i=1; $i<=3; $i++) {
                            $keywordLabel = $form->get("keyword{$i}")->getData();
                            if ($keywordLabel) {
                                $keyword = new Keyword();
                                $keyword->setKeyword($keywordLabel);
                                $keyword->setProject($project);
                                $keyword->setMarket($market);
                                $em->persist($keyword);
                                $em->flush();

                                $linkmotorPages->importFromKeyword($keyword);
                            }
                        }

                        return $this->redirect(
                            $this->generateUrl('pool_linkmotor_select_project', array('id' => $project->getId()))
                        );
                    }
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/projects/{id}/settings", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_admin_projects_settings")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function settingsAction(Request $request, Project $project)
    {
        $form = $this->createForm(new ProjectSettingsType(), $project->getSettings());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $project->setSettings($form->getData());

            $em = $this->getDoctrine()->getManager();
            $em->persist($project);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The project settings have been updated'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_admin_projects_index'));
        }

        return array('project' => $project, 'form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/admin/projects/{id}/delete", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_admin_projects_delete")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function deleteAction(Request $request, Project $project)
    {
        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            // Reset all "last used projects"
            $users = $this->getDoctrine()
                ->getRepository('PoolLinkmotorBundle:User')
                ->findByLastUsedProject($project->getId());
            foreach ($users as $user) {
                $user->setLastUsedProject(null);
                $em->persist($user);
            }

            if ($this->getSelectedProject() == $project) {
                $this->setSelectedProject(null);
            }

            // Delete the project
            $em->remove($project);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The project has been deleted'
            );

            return $this->redirect($this->generateUrl('pool_linkmotor_admin_projects_index'));
        }

        return array('project' => $project);
    }
}
