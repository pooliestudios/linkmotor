<?php

namespace Pool\LinkmotorBundle\Service;

use Pool\LinkmotorBundle\Entity\Backlink;
use Pool\LinkmotorBundle\Entity\CrawlLog;
use Pool\LinkmotorBundle\Entity\Project;
use Symfony\Component\DomCrawler\Crawler as DomCrawler;

class Crawler
{
    #private $userAgent = 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)';
    private $userAgent = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Win64; x64; Trident/6.0; MASEJS)';
    private $urlInfo;

    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    /**
     * @var RobotsTxt
     */
    private $robotsTxt;

    public function __construct($doctrine = null, RobotsTxt $robotsTxt = null)
    {
        $this->doctrine = $doctrine;
        $this->robotsTxt = $robotsTxt;
        $this->urlInfo = array(
            'url' => '',
            'httpStatusCode' => 0,
            'metaIndex' => 0,
            'metaFollow' => 0,
            'robotsGoogle' => 0,
            'xRobotsIndex' => 0,
            'xRobotsFollow' => 0
        );
    }

    public function getUrlInfo()
    {
        return $this->urlInfo;
    }

    /**
     * @param string $url
     * @return array
     */
    public function checkIfUrlExists($url)
    {
        $result = $this->getContentFromUrl($url);
        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        $returnValue = true;
        if ($httpStatusCode != 200 || !$content) {
            $message = 'URL not found';
            if ($httpStatusCode == 302) {
                $message = 'Found 302 redirect';
            } elseif ($httpStatusCode == 301) {
                $message = 'Found 301 redirect';
            }

            $returnValue = $message;
        }

        return $returnValue;
    }

    public function crawlBacklink(Backlink $backlink)
    {
        if ($backlink->getCrawlType() == 'dom') {
            return $this->crawlDomBacklink($backlink);
        }

        return $this->crawlTextBacklink($backlink);
    }

    /**
     * @param Project $project
     * @param $url
     * @return array
     */
    public function findBacklinksForProjectOnUrl(Project $project, $url)
    {
        $url = $this->convertDomainToAscii($url);

        $result = $this->getContentFromUrl($url);
        $info = $result['info'];
        $this->urlInfo['httpStatusCode'] = isset($info['http_code']) ? $info['http_code'] : 0;
        $this->urlInfo['url'] = isset($info['url']) ? $info['url'] : '';

        $this->urlInfo['robotsGoogle'] = 1;
        if (!$this->robotsTxt->isGoogleBotAllowedForUrl($url)) {
            $this->urlInfo['robotsGoogle'] = 0;
        }

        $content = $result['content'];
        if ($this->urlInfo['httpStatusCode'] != 200 || !$content
            || trim($url, '/') != trim($this->urlInfo['url'], '/')
        ) {
            return array();
        }

        $domCrawler = new DomCrawler($content);
        $linkNodes = $domCrawler->filter('a');
        if ($linkNodes->count() == 0) {
            return array();
        }

        if ($project->getSubdomain()) {
            $regexDomain = str_replace('.', '\.', $project->getSubdomain()->getFull());
        } else {
            $regexDomain = '.*' . str_replace('.', '\.', $project->getDomain()->getName());
        }
        $regexToSearchFor = '/\/\/' . $regexDomain . '.*/i';

        $backlinks = array();
        $numNodes = $linkNodes->count();
        for ($i=0; $i<$numNodes; $i++) {
            $linkNode = $linkNodes->eq($i);

            $backlinkUrl = $linkNode->attr('href');
            if (!preg_match($regexToSearchFor, $backlinkUrl)) {
                continue;
            }

            $linkType = $this->getLinkType($linkNode->html());
            if ($linkType == 't') {
                $anchor = $linkNode->text();
            } else {
                $anchor = $this->getImageAltText($linkNode->html());
            }
            $anchor = str_replace("\n", ' ', $anchor);
            $anchor = str_replace("\r", ' ', $anchor);
            $anchor = str_replace("\t", ' ', $anchor);
            $anchor = preg_replace('!\s+!', ' ', $anchor);
            $anchor = trim($anchor);
            $result = array(
                'crawlType' => 'dom',
                'url' => $backlinkUrl,
                'anchor' => $anchor,
                'follow' => true,
                'type' => $linkType,
                'xpath' => $linkNode->current()->getNodePath()
            );

            $rel = strtolower($linkNode->attr('rel'));
            if ($rel) {
                $rel = explode(' ', $rel);
                if (in_array('nofollow', $rel)) {
                    $result['follow'] = false;
                }
            }

            $backlinks[] = $result;
        }

        return $backlinks;
    }

    public function convertDomainToAscii($url)
    {
        if (strpos($url, 'http') !== 0 && strpos($url, '//') !== 0) {
            $url = 'http://' . $url;
        }
        $parts = parse_url($url);

        if (!$parts) {
            return $url;
        }

        $scheme = isset($parts['scheme']) ? $parts['scheme'] : 'http';
        $host = isset($parts['host']) ? $parts['host'] : '';
        $convertedHost = idn_to_ascii($host);
        $path = isset($parts['path']) ? $parts['path'] : '';
        $query = isset($parts['query']) ? ('?' . $parts['query']) : '';

        return $scheme . '://' . $convertedHost . $path . $query;
    }

    protected function crawlTextBacklink(Backlink $backlink)
    {
        $backlink->setLastCrawledAt(new \DateTime());

        $result = $this->getContentFromUrl($backlink->getPage()->getFull());
        $info = $result['info'];
        $this->urlInfo['url'] = isset($info['url']) ? $info['url'] : '';
        $this->urlInfo['httpStatusCode'] = isset($info['http_code']) ? $info['http_code'] : 0;

        $content = $result['content'];
        if ($this->urlInfo['httpStatusCode'] != 200 || !$content) {
            $content = '';
        }

        $backlink = $this->updateBacklinkUrlInfo($backlink);

        $result = array('crawlType' => 'text', 'urlInfo' => $this->urlInfo);
        if (stripos($content, $backlink->getUrl()) === false) {
            $backlink->setUrlOk(false);
            $result['found'] = false;
        } else {
            $backlink->setUrlOk(true);
            $backlink->setAnchorOk(true);
            $backlink->setTypeOk(true);
            $backlink->setFollowOk(true);
            $backlink->setXPathOk(true);

            $result['found'] = true;
        }

        $em = $this->doctrine->getManager();
        $em->persist($backlink);
        $em->flush($backlink);

        $this->updateCrawlLog($backlink);

        return array($result);
    }

    protected function crawlDomBacklink(Backlink $backlink)
    {
        $isFirstCrawl = $backlink->getLastCrawledAt() == null;

        $results = $this->getBacklinkInfo($backlink);
        $backlink->setLastCrawledAt(new \DateTime());

        $backlink = $this->updateBacklinkUrlInfo($backlink);

        if (!$results) {
            $backlink->setUrlOk(false);
        } else {
            if (count($results) > 1) {
                $result = $this->findBestMatchingResult($results, $backlink);
            } else {
                $result = $results[0];
            }

            $backlink->setUrlOk(true);
            $backlink->setAnchorLastCrawl($result['anchor']);
            $backlink->setTypeLastCrawl($result['type']);
            $backlink->setFollowLastCrawl($result['follow']);
            $backlink->setXPathLastCrawl($result['xpath']);

            if ($isFirstCrawl) {
                // On first crawl, just use the xpath value from that crawl
                $backlink->setXPath($result['xpath']);

                // Also set the anchor to the value from that crawl, if type is 'Image' and
                // no anchor was given.
                if ($backlink->getType() == 'i' && $backlink->getAnchor() == '') {
                    $backlink->setAnchor($result['anchor']);
                }
            }

            // Add urlInfo to first result
            $results[0]['urlInfo'] = $this->urlInfo;
        }

        $em = $this->doctrine->getManager();
        $em->persist($backlink);
        $em->flush($backlink);

        $this->updateCrawlLog($backlink);

        return $results;
    }

    protected function updateMetaInfo($header, $content, $info)
    {
        if ($info['http_code'] != 200) {
            return;
        }

        $this->urlInfo['metaIndex'] = 1;
        $this->urlInfo['metaFollow'] = 1;

        $domCrawler = new DomCrawler($content);
        $metaNodes = $domCrawler->filter('meta');
        $numMetaNodes = $metaNodes->count();
        for ($i=0; $i<$numMetaNodes; $i++) {
            $metaNode = $metaNodes->eq($i);
            if (strtolower($metaNode->attr('name')) == 'robots') {
                $content = strtolower($metaNode->attr('content'));
                $content = str_replace(' ', '', $content);
                $metaValues = explode(',', $content);
                if (in_array('noindex', $metaValues)) {
                    $this->urlInfo['metaIndex'] = 0;
                }
                if (in_array('nofollow', $metaValues)) {
                    $this->urlInfo['metaFollow'] = 0;
                }
            }
        }

        $this->urlInfo['xRobotsIndex'] = 1;
        $this->urlInfo['xRobotsFollow'] = 1;
        $headerArray = explode("\n", $header);
        foreach ($headerArray as $headerLine) {
            if (preg_match('/X-Robots-Tag:(.*)/', $headerLine, $match)) {
                $tagValues = array();
                $value = strtolower(trim($match[1]));
                $value = str_replace(' ', '', $value);
                $botValue = explode(':', $value);
                if (count($botValue) == 2) {
                    if ($botValue[0] == 'googlebot') {
                        $tagValues = explode(',', $botValue[1]);
                    }
                } else {
                    $tagValues = explode(',', $botValue[0]);
                }
                if (in_array('noindex', $tagValues)) {
                    $this->urlInfo['xRobotsIndex'] = 0;
                }
                if (in_array('nofollow', $tagValues)) {
                    $this->urlInfo['xRobotsFollow'] = 0;
                }
            }
        }
    }

    protected function updateBacklinkUrlInfo(Backlink $backlink)
    {
        $this->urlInfo['robotsGoogle'] = 1;
        if (!$this->robotsTxt->isGoogleBotAllowedForPage($backlink->getPage())) {
            $this->urlInfo['robotsGoogle'] = 0;
        }

        $backlink->setStatusCodeLastCrawl($this->urlInfo['httpStatusCode']);
        $backlink->setMetaFollowLastCrawl($this->urlInfo['metaFollow']);
        $backlink->setMetaIndexLastCrawl($this->urlInfo['metaIndex']);
        $backlink->setXRobotsIndexLastCrawl($this->urlInfo['xRobotsIndex']);
        $backlink->setXRobotsFollowLastCrawl($this->urlInfo['xRobotsFollow']);
        $backlink->setRobotsGoogleLastCrawl($this->urlInfo['robotsGoogle']);

        return $backlink;
    }

    public function findBestMatchingResult($results, Backlink $backlink)
    {
        $bestMatch = null;
        $scoreBestMatch = -1;
        foreach ($results as $result) {
            $currentScore = 0;
            if ($backlink->getAnchor() == $result['anchor']) {
                $currentScore += 2;
            }
            if ($backlink->getType() == $result['type']) {
                $currentScore += 2;
            }
            if ($backlink->getFollow() == $result['follow']) {
                $currentScore += 2;
            }
            if (!$backlink->getIgnorePosition() && $backlink->getXPath() == $result['xpath']) {
                $currentScore++;
            }
            if ($currentScore > $scoreBestMatch) {
                $bestMatch = $result;
                $scoreBestMatch = $currentScore;
            }
        }

        return $bestMatch;
    }

    public function getBacklinkXPath(Backlink $backlink)
    {
        $result = $this->getBacklinkInfo($backlink);

        return isset($result[0]['xpath']) ? $result[0]['xpath'] : '';
    }

    private function getContentFromUrl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_COOKIESESSION, true);
        curl_setopt($ch, CURLOPT_COOKIEFILE, '');
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // @todo
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        $headerSize = $info['header_size'];
        $header = substr($response, 0, $headerSize);
        $content = substr($response, $headerSize);
        if (stripos($header, 'Content-Encoding: gzip') !== false) {
            $content = gzinflate(substr($content, 10, -8));
        }

        $this->updateMetaInfo($header, $content, $info);

        return array(
            'header' => $header,
            'content' => $content,
            'info' => $info
        );
    }

    private function updateCrawlLog(Backlink $backlink)
    {
        $newEntryNeeded = false;
        $lastEntry = $this->doctrine->getRepository('PoolLinkmotorBundle:CrawlLog')->getLastEntry($backlink);
        if (!$lastEntry) {
            $newEntryNeeded = true;
        } else {
            $lastEntry = $lastEntry[0];
            if ($lastEntry->getUrlOk() != $backlink->getUrlOk()
                || $lastEntry->getAnchorOk() != $backlink->getAnchorOk()
                || $lastEntry->getTypeOk() != $backlink->getTypeOk()
                || $lastEntry->getXPathOk() != $backlink->getXPathOk()
                || $lastEntry->getStatusCodeOk() != $backlink->getStatusCodeOk()
                || $lastEntry->getMetaIndexOk() != $backlink->getMetaIndexOk()
                || $lastEntry->getMetaFollowOk() != $backlink->getMetaFollowOk()
                || $lastEntry->getXRobotsIndexOk() != $backlink->getXRobotsIndexOk()
                || $lastEntry->getXRobotsFollowOk() != $backlink->getXRobotsFollowOk()
                || $lastEntry->getRobotsGoogleOk() != $backlink->getRobotsGoogleOk()
            ) {
                $newEntryNeeded = true;
            }
        }

        if ($newEntryNeeded) {
            $newEntry = new CrawlLog();
            $newEntry->setBacklink($backlink);
            $newEntry->setProject($backlink->getProject());
            $newEntry->setCrawlType($backlink->getCrawlType());
            $newEntry->setStatusCode($backlink->getStatusCode());
            $newEntry->setStatusCodeOk($backlink->getStatusCodeOk());
            $newEntry->setMetaIndex($backlink->getMetaIndex());
            $newEntry->setMetaIndexOk($backlink->getMetaIndexOk());
            $newEntry->setMetaFollow($backlink->getMetaFollow());
            $newEntry->setMetaFollowOk($backlink->getMetaFollowOk());
            $newEntry->setXRobotsFollow($backlink->getXRobotsFollow());
            $newEntry->setXRobotsFollowOk($backlink->getXRobotsFollowOk());
            $newEntry->setXRobotsIndex($backlink->getXRobotsIndex());
            $newEntry->setXRobotsIndexOk($backlink->getXRobotsIndexOk());
            $newEntry->setRobotsGoogle($backlink->getRobotsGoogle());
            $newEntry->setRobotsGoogleOk($backlink->getRobotsGoogleOk());
            if ($backlink->getCrawlType() == 'dom') {
                $newEntry->setUrlOk($backlink->getUrlOk());
                $newEntry->setAnchor($backlink->getAnchorLastCrawl());
                $newEntry->setAnchorOk($backlink->getAnchorOk());
                $newEntry->setType($backlink->getTypeLastCrawl());
                $newEntry->setTypeOk($backlink->getTypeOk());
                $newEntry->setFollow($backlink->getFollowLastCrawl());
                $newEntry->setFollowOk($backlink->getFollowOk());
                $newEntry->setXPath($backlink->getXPathLastCrawl());
                $newEntry->setXPathOk($backlink->getXPathOk());
            } else {
                $newEntry->setUrlOk($backlink->getUrlOk());
                $newEntry->setAnchor(null);
                $newEntry->setAnchorOk($backlink->getAnchorOk());
                $newEntry->setType(null);
                $newEntry->setTypeOk($backlink->getTypeOk());
                $newEntry->setFollow(null);
                $newEntry->setFollowOk($backlink->getFollowOk());
                $newEntry->setXPath(null);
                $newEntry->setXPathOk($backlink->getXPathOk());
            }

            $em = $this->doctrine->getManager();
            $em->persist($newEntry);
            $em->flush($newEntry);
        }
    }

    /**
     * @param Backlink $backlink
     * @return array|bool
     */
    private function getBacklinkInfo(Backlink $backlink)
    {
        $result = $this->getContentFromUrl($backlink->getPage()->getFull());
        $info = $result['info'];
        $this->urlInfo['httpStatusCode'] = isset($info['http_code']) ? $info['http_code'] : 0;
        $this->urlInfo['url'] = isset($info['url']) ? $info['url'] : '';

        $content = $result['content'];
        if ($this->urlInfo['httpStatusCode'] != 200 || !$content
            || trim($backlink->getPage()->getFull(), '/') != trim($this->urlInfo['url'], '/')
        ) {
            return array();
        }

        return $this->searchBacklinkInContent($backlink->getUrl(), $content);
    }

    /**
     * @param string $backlinkUrl
     * @param string $content
     *
     * @return array
     */
    public function searchBacklinkInContent($backlinkUrl, $content)
    {
        $domCrawler = new DomCrawler($content);
        $linkNodes = $domCrawler->filter('a[href="' . $backlinkUrl . '"]');
        if ($linkNodes->count() == 0) {
            return array();
        }

        $results = array();
        $numLinkNodes = $linkNodes->count();
        for ($i=0; $i<$numLinkNodes; $i++) {
            $linkNode = $linkNodes->eq($i);

            $linkType = $this->getLinkType($linkNode->html());
            if ($linkType == 't') {
                $anchor = $linkNode->text();
            } else {
                $anchor = $this->getImageAltText($linkNode->html());
            }
            $anchor = str_replace("\n", ' ', $anchor);
            $anchor = str_replace("\r", ' ', $anchor);
            $anchor = str_replace("\t", ' ', $anchor);
            $anchor = preg_replace('!\s+!', ' ', $anchor);
            $anchor = trim($anchor);
            $result = array(
                'crawlType' => 'dom',
                'url' => $linkNode->attr('href'),
                'anchor' => $anchor,
                'follow' => true,
                'type' => $linkType,
                'xpath' => $linkNode->current()->getNodePath()
            );

            $rel = strtolower($linkNode->attr('rel'));
            if ($rel) {
                $rel = explode(' ', $rel);
                if (in_array('nofollow', $rel)) {
                    $result['follow'] = false;
                }
            }

            $results[] = $result;
        }

        return $results;
    }

    public function getImageAltText($html)
    {
        $altText = '';

        $domCrawler = new DomCrawler($html);
        $imgTags = $domCrawler->filter('img');
        $numImgTags = $imgTags->count();
        for ($i=0; $i<$numImgTags; $i++) {
            $imgTag = $imgTags->eq($i);
            if ($imgTag->attr('alt')) {
                $altText = $imgTag->attr('alt');
                break;
            }
        }

        return $altText;
    }

    /**
     * @param string $html
     * @return string
     */
    private function getLinkType($html)
    {
        $domCrawler = new DomCrawler($html);
        $numImgTags = $domCrawler->filter('img')->count();
        if ($numImgTags) {
            return 'i';
        }

        return 't';
    }
}
