<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Project;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends BaseController
{
    /**
     * @Route("/anonymizer/", name="pool_linkmotor_anonymizer")
     */
    public function anonymizerAction(Request $request)
    {
        $targetUrl = $request->query->get('url');
        if (strpos($targetUrl, '//') === 0) {
            $targetUrl = 'http:' . $targetUrl;
        }

        return $this->redirect('http://anonym.to/?' . urlencode($targetUrl));
    }

    /**
     * @Route("/", name="pool_linkmotor_no_language_index")
     * @Template()
     */
    public function indexNoLanguageAction(Request $request)
    {
        if ($this->getUser()) {
            $locale = $this->getUser()->getLocale();
        } else {
            $language = $request->getPreferredLanguage();
            $locale = strtolower(\Locale::getPrimaryLanguage($language));
        }

        if (!in_array($locale, $this->supportedLanguages)) {
            $locale = 'en';
        }

        return $this->redirect($this->generateUrl('pool_linkmotor_index', array('_locale' => $locale)));
    }

    /**
     * @Route("/{_locale}/", defaults={"_locale" = "en"}, name="pool_linkmotor_index")
     */
    public function indexAction()
    {
        if ($this->getUser()) {
            if (!$this->get('session')->get('selectedProjectId')) {
                $project = $this->getUser()->getLastUsedProject();
                if ($project) {
                    $this->get('session')->set('selectedProjectId', $project->getId());
                    $this->get('session')->set('selectedProjectName', $project->getName());
                }
            }
            return $this->redirect($this->generateUrl('pool_linkmotor_dashboard'));
        } else {
            return $this->redirect($this->generateUrl('login'));
        }
    }

    /**
     * @Route("/{_locale}/pricing/", name="pool_linkmotor_pricing")
     * @Template()
     */
    public function pricingAction()
    {
        return array();
    }

    /**
     * @Route("/{_locale}/account/limited/", name="pool_linkmotor_account_limited")
     * @Template()
     */
    public function accountLimitedPageAction(Request $request)
    {
        $this->get('security.context')->setToken(null);
        $request->getSession()->invalidate();

        return array();
    }

    /**
     * @Route("/{_locale}/app/limits/reached/", name="pool_linkmotor_limits_reached")
     * @Template()
     */
    public function limitsReachedPageAction()
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_admin_account_edit'));
        }

        return array();
    }

    /**
     * @Route("/{_locale}/app/limits/overstepped/", name="pool_linkmotor_limits_overstepped")
     * @Template()
     */
    public function limitsOversteppedPageAction()
    {
        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return $this->redirect($this->generateUrl('pool_linkmotor_admin_account_edit'));
        }

        return array();
    }

    /**
     * @Route("/{_locale}/app/projects/select/{id}/", defaults={"_locale" = "en"}, name="pool_linkmotor_select_project")
     * @Template()
     */
    public function projectsSelectAction(Project $project)
    {
        $limits = $this->get('linkmotor.limits');
        if ($limits->projectsLimitOverstepped()) {
            $projects = $limits->getSelectableProjects();
            $isLimitedProject = false;
            foreach ($projects as $limitedProject) {
                if ($limitedProject->getId() == $project->getId()) {
                    $isLimitedProject = true;
                    break;
                }
            }
            if (!$isLimitedProject) {
                return $this->redirect($this->generateUrl('pool_linkmotor_limits_overstepped'));
            }
        }

        $this->setSelectedProject($project);

        $user = $this->getUser();
        $user->setLastUsedProject($project);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush($user);

        return $this->redirect(
            $this->generateUrl('pool_linkmotor_project_dashboard', array('id' => $project->getId()))
        );
    }

    /**
     * @Route("/ajax/project-alert-badge/", name="pool_linkmotor_ajax_project_alert_badge")
     * @Template()
     */
    public function alertBadgeAction()
    {
        $number = 0;
        $selectedProjectId = $this->get('session')->get('selectedProjectId');
        if ($selectedProjectId) {
            $project = $this->getDoctrine()
                ->getRepository('PoolLinkmotorBundle:Project')
                ->find($selectedProjectId);

            $number = $this->getDoctrine()
                ->getRepository('PoolLinkmotorBundle:Alert')
                ->getCount($project);
        }

        return array('number' => $number);
    }

    /**
     * @Route("/ajax/project-new-pages-badge/", name="pool_linkmotor_ajax_project_new_pages_badge")
     * @Template()
     */
    public function newPagesBadgeAction()
    {
        $number = 0;
        $selectedProject = $this->getSelectedProject();
        if ($selectedProject) {
            $number = $this->getDoctrine()
                ->getRepository('PoolLinkmotorBundle:Page')
                ->getNewPagesCount($selectedProject, $this->getUser());
        }

        return array('number' => $number);
    }

    /**
     * @Template()
     */
    public function metaNavigationAction($route)
    {
        $limitedProjects = array();
        $limits = $this->get('linkmotor.limits');
        if ($limits->projectsLimitOverstepped()) {
            $limitedProjects = $limits->getSelectableProjects();
            $selectedProject = $this->getSelectedProject();
            if ($selectedProject) {
                $isLimitedProject = false;
                foreach ($limitedProjects as $limitedProject) {
                    if ($limitedProject->getId() == $selectedProject->getId()) {
                        $isLimitedProject = true;
                        break;
                    }
                }
                if (!$isLimitedProject) {
                    $this->setSelectedProject($limitedProjects[0]);
                }
            }
        }

        $projects = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Project')->findAll();

        return array('projects' => $projects, 'limitedProjects' => $limitedProjects, 'route' => $route);
    }
}
