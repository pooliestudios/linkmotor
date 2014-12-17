<?php

namespace Pool\LinkmotorBundle\Controller;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Subdomain;
use Pool\LinkmotorBundle\Form\Type\BacklinkAddType;
use Pool\LinkmotorBundle\Form\Type\BacklinkBulkActionsType;
use Pool\LinkmotorBundle\Form\Type\BacklinkImportType;
use Pool\LinkmotorBundle\Form\Type\BacklinkType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BacklinkController extends BaseController
{
    /**
     * @Route("/{_locale}/app/backlinks/{type}/", defaults={"_locale" = "en", "type" = "my-alerts"},
     *        name="pool_linkmotor_backlinks_index")
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
            return $this->redirect($this->generateUrl('pool_linkmotor_backlinks_index', array('type' => $type)));
        }

        $sortRedirect = $this->needsSortRedirect(
            $project->getId(),
            'backlinks',
            $type,
            $request,
            $this->generateUrl('pool_linkmotor_backlinks_index', array('type' => $type))
        );
        if ($sortRedirect) {
            return $sortRedirect;
        }

        $user = null;
        if ($type != 'all') {
            $user = $this->getUser();
        }
        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->getQueryForBacklinkIndex($project, $user, $this->getFilter($type));
        $paginator  = $this->get('knp_paginator');
        $backlinks = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        $bulkData = array('bulkReturnToType' => $type);
        $bulkActionsType = new BacklinkBulkActionsType();
        $users = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        $bulkActionsType->setUsers($users);
        $bulkActionsType->setIsAdmin($this->get('security.context')->isGranted('ROLE_ADMIN'));
        $bulkActionsForm = $this->createForm($bulkActionsType, $bulkData);

        return array(
            'type' => $type, 'backlinks' => $backlinks, 'bulkActionsForm' => $bulkActionsForm->createView()
        );
    }

    /**
     * @Route("/{_locale}/app/bulk-actions/backlinks/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_bulk_action")
     * @Method("POST")
     * @Template()
     */
    public function bulkAction(Request $request)
    {
        $bulkActionsType = new BacklinkBulkActionsType();
        $users = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getAllActiveNonSupportUsers();
        $bulkActionsType->setUsers($users);
        $form = $this->createForm($bulkActionsType);

        $form->submit($request);
        $backlinkIds = explode(',', $form['bulkItems']->getData());
        $action = explode('-', $form['bulkAction']->getData());
        if ($form['bulkAction']->getData() == null) {
            // Aus mir nicht bekanntem Grund komme ich mit der anderen Methode einfach nicht an delete-delete
            $action = $request->request->get('backlink_bulk_actions');
            $action = explode('-', $action['bulkAction']);
        }

        $em = $this->getDoctrine()->getManager();
        switch ($action[0]) {
            case 'status':
                $user = $this->getUser();
                foreach ($backlinkIds as $backlinkId) {
                    $backlink = $em->getRepository('PoolLinkmotorBundle:Backlink')->find($backlinkId);
                    if ($backlink->getIsOfflineMayBeChangedByUser($user)) {
                        $backlink->setIsOffline($action[1] == 2 ? true : false);
                        $em->persist($backlink);
                        $em->flush();
                    }
                }
                break;
            case 'delete':
                if ($this->get('security.context')->isGranted('ROLE_ADMIN')) {
                    foreach ($backlinkIds as $backlinkId) {
                        $backlink = $em->getRepository('PoolLinkmotorBundle:Backlink')->find($backlinkId);

                        // @todo Besser in einen Listener packen? Wird auch in BulkAction gemacht
                        $page = $backlink->getPage();
                        $page->setStatus($em->getRepository('PoolLinkmotorBundle:Status')->find(7)); // Offline
                        $page->getBacklinks()->removeElement($backlink);
                        $em->persist($page);

                        $em->remove($backlink);
                        $em->flush();
                    }
                }
                break;
            case 'user':
                foreach ($backlinkIds as $backlinkId) {
                    $backlink = $em->getRepository('PoolLinkmotorBundle:Backlink')->find($backlinkId);
                    $user = $em->getRepository('PoolLinkmotorBundle:User')->find($action[1]);
                    $backlink->setAssignedTo($user);
                    $em->persist($backlink);
                    $em->flush();
                }
                break;
            case 'hideuntil':
                if ($action[1] == 1) {
                    $hideUntil = new \DateTime('7 days');
                } elseif ($action[1] == 2) {
                    $hideUntil = new \DateTime('1 month');
                } elseif ($action[1] == 3) {
                    $hideUntil = new \DateTime('3 month');
                }
                foreach ($backlinkIds as $backlinkId) {
                    $alerts = $em->getRepository('PoolLinkmotorBundle:Alert')->findByBacklink($backlinkId);
                    foreach ($alerts as $alert) {
                        $alert->setHideUntil($hideUntil);
                        $em->persist($alert);
                        $em->flush();
                    }
                }
                break;
        }

        $this->get('session')->getFlashBag()->add(
            'success',
            'The bulk action has been executed'
        );

        $type = $form['bulkReturnToType']->getData();
        return $this->redirect($this->generateUrl('pool_linkmotor_backlinks_index', array('type' => $type)));
    }

    /**
     * @Route("/{_locale}/app/import/backlinks/", defaults={"_locale" = "en"}, name="pool_linkmotor_backlinks_import")
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

        $form = $this->createForm(new BacklinkImportType());

        if ($request->getMethod() == 'POST') {
            $form->submit($request);
            $uploadedFile = $form['file']->getData();
            $isValid = true;
            if ($uploadedFile == null) {
                $isValid = false;
                $form->addError(new FormError('You need to specify a file to upload'));
            }
            if ($isValid) {
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
                $em = $this->getDoctrine()->getManager();
                while (($data = fgetcsv($handle, 1000, $delimiter, $enclosure)) !== false) {
                    if (count($data) != 6) {
                        $numInvalidRows++;
                        $invalidRows[] = $data;
                        continue;
                    }
                    $data[3] = strtolower($data[3]);
                    $data[4] = strtolower($data[4]);
                    $data[5] = strtolower($data[5]);
                    $encodingOk = true;
                    foreach ($data as $item) {
                        if (!mb_check_encoding($item, 'UTF-8')) {
                            $encodingOk = false;
                            break;
                        }
                    }
                    if (!$encodingOk) {
                        $numInvalidRows++;
                        $invalidRows[] = $data;
                        continue;
                    }

                    $user = $userRepository->getUserByName($data[5]);
                    if (!$user || strtolower($data[5]) == 'support@linkmotor.de') {
                        $data[6] = false;
                        $data[7] = 'Unknown user';
                        $log[] = $data;
                        continue;
                    }
                    $page = $pageCreator->addPage($project, $data[0], $user);
                    if (!$page) {
                        $data[6] = false;
                        $data[7] = 'Domain is on blacklist or is competitor';
                        $log[] = $data;
                        continue;
                    }
                    $backlink = new Backlink();
                    $backlink->setProjectAndApplyDefaultValues($project);
                    $backlink->setAssignedTo($user);
                    $backlink->setUrl($data[1]);
                    $backlink->setPage($page);
                    $backlink->setAnchor($data[2]);
                    if ($data[3] == 'image') {
                        $backlink->setType('i');
                    } else {
                        $backlink->setType('t');
                    }
                    if ($data[4] == 'follow') {
                        $backlink->setFollow(true);
                    } else {
                        $backlink->setFollow(false);
                    }

                    if (!$backlink->urlIsValid()) {
                        $data[6] = false;
                        $data[7] = 'The url must start with http://, https:// or //';
                        $log[] = $data;
                        continue;
                    }

                    $domainMatchingError = $backlink->checkProjectDomainOrSubdomain();
                    if ($domainMatchingError) {
                        $data[6] = false;
                        $data[7] = $domainMatchingError;
                        $log[] = $data;
                        continue;
                    }

                    $sameBacklink = $em->getRepository('PoolLinkmotorBundle:Backlink')->findSame($backlink);
                    if ($sameBacklink) {
                        $data[6] = false;
                        $data[7] = 'Backlink already in project';
                        $log[] = $data;
                        continue;
                    } else {
                        $page->setStatus($em->getRepository('PoolLinkmotorBundle:Status')->find(6));

                        $em->persist($page);
                        $em->persist($backlink);
                        $em->flush();
                    }
                    $data[5] = $user;
                    $data[6] = true;
                    $log[] = $data;
                }
                ini_set('auto_detect_line_endings', $oldAutoDetectLineEndings);

                if ($numInvalidRows > 0) {
                    $form->addError(new FormError($numInvalidRows . ' invalid rows in import file'));
                } elseif (!$log) {
                    $form->addError(new FormError('Not a valid CSV file'));
                }
                if ($isValid) {
                    return array('log' => $log, 'invalidRows' => $invalidRows, 'form' => $form->createView());
                }
            }
        }

        return array('form' => $form->createView());
    }

    /**
     * @Route("/{_locale}/app/backlinks/{type}/export/", defaults={"_locale" = "en", "type" = "my"},
     *        name="pool_linkmotor_backlinks_export")
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
        $backlinks = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->getQueryForBacklinkIndex($project, $user, $filter)
            ->getQuery()
            ->getResult();

        $content = $this->render(
            'PoolLinkmotorBundle:Backlink:list.csv.twig',
            array('filter' => $filter, 'backlinks' => $backlinks)
        );

        $filename = 'backlinks_' . date('Ymd_His');

        $response = new Response($content->getContent());
        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="' . $filename .'.csv"');

        return $response;
    }

    /**
     * @Route("/{_locale}/app/subdomains/{id}/backlinks/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_subdomain")
     * @Method("GET")
     * @Template()
     */
    public function subdomainAction(Subdomain $subdomain)
    {
        $project = $this->getSelectedProject();
        if (!$project) {
            // @todo Wenn Subdomain nur in einem Projekt benutzt wird, dieses sofort auswählen
            return $this->noProjectSelectedRedirect();
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $query = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->getQueryForBacklinkIndex($project, null, array('subdomain' => $subdomain->getId()));

        $paginator  = $this->get('knp_paginator');
        $backlinks = $paginator->paginate(
            $query,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('subdomain' => $subdomain, 'backlinks' => $backlinks, 'type' => 'subdomain');
    }

    /**
     * @Route("/{_locale}/app/backlink/add/", defaults={"_locale" = "en"}, name="pool_linkmotor_backlinks_add")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function addAction(Request $request)
    {
        if ($this->get('linkmotor.limits')->backlinksLimitReached()) {
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

        $backlink = new Backlink();
        $backlink->setProjectAndApplyDefaultValues($project);
        $backlink->setAssignedTo($this->getUser());
        $form = $this->createForm(new BacklinkAddType(), $backlink);

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();
            $form->submit($request);

            $pageUrl = $form['pageUrl']->getData();
            if (!$pageUrl) {
                $form->addError(new FormError('You need to specify a page url'));
            }
            if ($pageUrl && $form->isValid()) {
                $pageCreator = $this->get('page_creator');
                $page = $pageCreator->checkIfPageExists($project, $pageUrl);
                if (!$page) {
                    $page = $pageCreator->addPage($project, $pageUrl, $this->getUser());
                    if (!$page) {
                        $errorMessage = $this->get('translator')
                            ->trans('Domain is on blacklist or is competitor', array(), 'validators');
                        $form->addError(new FormError($errorMessage));
                    }
                }

                if ($page) {
                    $backlink->setPage($page);

                    $sameBacklink = $em->getRepository('PoolLinkmotorBundle:Backlink')->findSame($backlink);
                    if ($sameBacklink) {
                        $errorMessage = $this->get('translator')
                            ->trans('The backlink already exists', array(), 'validators');
                        $form->addError(new FormError($errorMessage));
                    } else {
                        $page->setStatus($em->getRepository('PoolLinkmotorBundle:Status')->find(6));

                        $em->persist($page);
                        $em->persist($backlink);
                        $em->flush();

                        $this->get('crawler')->crawlBacklink($backlink);

                        $this->get('session')->getFlashBag()->add(
                            'success',
                            'The backlink has been added'
                        );

                        return $this->redirect(
                            $this->generateUrl('pool_linkmotor_backlinks_view', array('id' => $backlink->getId()))
                        );
                    }
                }
            }
        }

        return array(
            'form' => $form->createView(),
            'project' => $project,
            'backlink' => $backlink
        );
    }

    /**
     * @Route("/{_locale}/app/page/{id}/backlinks/add/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_add_for_page")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function addForPageAction(Request $request, Page $page)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $page->getProject()) {
            $project = $page->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $loggedInUserMayEdit = $this->loggedInUserMayEdit($page);

        $backlink = new Backlink();
        $backlink->setProjectAndApplyDefaultValues($project);
        $backlink->setPage($page);
        $backlinkType = new BacklinkType();
        $backlinkType->setLoggedInUserMayEdit($loggedInUserMayEdit);
        $form = $this->createForm($backlinkType, $backlink);

        if ($loggedInUserMayEdit && $request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();
            $form->submit($request);

            $isValid = true;
            $sameBacklink = $em->getRepository('PoolLinkmotorBundle:Backlink')->findSame($backlink);
            if ($sameBacklink) {
                $isValid = false;
                $errorMessage = $this->get('translator')
                    ->trans('The backlink already exists', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }
            if ($form->isValid() && $isValid) {

                $backlink->setAssignedTo($this->getUser());
                $page->setStatus($em->getRepository('PoolLinkmotorBundle:Status')->find(6));

                $em->persist($page);
                $em->persist($backlink);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'The backlink has been added'
                );

                return $this->redirect(
                    $this->generateUrl('pool_linkmotor_backlinks_view', array('id' => $backlink->getId()))
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'page' => $page,
            'backlink' => $backlink,
            'loggedInUserMayEdit' => $loggedInUserMayEdit
        );
    }

    /**
     * @Route("/{_locale}/app/backlink/{id}/view/", defaults={"_locale" = "en"}, name="pool_linkmotor_backlinks_view")
     * @Method("GET")
     * @Template()
     */
    public function viewAction(Backlink $backlink)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $backlink->getProject()) {
            $project = $backlink->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        return array('backlink' => $backlink, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/backlink/{id}/delete/", defaults={"_locale" = "en"}, name="pool_linkmotor_backlinks_delete")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function deleteAction(Request $request, Backlink $backlink)
    {
        if (!$this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }

        $project = $this->getSelectedProject();
        if (!$project || $project != $backlink->getProject()) {
            $project = $backlink->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        if ($request->getMethod() == 'POST') {
            $em = $this->getDoctrine()->getManager();

            // @todo Besser in einen Listener packen? Wird auch in BulkAction gemacht
            $page = $backlink->getPage();
            $page->setStatus($em->getRepository('PoolLinkmotorBundle:Status')->find(7)); // Offline
            $page->getBacklinks()->removeElement($backlink);
            $em->persist($page);

            $em->remove($backlink);
            $em->flush();

            $this->get('session')->getFlashBag()->add(
                'success',
                'The backlink has been deleted'
            );

            $type = $this->getUser()->getOptionsBacklinksType();
            return $this->redirect($this->generateUrl('pool_linkmotor_backlinks_index', array('type' => $type)));

        }

        return array('backlink' => $backlink, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/backlink/{id}/changelog/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_changelog")
     * @Method("GET")
     * @Template()
     */
    public function changelogAction(Backlink $backlink)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $backlink->getProject()) {
            $project = $backlink->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $logEntries = $this->getDoctrine()
            ->getRepository('Gedmo\Loggable\Entity\LogEntry')
            ->getLogEntries($backlink);

        $paginator  = $this->get('knp_paginator');
        $logEntries = $paginator->paginate(
            $logEntries,
            $this->getRequest()->query->get('page', 1),
            $this->getItemsPerPage()
        );

        return array('logEntries' => $logEntries, 'backlink' => $backlink, 'project' => $project);
    }

    /**
     * @Route("/{_locale}/app/backlink/{id}/edit/", defaults={"_locale" = "en"}, name="pool_linkmotor_backlinks_edit")
     * @Method({"GET", "POST"})
     * @Template()
     */
    public function editAction(Request $request, Backlink $backlink)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $backlink->getProject()) {
            $project = $backlink->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $loggedInUserMayEdit = $this->loggedInUserMayEdit($backlink);

        $backlinkType = new BacklinkType();
        $backlinkType->setLoggedInUserMayEdit($loggedInUserMayEdit);
        $form = $this->createForm($backlinkType, $backlink);

        if ($request->getMethod() == 'POST') {
            $oldBacklinkAssignedTo = $backlink->getAssignedTo();
            $form->submit($request);

            $em = $this->getDoctrine()->getManager();

            $isValid = true;
            $sameBacklink = $em->getRepository('PoolLinkmotorBundle:Backlink')->findSame($backlink);
            if ($sameBacklink) {
                $isValid = false;
                $errorMessage = $this->get('translator')
                    ->trans('The backlink already exists', array(), 'validators');
                $form->addError(new FormError($errorMessage));
            }

            if ($isValid && $form->isValid()) {
                $em->persist($backlink);
                $em->flush();

                $this->get('session')->getFlashBag()->add(
                    'success',
                    'Your changes have been saved'
                );

                if ($oldBacklinkAssignedTo != $backlink->getAssignedTo()) {
                    foreach ($backlink->getAlerts() as $alert) {
                        $alert->setUser($backlink->getAssignedTo());
                        $em->persist($alert);
                    }
                    $em->flush();
                }

                return $this->redirect(
                    $this->generateUrl('pool_linkmotor_backlinks_view', array('id' => $backlink->getId()))
                );
            }
        }

        return array(
            'form' => $form->createView(),
            'backlink' => $backlink,
            'loggedInUserMayEdit' => $loggedInUserMayEdit
        );
    }

    /**
     * @Route("/{_locale}/app/backlink/{id}/crawl-log/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_crawl_log")
     * @Method("GET")
     * @Template()
     */
    public function crawlLogAction(Backlink $backlink)
    {
        $project = $this->getSelectedProject();
        if (!$project || $project != $backlink->getProject()) {
            $project = $backlink->getProject();
            $this->setSelectedProject($project);
        }
        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        return array('backlink' => $backlink);
    }

    /**
     * @Route("/{_locale}/app/backlink/{id}/crawl/", defaults={"_locale" = "en"}, name="pool_linkmotor_backlinks_crawl")
     * @Method("GET")
     * @Template()
     */
    public function crawlAction(Backlink $backlink)
    {
        $results = $this->get('crawler')->crawlBacklink($backlink);

        if (count($results) > 1) {
            $this->get('session')->getFlashBag()->add(
                'warning',
                'More than one link was found!'
            );
        }

        $resultMessage = array();
        $numProcessed = 0;
        foreach ($results as $result) {
            if (count($results) > 1 && $numProcessed > 0) {
                $resultMessage[] = "<hr>";
            }

            if ($numProcessed == 0) {
                $resultMessage[] = "<strong>Statuscode</strong>: {$result['urlInfo']['httpStatusCode']}";
                $resultMessage[] = "<strong>Meta-Index</strong>: "
                                 . ($result['urlInfo']['metaIndex'] ? 'INDEX' : 'NOINDEX');
                $resultMessage[] = "<strong>Meta-Follow</strong>: "
                    . ($result['urlInfo']['metaFollow'] ? 'FOLLOW' : 'NOFOLLOW');
                $resultMessage[] = "<strong>X-Robots-Index</strong>: "
                    . ($result['urlInfo']['xRobotsIndex'] ? 'INDEX' : 'NOINDEX');
                $resultMessage[] = "<strong>X-Robots-Follow</strong>: "
                    . ($result['urlInfo']['xRobotsFollow'] ? 'FOLLOW' : 'NOFOLLOW');
                $resultMessage[] = "<strong>Robots.txt (Google)</strong>: "
                    . ($result['urlInfo']['robotsGoogle'] ? 'ALLOW' : 'DISALLOW');

                $resultMessage[] = "<hr>";
            }
            if ($result['crawlType'] == 'dom') {
                $type = $result['type'] == 'i' ? 'Image' : 'Text';
                $follow = $result['follow'] ? 'Yes' : 'No';
                $resultMessage[] = "<strong>Anchor</strong>: {$result['anchor']}";
                $resultMessage[] = "<strong>Type</strong>: {$type}";
                $resultMessage[] = "<strong>Follow</strong>: {$follow}";
                $resultMessage[] = "<strong>XPath</strong>: {$result['xpath']}";
            } else {
                $found = $result['found'] ? 'Yes' : 'No';
                $resultMessage[] = "<strong>Found</strong>: {$found}";
            }

            $numProcessed++;
        }

        if ($resultMessage) {
            $this->get('session')->getFlashBag()->add(
                'info',
                implode("<br>", $resultMessage)
            );
        } else {
            $this->get('session')->getFlashBag()->add('error', 'Crawl failed!');
        }

        return $this->redirect(
            $this->generateUrl('pool_linkmotor_backlinks_crawl_log', array('id' => $backlink->getId()))
        );
    }

    /**
     * @todo Zusätzlich als Ajax-Request realisieren
     *
     * @Route("/{_locale}/app/backlink/{id}/refresh-xpath/", defaults={"_locale" = "en"},
     *        name="pool_linkmotor_backlinks_refresh_xpath")
     * @Template()
     */
    public function refreshXPathAction(Backlink $backlink)
    {
        $xPath = $this->get('crawler')->getBacklinkXPath($backlink);
        if ($xPath !== '') {
            $em = $this->getDoctrine()->getManager();
            $backlink->setXPath($xPath);
            $em->persist($backlink);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('pool_linkmotor_backlinks_view', array('id' => $backlink->getId())));
    }

    /**
     * @Template()
     */
    public function filterAction($withUser, $type)
    {
        return $this->getFilterData($withUser, $type);
    }
}
