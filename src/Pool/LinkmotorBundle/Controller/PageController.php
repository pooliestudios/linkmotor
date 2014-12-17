<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Pool\LinkmotorAdminBundle\Form\Type\BlacklistAddType;
use Pool\LinkmotorAdminBundle\Form\Type\CompetitorAddType;
use Pool\LinkmotorBundle\Form\Type\PageAddType;
use Pool\LinkmotorBundle\Form\Type\PageBulkActionsType;
use Pool\LinkmotorBundle\Form\Type\PageEditType;
use Pool\LinkmotorBundle\Form\Type\PageImportType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PageController extends BaseController
{
    /**
     * @Route("/{_locale}/app/page/{id}/search-backlinks/", defaults={"_locale" = "en", "id" = "0"},
     *        name="pool_linkmotor_pages_search_backlinks")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function searchBacklinksAction(Request $request, $id)
    {
        if ($this->get('linkmotor.limits')->backlinksLimitReached()) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $loggedInUserMayEdit = true;
        $em = $this->getDoctrine()->getManager();
        $url = $request->get('url');
        $page = null;
        if ($id) {
            $page = $em->getRepository('PoolLinkmotorBundle:Page')->find($id);
            if ($page) {
                $loggedInUserMayEdit = $this->loggedInUserMayEdit($page);
                $url = $page->getFull();
            }
        }

        $crawler = $this->get('crawler');

        $error = '';
        $selectedId = '';
        $didSearchForBacklinks = false;
        if ($request->getMethod() == 'POST') {
            $selectedId = $request->request->get('backlinkId');
            if ($selectedId === '') {
                $error = 'You did not select any backlink';
            } else {
                $data = $request->request->get('data');
                $data = $data[$selectedId]; // Wegen älterer PHP-Version auf dem Alfahosting-Server nicht in einer Zeile
                $backlink = new Backlink();
                $backlink->setProjectAndApplyDefaultValues($project);
                $backlink->setAnchor($data['anchor']);
                $backlink->setCrawlType('dom');
                $backlink->setFollow($data['follow']);
                $backlink->setUrl($data['url']);
                $backlink->setType($data['type']);
                $backlink->setStatusCode($request->request->get('urlInfo_httpStatusCode'));
                $backlink->setMetaIndex($request->request->get('urlInfo_metaIndex'));
                $backlink->setMetaFollow($request->request->get('urlInfo_metaFollow'));
                $backlink->setXRobotsIndex($request->request->get('urlInfo_xRobotsIndex'));
                $backlink->setXRobotsFollow($request->request->get('urlInfo_xRobotsFollow'));
                $backlink->setRobotsGoogle($request->request->get('urlInfo_robotsGoogle'));

                if (!$page) {
                    $pageCreator = $this->get('page_creator');
                    $page = $pageCreator->checkIfPageExists($project, $url);
                    if (!$page) {
                        $page = $pageCreator->addPage($project, $url, $this->getUser());
                        if (!$page) {
                            $error = $this->get('translator')
                                ->trans('Domain is on blacklist or is competitor', array(), 'validators');
                        }
                    }
                }

                if ($page) {
                    $backlink->setAssignedTo($page->getAssignedTo());
                    $backlink->setPage($page);

                    $sameBacklink = $em->getRepository('PoolLinkmotorBundle:Backlink')->findSame($backlink);
                    if ($sameBacklink) {
                        $error = $this->get('translator')
                            ->trans('The backlink already exists', array(), 'validators');
                        if (!$id) {
                            $page = null;
                        }
                    } else {
                        $page->setStatus($em->getRepository('PoolLinkmotorBundle:Status')->find(6));

                        $em->persist($page);
                        $em->persist($backlink);
                        $em->flush();

                        $crawler->crawlBacklink($backlink);

                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'The backlink has been added'
                        );

                        $request->getSession()->remove('bookmarklet.url');

                        return $this->redirect(
                            $this->generateUrl('pool_linkmotor_backlinks_view', array('id' => $backlink->getId()))
                        );
                    }
                }
            }
        }

        $backlinks = array();
        $urlInfo = array();
        if ($url) {
            $backlinks = $crawler->findBacklinksForProjectOnUrl($project, $url);
            $urlInfo = $crawler->getUrlInfo();
            $didSearchForBacklinks = true;
        }

        return array(
            'selectedId' => $selectedId,
            'url' => $url,
            'backlinks' => $backlinks,
            'error' => $error,
            'urlInfo' => $urlInfo,
            'page' => $page,
            'loggedInUserMayEdit' => $loggedInUserMayEdit,
            'pageId' => $id,
            'didSearchForBacklinks' => $didSearchForBacklinks
        );
    }

    /**
     * @Route("/{_locale}/app/pages/{type}/", defaults={"_locale" = "en", "type" = "my-new"},
     *        name="pool_linkmotor_pages_index")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request, $type)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($this->hasFilterChanged($request)) {
            // Notwendig, da der Parameter sonst auch beim Paginieren angehängt wird
            return $this->redirect($this->generateUrl('pool_linkmotor_pages_index', array('type' => $type)));
        }

        $sortRedirect = $this->needsSortRedirect(
            $project->getId(),
            'pages',
            $type,
            $request,
            $this->generateUrl('pool_linkmotor_pages_index', array('type' => $type))
        );
        if ($sortRedirect) {
            return $sortRedirect;
        }

        $user = null;
        if ($type != 'all') {
            $user = $this->getUser();
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->getQueryForPagesIndex($project, $user, $this->getFilter($type));

        $paginator  = $this->get('knp_paginator');
        $pages = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        $bulkData = array('bulkReturnToType' => $type);
        $bulkActionsType = new PageBulkActionsType();
        $users = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        $bulkActionsType->setUsers($users);
        $bulkActionsType->setIsAdmin($this->get('security.context')->isGranted('ROLE_ADMIN'));
        $bulkActionsForm = $this->createForm($bulkActionsType, $bulkData);

        return array('type' => $type, 'pages' => $pages, 'bulkActionsForm' => $bulkActionsForm->createView());
    }

    /**
     * @Route("/{_locale}/app/bulk-actions/pages/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_pages_bulk_action")
     * @Method("POST")
     * @Template()
     */
    public function bulkAction(Request $request)
    {
        $bulkActionsType = new PageBulkActionsType();
        $users = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        $bulkActionsType->setUsers($users);
        $form = $this->createForm($bulkActionsType);

        $form->submit($request);
        $pageIds = explode(',', $form['bulkItems']->getData());
        $action = explode('-', $form['bulkAction']->getData());
        if ($form['bulkAction']->getData() == null) {
            // Aus mir nicht bekanntem Grund komme ich mit der anderen Methode einfach nicht an delete-delete
            $action = $request->request->get('page_bulk_actions');
            $action = explode('-', $action['bulkAction']);
        }

        $em = $this->getDoctrine()->getManager();
        switch ($action[0]) {
            case 'status':
                $user = $this->getUser();
                foreach ($pageIds as $pageId) {
                    $page = $em->getRepository('PoolLinkmotorBundle:Page')->find($pageId);
                    if ($page->getStatusMayBeChangedByUser($user)) {
                        $status = $em->getRepository('PoolLinkmotorBundle:Status')->find($action[1]);
                        $page->setStatus($status);
                        $em->persist($page);
                        $em->flush();
                    }
                }
                break;
            case 'delete':
                if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                    foreach ($pageIds as $pageId) {
                        $page = $em->getRepository('PoolLinkmotorBundle:Page')->find($pageId);
                        if (!$page->hasBacklink()) {
                            $em->remove($page);
                            $em->flush();
                        }
                    }
                }
                break;
            case 'user':
                foreach ($pageIds as $pageId) {
                    $page = $em->getRepository('PoolLinkmotorBundle:Page')->find($pageId);
                    $user = $em->getRepository('PoolLinkmotorBundle:User')->find($action[1]);
                    $page->setAssignedTo($user);
                    $em->persist($page);
                    $em->flush();
                }
                break;
        }

        $this->get('session')->getFlashBag()->add(
            'success',
            'The bulk action has been executed'
        );

        $type = $form['bulkReturnToType']->getData();
        return $this->redirect($this->generateUrl('pool_linkmotor_pages_index', array('type' => $type)));
    }

    /**
     * @Route("/{_locale}/app/pages/{type}/export/", defaults={"_locale" = "en", "type" = "my"},
     *        name="pool_linkmotor_pages_export")
     * @Method("GET")
     */
    public function exportAction($type)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $filter = $this->getFilter($type);
        $user = null;
        if ($type != 'all') {
            $user = $this->getUser();
            $filter['user'] = $user->getId();
        }
        $pages = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->getQueryForPagesIndex($project, $user, $filter)
            ->getQuery()
            ->getResult();

        $content = $this->render(
            'PoolLinkmotorBundle:Page:list.csv.twig',
            array('filter' => $filter, 'pages' => $pages)
        );

        $filename = 'pages_' . date('Ymd_His');

        $response = new Response($content->getContent());
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename .'.csv"');

        return $response;
    }

    /**
     * @Route("/{_locale}/app/subdomains/{id}/pages/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_pages_subdomain")
     * @Method("GET")
     * @Template()
     */
    public function subdomainAction(Subdomain $subdomain)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn die Subdomain nur in einem Projekt verwendet wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Page')
            ->getQueryForPagesIndex($project, null, array('subdomain' => $subdomain->getId()));

        $paginator  = $this->get('knp_paginator');
        $pages = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('subdomain' => $subdomain, 'pages' => $pages, 'type' => 'subdomain');
    }

    /**
     * @Route("/{_locale}/app/import/pages/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_import")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function importAction(Request $request)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $form = $this->createForm(new PageImportType());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $uploadedFile = $form['file']->getData();
            $isValid = true;
            if ($uploadedFile == null) {
                $isValid = false;
                $form->addError(new FormError('You need to specify a file to upload'));
            }
            if ($isValid) {
                $subdomainsWithNewlyAddedPages = array();
                $pathToFile = $uploadedFile->getRealPath();
                $oldAutoDetectLineEndings = ini_get('auto_detect_line_endings');
                $delimiter = $form['delimiter']->getData();
                $enclosure = $form['enclosure']->getData();

                $userRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User');
                $pageCreator = $this->get('page_creator');

                ini_set('auto_detect_line_endings', true);
                $handle = fopen($pathToFile, 'r');
                $log = array();
                $numInvalidRows = 0;
                $invalidRows = array();
                while (($data = fgetcsv($handle, 1000, $delimiter, $enclosure)) !== false) {
                    if (count($data) != 2) {
                        $numInvalidRows++;
                        continue;
                    }
                    $data[1] = strtolower($data[1]);
                    if (!mb_check_encoding($data[0], 'UTF-8') || !mb_check_encoding($data[1], 'UTF-8')) {
                        $numInvalidRows++;
                        $invalidRows[] = $data;
                        continue;
                    }

                    $user = $userRepository->getUserByName($data[1]);
                    if (!$user || strtolower($data[1]) == 'support@linkmotor.de') {
                        $data[3] = false;
                        $data[4] = 'Unknown user';
                        $log[] = $data;
                        continue;
                    }
                    if ($pageCreator->checkIfPageExists($project, $data[0]) == true) {
                        $data[3] = false;
                        $data[4] = 'URL already in project';
                        $log[] = $data;
                        continue;
                    }
                    // Subdomains, die in diesem Import angelegt werden, sind natürlich erlaubt
                    $result = $pageCreator->checkIfSubdomainHasPages($project, $data[0]);
                    if ($result['hasPages']
                        && ($result['subdomain']
                            && !in_array($result['subdomain']->getId(), $subdomainsWithNewlyAddedPages)
                        )
                    ) {
                        $data[3] = false;
                        $data[4] = 'Subdomain already has pages in project';
                        $log[] = $data;
                        continue;
                    }

                    $createdPage = $pageCreator->addPage($project, $data[0], $user);
                    if (!$createdPage) {
                        $data[3] = false;
                        $data[4] = 'Domain is on blacklist or is competitor';
                        $log[] = $data;
                        continue;
                    }
                    $subdomainId = $createdPage->getSubdomain()->getId();
                    if (!in_array($subdomainId, $subdomainsWithNewlyAddedPages)) {
                        $subdomainsWithNewlyAddedPages[] = $subdomainId;
                    }
                    $data[1] = $user;
                    $data[3] = true;
                    $log[] = $data;
                }
                ini_set('auto_detect_line_endings', $oldAutoDetectLineEndings);

                if ($numInvalidRows > 0) {
                    $form->addError(new FormError($numInvalidRows . ' invalid rows in import file'));
                }
                if ($isValid) {
                    return array('log' => $log, 'invalidRows' => $invalidRows, 'form' => $form->createView());
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/page/add/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_add")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        if ($this->get('linkmotor.limits')->prospectsLimitReached()) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $submit = $request->get('submit');
        if ($submit) {
            $token = $request->getSession()->get('bookmarklet-submit-token');
            $request->getSession()->remove('bookmarklet-submit-token');
            if ($token != $submit) {
                $submit = false;
            }
        }
        $formOptions = array();
        if ($submit) {
            $formOptions = array('csrf_protection' => false);
        }
        $page = new Page();
        $page->setUrl($request->get('url'));
        $form = $this->createForm(new PageAddType(), $page, $formOptions);

        if ($request->getMethod() == 'POST' || $submit) {
            if ($submit) {
                $form->submit(array('url' => $request->get('url')));
            } else {
                $form->submit($request);
            }
            if ($form->isValid()) {
                $pageCreator = $this->get('page_creator');
                $pageFound = $pageCreator->checkIfPageExists($project, $page->getUrl());
                if ($pageFound) {
                    $this->get('session')->getFlashBag()->add(
                        'warning',
                        'The page already exists in the project'
                    );
                    return $this->redirect(
                        $this->generateUrl('pool_linkmotor_pages_view', array('id' => $pageFound->getId()))
                    );
                }

                $page = $pageCreator->addPage($project, $page->getUrl(), $this->getUser());
                if (!$page) {
                    $errorMessage = $this->get('translator')
                        ->trans('Domain is on blacklist or is competitor', array(), 'validators');
                    $form->addError(new FormError($errorMessage));
                } else {
                    $this->get('session')->getFlashBag()->add(
                        'success',
                        'The page has been added'
                    );

                    return $this->redirect(
                        $this->generateUrl('pool_linkmotor_pages_view', array('id' => $page->getId()))
                    );
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/page/{id}/view/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_view")
     * @Route("/{_locale}/app/page/{id}/edit/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function viewAction(Request $request, Page $page)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $page->getProject()) {
            $project = $page->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $formOptions = array();
        $isInline = false;
        $previous = 0;
        if ($request->getMethod() == 'POST' && $request->request->get('inline')) {
            $isInline = true;
            $previous = $request->request->get('previous');
            $formOptions = array('csrf_protection' => false);
        }

        $loggedInUserMayEdit = $this->loggedInUserMayEdit($page);
        $pageEditType = new PageEditType();
        $pageEditType->setPage($page);
        $pageEditType->setCurrentStatus($page->getStatus());
        $pageEditType->setLoggedInUserMayEdit($loggedInUserMayEdit);
        $form = $this->createForm($pageEditType, $page, $formOptions);

        if ($request->getMethod() == 'POST') {
            $oldStatus = $page->getStatus();
            $oldAssignedUser = $page->getAssignedTo();

            $form->submit($request);
            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();

                if ($isInline) {
                    $page->setAssignedTo($oldAssignedUser);
                }

                if ($oldStatus != $page->getStatus() || $oldAssignedUser != $page->getAssignedTo()) {
                    $page->setLastModifiedAt(new \DateTime());
                }

                $em->persist($page);
                $em->flush($page);

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                $redirectPath = 'pool_linkmotor_pages_view';
                if ($page->getStatus()->isNotRelevant() && $oldStatus != $page->getStatus()) {
                    $redirectPath = 'pool_linkmotor_pages_after_not_relevant';
                }

                if (!$isInline || $redirectPath != 'pool_linkmotor_pages_view') {
                    return $this->redirect($this->generateUrl($redirectPath, array('id' => $page->getId())));
                }
            }

            if ($isInline) {
                $errors = $form->getErrors();
                if ($errors) {
                    foreach ($errors as $error) {
                        $this->get('session')->getFlashBag()->add(
                            'danger',
                            $error->getMessage()
                        );
                    }
                }

                if ($previous != '') {
                    $previous = "#prospect-{$previous}";
                }
                $redirectUrl = $request->headers->get('referer');
                $pageNum = 1;
                if (preg_match('/page=(\d)/i', $redirectUrl, $match)) {
                    $pageNum = $match[1];
                }
                if ($pageNum > 1) {
                    $redirectUrl = str_replace("page={$pageNum}", 'page=1', $redirectUrl);
                    $previous = '';
                }

                return $this->redirect($redirectUrl . $previous);
            }
        }

        return array(
            'form' => $form->createView(),
            'page' => $page,
            'project' => $project,
            'loggedInUserMayEdit' => $loggedInUserMayEdit
        );
    }

    /**
     * @Route("/{_locale}/app/page/{id}/delete/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_delete")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function deleteAction(Request $request, Page $page)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $project = $this->getSelectedProject();
        if (!$project || $project != $page->getProject()) {
            $project = $page->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            $em->remove($page);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The page has been deleted'
            );

            $type = $this->getUser()->getOptionsPagesType();
            return $this->redirect($this->generateUrl('pool_linkmotor_pages_index', array('type' => $type)));

        }

        return array('page' => $page, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/page/{id}/changelog/", defaults={"_locale" = "en"}, name="pool_linkmotor_pages_changelog")
     * @Method("GET")
     * @Template()
     */
    public function changelogAction(Page $page)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $page->getProject()) {
            $project = $page->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $logEntries = $this->getDoctrine()
            ->getRepository('Gedmo\Loggable\Entity\LogEntry')
            ->getLogEntries($page);

        $paginator  = $this->get('knp_paginator');
        $logEntries = $paginator->paginate(
            $logEntries,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('logEntries' => $logEntries, 'page' => $page, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/page/{id}/after-not-relevant/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_pages_after_not_relevant")
     * @Method("GET")
     * @Template()
     */
    public function afterNotRelevantAction(Page $page)
    {
        $domain = $page->getSubdomain()->getDomain()->getName();

        $competitorAddType = new CompetitorAddType();
        $competitorAddType->setDomainName($domain);
        $formCompetitor = $this->createForm($competitorAddType);

        $blacklistAddType = new BlacklistAddType();
        $blacklistAddType->setDomainName($domain);
        $formBlacklist = $this->createForm($blacklistAddType);

        return array(
            'formCompetitor' => $formCompetitor->createView(),
            'formBlacklist' => $formBlacklist->createView(),
            'page' => $page
        );
    }

    /**
     * @todo Zusätzlich als Ajax-Request realisieren
     *
     * @Route("/{_locale}/app/page/{id}/refresh-authority/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_pages_refresh_authority")
     * @Method("GET")
     * @Template()
     */
    public function refreshAuthorityAction(Page $page)
    {
        $authority = $this->get('seoservices')->getPageAuthority($page->getFull());
        if ($authority !== '') {
            $em = $this->getDoctrine()->getManager();
            $page->setAuthority($authority);
            $em->persist($page);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pool_linkmotor_pages_view', array('id' => $page->getId())));
    }

    /**
     * @todo Zusätzlich als Ajax-Request realisieren
     *
     * @Route("/{_locale}/app/page/{id}/refresh-domain-authority/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_pages_refresh_domain_authority")
     * @Method("GET")
     * @Template()
     */
    public function refreshDomainAuthorityAction(Page $page)
    {
        $authority = $this->get('seoservices')->getDomainAuthority($page->getFull());
        if ($authority !== '') {
            $em = $this->getDoctrine()->getManager();
            $domain = $page->getSubdomain()->getDomain();
            $domain->setAuthority($authority);
            $em->persist($domain);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pool_linkmotor_pages_view', array('id' => $page->getId())));
    }

    /**
     * @Template()
     */
    public function filterAction($withUser, $type)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn nur ein Projekt vorhanden ist, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $domains = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Domain')->getForProject($project);
        $vendors = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Vendor')->getForProject($project);
        $users = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllNonSupportUsers();

        return array(
            'type' => $type,
            'domains' => $domains,
            'vendors' => $vendors,
            'users' => $users,
            'filter' => $this->getFilter($type),
            'withUser' => $withUser
        );
    }
}
