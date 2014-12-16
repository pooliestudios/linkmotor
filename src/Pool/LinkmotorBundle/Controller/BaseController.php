<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Project;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{
    protected $supportedLanguages = array('de', 'en');

    /**
     * @return \Pool\LinkmotorBundle\Entity\Project $project
     */
    public function getSelectedProject()
    {
        $projectId = $this->get('session')->get('selectedProjectId');
        if (!$projectId) {
            return false;
        }

        return $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Project')->find($projectId);
    }

    /**
     * @param Project $project
     */
    public function setSelectedProject(Project $project = null)
    {
        if ($project) {
            $this->get('session')->set('selectedProjectId', $project->getId());
            $this->get('session')->set('selectedProjectName', $project->getName());
        } else {
            $this->get('session')->remove('selectedProjectId');
            $this->get('session')->remove('selectedProjectName');
        }
    }

    /**
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function noProjectSelectedRedirect()
    {
        return $this->redirect($this->generateUrl('pool_linkmotor_index'));
    }

    /**
     * @todo Evtl. geht das auch direkt über den SortSubscriber
     *
     * @param integer $projectId
     * @param string $which
     * @param string $type
     * @param Request $request
     * @param string $baseUrl
     * @return bool|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    protected function needsSortRedirect($projectId, $which, $type, Request $request, $baseUrl)
    {
        $which = "{$which}.{$projectId}";
        $sortSettings = array('sort' => '', 'direction' => 'asc');
        $allSortSettings = $request->getSession()->get("{$which}.sort", array());
        if ($this->getRequest()->query->get('sort')) {
            $sortSettings['sort'] = $this->getRequest()->query->get('sort');
            $sortSettings['direction'] = $this->getRequest()->query->get('direction', 'asc');
            $allSortSettings[$type] = $sortSettings;

            $request->getSession()->set("{$which}.sort", $allSortSettings);
        } else {
            if (isset($allSortSettings[$type])) {
                $sortSettings = $allSortSettings[$type];
                $page = $this->getRequest()->query->get('page', 1);
                $params = "sort={$sortSettings['sort']}&direction={$sortSettings['direction']}&page={$page}";

                return $this->redirect("{$baseUrl}?{$params}");
            }
        }

        return false;
    }

    protected function hasFilterChanged(Request $request)
    {
        $filter = $this->getFilter();
        $hasChanged = false;

        $from = $request->get('from');
        if ($from != 'all') {
            $filter = $this->getFilter($from);
        }
        $resetFilter = $request->get('resetFilter');
        if ($resetFilter !== null) {
            $filter = $this->getEmptyFilter();
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeStatusFilter = $request->get('changeStatusFilter');
        if ($changeStatusFilter !== null) {
            $filter['status'] = $changeStatusFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeCrawlTypeFilter = $request->get('changeCrawlTypeFilter');
        if ($changeCrawlTypeFilter !== null) {
            $filter['crawlType'] = $changeCrawlTypeFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeOfflineFilter = $request->get('changeOfflineFilter');
        if ($changeOfflineFilter !== null) {
            $filter['offline'] = $changeOfflineFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeBacklinkStatusFilter = $request->get('changeBacklinkStatusFilter');
        if ($changeBacklinkStatusFilter !== null) {
            $filter['backlinkStatus'] = $changeBacklinkStatusFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeDomainFilter = $request->get('changeDomainFilter');
        if ($changeDomainFilter !== null) {
            $filter['domain'] = $changeDomainFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeVendorFilter = $request->get('changeVendorFilter');
        if ($changeVendorFilter !== null) {
            $filter['vendor'] = $changeVendorFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeUserFilter = $request->get('changeUserFilter');
        if ($changeUserFilter !== null) {
            $filter['user'] = $changeUserFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeKeywordFilter = $request->get('changeKeywordFilter');
        if ($changeKeywordFilter !== null) {
            $filter['keyword'] = $changeKeywordFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeKeywordVendorFilter = $request->get('changeKeywordVendorFilter');
        if ($changeKeywordVendorFilter !== null) {
            $filter['keywordVendor'] = $changeKeywordVendorFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeCostTypeFilter = $request->get('changeCostTypeFilter');
        if ($changeCostTypeFilter !== null) {
            $filter['costType'] = $changeCostTypeFilter;
            $this->setFilter($filter);
            $hasChanged = true;
        }

        $changeDateFilter = $request->get('changeDateFilter');
        if ($changeDateFilter !== null) {
            $filter['date'] = $changeDateFilter;
            if ($changeDateFilter == 'manual') {
                $dateFrom = $request->get('date-filter-from');
                $dateTo = $request->get('date-filter-to');
                try {
                    new \DateTime("{$dateFrom} 00:00:00");
                    new \DateTime("{$dateTo} 23:59:59");
                    $filter['dateFrom'] = $dateFrom;
                    $filter['dateTo'] = $dateTo;
                    $filter['date'] = 'saved';
                } catch (\Exception $e) {
                    $this->get('session')->getFlashBag()->add(
                        'error',
                        'The given range is not valid'
                    );
                    $filter['date'] = '';
                }

            }
            $this->setFilter($filter);
            $hasChanged = true;
        }

        return $hasChanged;
    }

    protected function getFilter($type = 'all')
    {
        if ($type != 'all') {
            $filter = $this->getEmptyFilter();
            $filter['user'] = $this->getUser()->getId();
            switch ($type) {
                case 'my-new':
                    $filter['status'] = 'new';
                    break;
                case 'my-contacted':
                    $filter['status'] = 'contacted';
                    break;
                case 'my-relevant':
                    $filter['status'] = 'relevant';
                    break;
                case 'my-in-progress':
                    $filter['status'] = 'in-progress';
                    break;
                case 'my-alerts':
                    $filter['backlinkStatus'] = 'alerts';
            }
            return $filter;
        }

        $projectId = $this->get('session')->get('selectedProjectId');
        $filter = $this->get('session')->get('filter');
        if (!$filter) {
            $filter = array($projectId => array());
        }

        $projectFilter = isset ($filter[$projectId]) ? $filter[$projectId] : null;
        if ($projectFilter == null) {
            $projectFilter = $this->getEmptyFilter();
            $filter[$projectId] = $projectFilter;
        }

        $this->get('session')->set('filter', $filter);

        return $projectFilter;
    }

    protected function getEmptyFilter()
    {
        return array(
            'keyword' => '',
            'keywordVendor' => '',
            'status' => 'all',
            'domain' => 0,
            'vendor' => 0,
            'user' => 0,
            'backlinkStatus' => 'all',
            'crawlType' => 'all',
            'date' => '',
            'dateFrom' => '',
            'dateTo' => '',
            'costType' => ''
        );
    }

    protected function setFilter($projectFilter)
    {
        $projectId = $this->get('session')->get('selectedProjectId');
        $filter = $this->get('session')->get('filter');
        if (!$filter) {
            $filter = array();
        }
        $filter[$projectId] = $projectFilter;

        $this->get('session')->set('filter', $filter);
    }

    protected function getItemsPerPage()
    {
        $itemsPerPage = $this->get('session')->get('itemsPerPage');
        if (!$itemsPerPage) {
            $user = $this->getUser();
            if ($user) {
                $itemsPerPage = $user->getItemsPerPage();
            }
            if (!$itemsPerPage) {
                $itemsPerPage = 50;
            }
        }

        return $itemsPerPage;
    }

    /**
     * @param Page|Backlink $object
     * @return bool
     * @throws \Exception
     */
    protected function loggedInUserMayEdit($object)
    {
        if (!($object instanceof Backlink || $object instanceof Page)) {
            throw new \Exception('mayLoggedInUserEdit not supported for ' . get_class($object));
        }

        if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
            return true;
        }

        return $this->getUser() == $object->getAssignedTo();
    }

    /**
     * @param Project $project
     * @return bool
     */
    protected function isLimitedProject(Project $project)
    {
        $limits = $this->get('linkmotor.limits');
        if ($limits->prospectsLimitOverstepped()) {
            $selectableProjects = $limits->getSelectableProjects();
            foreach ($selectableProjects as $selectableProject) {
                if ($selectableProject->getId() == $project->getId()) {
                    return false;
                }
            }

            return true;
        }

        return false;
    }
}
