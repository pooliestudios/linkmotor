<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Project;
use Pool\LinkmotorBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class DashboardController extends BaseController
{
    protected $supportedLanguages = array('de', 'en');

    /**
     * @Route("/{_locale}/app/dashboard/{id}/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_project_dashboard")
     * @Method("GET")
     * @Template()
     */
    public function projectDashboardAction(Project $project)
    {
        if ($this->getSelectedProject() != $project) {
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $numUsers = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getNumNonSupportUsers();
        $user = $this->getUser();
        $dashboardValues = array(
            'my' => $this->getDashboardValues($project, $user),
            'project' => $this->getDashboardValues($project)
        );

        $backlinkRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Backlink');

        $newBacklinks = $backlinkRepository->getNewestBacklinksForProject($project, 10);
        $numBacklinksTotal = $backlinkRepository->getNumBacklinksOnline($project);
        $avgDomainAuthority = round($backlinkRepository->getAvgDomainAuthority($project));
        $avgPageAuthority = round($backlinkRepository->getAvgPageAuthority($project));
        $avgDomainNetPop = round($backlinkRepository->getAvgDomainNetPop($project));

        $numDomains = $backlinkRepository->getNumDomains($project);
        $topLinkTargets = $backlinkRepository->getTopLinkTargets($project, 10);
        $topAnchorTexts = $backlinkRepository->getTopAnchorTexts($project, 10);
        $costs = array(
            'monthly' => $backlinkRepository->getMonthlyCosts($project),
            'oneTime' => $backlinkRepository->getOneTimeCosts($project),
            'toToday' => $backlinkRepository->getCostsToToday($project)
        );
        $userStats = $this->getUserStats($project);

        if ($user->getOptionsShowDashboardTour()) {
            $user->setOptionsShowDashboardTour(false);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            // But now switch it back, so at least shows once!
            $user->setOptionsShowDashboardTour(true);
        }

        return compact(
            'project', 'dashboardValues', 'newBacklinks', 'numBacklinksTotal', 'userStats', 'costs',
            'avgDomainAuthority', 'avgPageAuthority', 'avgDomainNetPop',
            'numDomains', 'topLinkTargets', 'topAnchorTexts', 'numUsers'
        );
    }

    /**
     * @Route("/{_locale}/app/dashboard/", defaults={"_locale" = "en"},
     *         name="pool_linkmotor_dashboard")
     * @Method("GET")
     * @Template()
     */
    public function dashboardAction()
    {
        $user = $this->getUser();

        $projects = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Project')->findAll();
        $projectsToGetStatsFor = $projects;

        $limitedProjects = array();
        $limits = $this->get('linkmotor.limits');
        if ($limits->projectsLimitReached()) {
            $limitedProjects = $limits->getSelectableProjects();
            $projectsToGetStatsFor = $projectsToGetStatsFor;
        }

        if (!$projects) {
            return $this->redirect($this->generateUrl('pool_linkmotor_admin_projects_add'));
        }

        $result = array();
        foreach ($projectsToGetStatsFor as $project) {
            $result[] = array(
                'project' => $project,
                'dashboardValues' => array(
                    'my' => $this->getDashboardValues($project, $user),
                    'project' => $this->getDashboardValues($project)
                )
            );
        }

        return array('projects' => $result, 'limitedProjects' => $limitedProjects);
    }

    private function getUserStats(Project $project)
    {
        $userStats = array();
        $users = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        $actionStatsRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:ActionStats');
        foreach ($users as $user) {
            $userStats[] = array(
                'user' => $user,
                'numBacklinksCreated' => array(
                    'sum' => $actionStatsRepository->getSum('numBacklinksCreated', $user, $project),
                    'lastMonth' => $actionStatsRepository->getLastMonth('numBacklinksCreated', $user, $project),
                    'thisMonth' => $actionStatsRepository->getThisMonth('numBacklinksCreated', $user, $project)
                ),
                'numCheckedPages' => array(
                    'sum' => $actionStatsRepository->getSum('numCheckedPages', $user, $project),
                    'lastMonth' => $actionStatsRepository->getLastMonth('numCheckedPages', $user, $project),
                    'thisMonth' => $actionStatsRepository->getThisMonth('numCheckedPages', $user, $project)
                ),
                'numContactsMade' => array(
                    'sum' => $actionStatsRepository->getSum('numContactsMade', $user, $project),
                    'lastMonth' => $actionStatsRepository->getLastMonth('numContactsMade', $user, $project),
                    'thisMonth' => $actionStatsRepository->getThisMonth('numContactsMade', $user, $project)
                )
            );
        }

        return $userStats;
    }

    /**
     * @param Project $project
     * @param User $user
     *
     * @return array
     */
    private function getDashboardValues(Project $project, User $user = null)
    {
        $pageRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Page');
        $alertRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Alert');

        return array(
            'alerts' => $alertRepository->getCount($project, $user),
            'newPages' => $pageRepository->getNewPagesCount($project, $user),
            'relevantPages' => $pageRepository->getRelevantPagesCount($project, $user),
            'contactedPages' => $pageRepository->getContactedPagesCount($project, $user),
            'inProgressPages' => $pageRepository->getInProgressPagesCount($project, $user)
        );
    }
}
