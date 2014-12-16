<?php
namespace Pool\LinkmotorBundle\Command;

use Doctrine\ORM\EntityManager;
use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\Import;
use Pool\LinkmotorBundle\Entity\Vendor;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Pool\LinkmotorBundle\Service\PageCreator;
use Pool\LinkmotorBundle\Service\Crawler;

class ProcessImportsCommand extends ContainerAwareCommand
{
    const MAX_ROWS_IN_IMPORT_FILE = 10000;

    /**
     * @var EntityManager
     */
    protected $em;

    /**
     * @var PageCreator
     */
    protected $pageCreator;

    /**
     * @var Crawler
     */
    protected $crawler;

    protected $fp;
    protected $importData;

    protected function configure()
    {
        $this->setName('seo:imports:process')
            ->setDescription('Processes unfinished imports (SEO-Tool)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->em = $this->getContainer()->get('doctrine')->getManager();
        $importsToProcess = $this->em->getRepository('PoolLinkmotorBundle:Import')
            ->findByStep(0);

        foreach ($importsToProcess as $import) {
            $this->processStep0($import);
        }

        $importsToProcess = $this->em->getRepository('PoolLinkmotorBundle:Import')
            ->findByStep(3);

        foreach ($importsToProcess as $import) {
            $this->processStep3($import);
        }
    }

    protected function processStep0($import)
    {
        $import->setStep(1);
        $import->setProgress(1);
        $this->em->persist($import);
        $this->em->flush();

        $data = $this->analyzeLinkbirdImport($import);
        if (isset($data['error'])) {
            $import->setProgress(0);
            $import->setStep(99);
            $import->setData(array('msg' => $data['error']));
        } elseif (isset($data['validRows']) && $data['validRows'] == 0) {
            $import->setProgress(0);
            $import->setStep(99);
            $import->setData(array('msg' => 'This does not seem to be a Linkbird export file'));
        } elseif ($data['projects']) {
            $import->setData($data);
            $import->setStep(2);
            $import->setProgress(0);
        } else {
            $import->setProgress(0);
            $import->setStep(99);
            $import->setData(array('msg' => 'No projects found'));
        }

        $this->em->persist($import);
        $this->em->flush();
    }

    protected function processStep3($import)
    {
        $importId = $import->getId();
        $this->importData = $import->getData();
        $connection = $this->em->getConnection();
        $connection->update('imports', array('step' => 4, 'progress' => 1), array('id' => $importId));

        $this->pageCreator = $this->getContainer()->get('page_creator');
        $this->crawler = $this->getContainer()->get('crawler');

        $transcriptFilename = $import->getUploadRootDir() . '/' . $import->getTranscriptFilename();
        $this->fp = fopen($transcriptFilename, 'w');
        fputcsv($this->fp, array('url', 'target-url', 'anchor', 'type', 'follow', 'user', 'status'), ';', '"');

        $numRowsImported = 0;

        $filename = $import->getUploadRootDir() . '/' . $import->getImportFilename();
        $objReader = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $objReader->setReadFilter(new ExcelReadFilterLinkbird());
        $objPHPExcel = $objReader->load($filename);
        $worksheet = $objPHPExcel->getActiveSheet();
        $numRows = $worksheet->getHighestDataRow();

        foreach ($worksheet->getRowIterator() as $row) {
            $currentRow = $row->getRowIndex();
            if ($currentRow == 1) {
                continue;
            }
            if ($currentRow % 50 == 0) {
                $progress = round(($currentRow/$numRows) * 100.0);
                $connection->update('imports', array('progress' => $progress), array('id' => $importId));
            }

            $this->processOneRow($import->getId(), $row);

            $numRowsImported++;
        }

        fclose($this->fp);

        $this->importData['numRowsImported'] = $numRowsImported;

        $import = $this->em->getRepository('PoolLinkmotorBundle:Import')->find($importId);
        $import->setData($this->importData);
        $import->setStep(5);
        $import->setProgress(0);

        $this->em->persist($import);
        $this->em->flush();
    }

    protected function processOneRow($importId, $row)
    {
        $this->em->clear();
        $linkbirdProject = $this->importData['project'];
        $userMappingEmpty = $this->importData['userMappingEmpty'];
        $userMapping = $this->importData['userMapping'];

        $import = $this->em->getRepository('PoolLinkmotorBundle:Import')->find($importId);

        $userMappingEmpty = $this->em->getRepository('PoolLinkmotorBundle:User')->find($userMappingEmpty);
        foreach ($userMapping as $idx => $userId) {
            $userMapping[$idx] = $this->em->getRepository('PoolLinkmotorBundle:User')->find($userId);
        }

        $rowData = array();
        foreach ($row->getCellIterator() as $cell) {
            $rowData[] = $cell->getValue();
        }

        if ($rowData[5] != $linkbirdProject) {
            // Nicht das ausgewählte Projekt
            return;
        }

        $data = array('', '', '', '', '', '', '');
        $data[0] = strtolower($rowData[0]); // url
        $data[2] = $rowData[3];

        $user = $userMappingEmpty;

        foreach ($this->importData['users'] as $idx => $username) {
            if ($username == $rowData[1]) {
                $user = $userMapping[$idx];
            }
        }

        $data[5] = $user->getDisplayName();

        $backlinksData = $this->crawler->findBacklinksForProjectOnUrl($import->getProject(), $data[0]);
        $urlInfo = $this->crawler->getUrlInfo();

        foreach ($backlinksData as $backlinkNum => $backlinkData) {
            if ($backlinkNum > 0) {
                // Nur beim ersten Link sollen diese Werte gespeichert werden
                $rowData[7] = ''; // Informationen zu den Kosten
            }

            $data[1] = $backlinkData['url'];
            $data[2] = $backlinkData['anchor'];

            $backlink = new Backlink();
            $backlink->setProjectAndApplyDefaultValues($import->getProject());
            $backlink->setUrl($data[1]);
            $backlink->setUrlOk(true);
            $backlink->setAnchor($data[2]);
            $backlink->setAnchorLastCrawl($backlink->getAnchor());
            $backlink->setType($backlinkData['type']);
            $backlink->setTypeLastCrawl($backlink->getType());
            $backlink->setXPath($backlinkData['xpath']);
            $backlink->setXPathLastCrawl($backlink->getXPath());
            $backlink->setStatusCode($urlInfo['httpStatusCode']);
            $backlink->setStatusCodeLastCrawl($backlink->getStatusCode());
            $backlink->setMetaIndex($urlInfo['metaIndex']);
            $backlink->setMetaIndexLastCrawl($backlink->getMetaIndex());
            $backlink->setMetaFollow($urlInfo['metaFollow']);
            $backlink->setMetaFollowLastCrawl($backlink->getMetaFollow());
            $backlink->setRobotsGoogle($urlInfo['robotsGoogle']);
            $backlink->setRobotsGoogleLastCrawl($backlink->getRobotsGoogle());
            $backlink->setXRobotsIndex($urlInfo['xRobotsIndex']);
            $backlink->setXRobotsIndexLastCrawl($backlink->getXRobotsIndex());
            $backlink->setXRobotsFollow($urlInfo['xRobotsFollow']);
            $backlink->setXRobotsFollowLastCrawl($backlink->getXRobotsFollow());
            $backlink->setLastCrawledAt(new \DateTime());

            $data[3] = $backlink->getTypeName();
            $rowData[11] = $backlinkData['follow'] ? 'follow' : 'nofollow';

            // Page anlegen
            $page = $this->pageCreator->addPage($import->getProject(), $data[0], $user);
            if (!$page) {
                $data[6] = 'Domain is on blacklist or is competitor';
                fputcsv($this->fp, $data, ';', '"');
                return;
            }
            if ($rowData[6]) {
                $domain = $page->getSubdomain()->getDomain();
                $vendor = $domain->getVendor();
                if (!$vendor) {
                    $vendorToImport = $rowData[6];
                    if (strpos($vendorToImport, '@') === false) {
                        // Keine E-Mail-Adresse vorhanden
                        $email = md5($vendorToImport) . '@linkbirdimport.local';
                        $name = $vendorToImport;
                    } else {
                        $email = $vendorToImport;
                        $name = '';
                    }
                    $vendor = $this->em->getRepository('PoolLinkmotorBundle:Vendor')->findByEmail($email);
                    if (!$vendor) {
                        $vendor = new Vendor();
                        $vendor->setName($name);
                        $vendor->setEmail($email);
                    } else {
                        $vendor = $vendor[0];
                    }
                    $domain->setVendor($vendor);
                    $vendor->addDomain($domain);
                    $this->em->persist($domain);
                    $this->em->persist($vendor);
                    $this->em->flush();
                }
            }

            $backlink->setAssignedTo($user);
            $backlink->setPage($page);
            $backlink->setCreatedAt(new \DateTime($rowData[2]));
            if ($rowData[7]) {
                $backlink->setPrice($rowData[7]);
                if (strtolower($rowData[8]) == 'yes') {
                    $backlink->setCostType(2);
                } else {
                    $backlink->setCostType(1);
                }
                if ($rowData[9]) {
                    $backlink->setCostNote('Link rent expires on: ' . $rowData[9]);
                }
            }

            if (strtolower($rowData[11]) == 'nofollow') {
                $data[4] = 'nofollow';
                $backlink->setFollow(false);
                $backlink->setFollowLastCrawl(false);
            } else {
                $data[4] = 'follow';
                $backlink->setFollow(true);
                $backlink->setFollowLastCrawl(true);
            }

            if (!$backlink->urlIsValid()) {
                $data[6] = 'The url must start with http://, https:// or //';
                fputcsv($this->fp, $data, ';', '"');
                return;
            }

            $sameBacklink = $this->em->getRepository('PoolLinkmotorBundle:Backlink')->findSame($backlink);
            if ($sameBacklink) {
                $data[6] = 'Backlink already in project';
                fputcsv($this->fp, $data, ';', '"');
                return;
            } else {
                $page->setStatus($this->em->getRepository('PoolLinkmotorBundle:Status')->find(6));

                $this->em->persist($page);
                $this->em->persist($backlink);
                $this->em->flush();
            }
            $data[5] = $user->getDisplayName();
            $data[6] = 'OK';

            fputcsv($this->fp, $data, ';', '"');
        }
    }

    /**
     * @todo Wird auch in Backlink benutzt
     * @param string $url
     * @return bool
     */
    public function urlIsValid($url)
    {
        return stripos($url, 'http://') === 0
               || stripos($url, 'https://') === 0
               || stripos($url, '//') === 0;
    }

    // @todo Die Importe sollten in einen Service und außerdem so gestaltet werden, dass
    // @todo neue Importformate leicht hinzuzufügen sind.
    protected function analyzeLinkbirdImport($import)
    {
        $filename = $import->getUploadRootDir() . '/' . $import->getImportFilename();

        $objReader = \PHPExcel_IOFactory::createReader(\PHPExcel_IOFactory::identify($filename));
        $objReader->setReadFilter(new ExcelReadFilterLinkbird());
        $objPHPExcel = $objReader->load($filename);

        $users = array();
        $projects = array();
        $worksheet = $objPHPExcel->getActiveSheet();
        $numRows = $worksheet->getHighestDataRow();
        if ($numRows > self::MAX_ROWS_IN_IMPORT_FILE) {
            return array('error' => 'Too many rows in import file (max. ' . self::MAX_ROWS_IN_IMPORT_FILE . ' allowed).');
        }
        $validRows = 0;
        foreach ($worksheet->getRowIterator() as $row) {
            $currentRow = $row->getRowIndex();
            if ($currentRow == 1) {
                continue;
            }
            if ($currentRow % 50 == 0) {
                $progress = round(($currentRow/$numRows) * 100.0);
                $import->setProgress($progress);
                $this->em->persist($import);
                $this->em->flush();
            }
            $rowData = array();
            foreach ($row->getCellIterator() as $cell) {
                $rowData[] = $cell->getValue();
            }

            if (!in_array($rowData[1], $users)) {
                $users[] = $rowData[1];
            }
            if (!in_array($rowData[5], $projects)) {
                $projects[] = $rowData[5];
            }

            if (!$this->urlIsValid($rowData[0]) || !$this->urlIsValid($rowData[4])) {
                continue;
            }
            $validRows++;
        }

        return array('users' => $users, 'projects' => $projects, 'validRows' => $validRows);
    }
}
