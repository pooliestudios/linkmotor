<?php

namespace Pool\LinkmotorBundle\Service;

use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Subdomain;

/**
 * Class RobotsTxt
 *
 * Code for parsing from: https://github.com/keysolutions/robotsparser-php
 */
class RobotsTxt
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    const PROCESS_STATE_USER_AGENT = 0;
    const PROCESS_STATE_USER_AGENT_MATCHED = 1;
    const PROCESS_STATE_DISALLOW = 2;

    private $robotUserAgentPattern;
    private $state;
    private $disallowedPaths;

    private $userAgent = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Win64; x64; Trident/6.0; MASEJS)';

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function isGoogleBotAllowedForPage(Page $page)
    {
        $robotsTxt = $this->getRobotsTxtForSubdomain($page->getSubdomain());

        return $this->testGoogleBotAllowedForContent($page->getUrl(), $robotsTxt);
    }

    private function testGoogleBotAllowedForContent($url, $content)
    {
        if ($content) {
            try {
                $this->parse($content, '/Googlebot\s|Googlebot$/i');
            } catch (\Exception $e) {
                $this->disallowedPaths = array();
            }
        } else {
            $this->disallowedPaths = array();
        }

        foreach ($this->disallowedPaths as $disallowedPath) {
            if (strpos($url, $disallowedPath) === 0) {
                return false;
            }
        }

        return true;
    }

    public function isGoogleBotAllowedForUrl($url)
    {
        $robotsTxt = null;

        $dummyDomain = new Domain();
        $urlParts = $dummyDomain->getUrlParts($url);
        $domain =  $this->doctrine->getManager()
            ->getRepository('PoolLinkmotorBundle:Domain')
            ->findByName($urlParts['domain']);
        if ($domain) {
            $domain = $domain[0];
            $subdomain = $this->doctrine->getManager()
                ->getRepository('PoolLinkmotorBundle:Subdomain')
                ->findBy(array('domain' => $domain->getId(), 'name' => $urlParts['subdomain']));

            if ($subdomain) {
                $robotsTxt = $this->getRobotsTxtForSubdomain($subdomain[0]);
            }
        }

        if ($robotsTxt === null) {
            $subDomain = '';
            if ($urlParts['subdomain']) {
                $subDomain .= $urlParts['subdomain'] . '.';
            }
            $robotsTxtUrl = 'http://' . $subDomain . $urlParts['domain'] . '/robots.txt';
            $robotsTxt = $this->getContentFromUrl($robotsTxtUrl);
        }

        return $this->testGoogleBotAllowedForContent($url, $robotsTxt);
    }

    protected function getRobotsTxtForSubdomain(Subdomain $subdomain)
    {
        if ($subdomain->robotsTxtNeedsRefresh()) {
            $content = $this->getContentFromUrl('http://' . $subdomain->getFull() . '/robots.txt');
            if (!$content) {
                $content = '';
            }
            $subdomain->setRobotsTxt($content);
            $subdomain->setRobotsTxtLastFetched(new \DateTime());

            if ($this->doctrine) {
                $em = $this->doctrine->getManager();
                $em->persist($subdomain);
                $em->flush($subdomain);
            }
        }

        return $subdomain->getRobotsTxt();
    }

    protected function parse($content, $robotUserAgentPattern)
    {
        $this->robotUserAgentPattern = $robotUserAgentPattern;
        $this->state = RobotsTxt::PROCESS_STATE_USER_AGENT;
        $this->disallowedPaths = array();
        $this->doParse($content);
    }

    private function doParse($content)
    {
        $content = $this->stripCommentLines($content);

        if (!preg_match_all("/((User-agent|Disallow|Allow):\s*.*)\s*/i", $content, $matches)) {
            throw new \Exception("robots.txt was invalid.");
        }

        $this->process($matches[1]);
    }

    private function stripCommentLines($content)
    {
        $lines = explode("\n", $content);
        $strippedLines = array();
        foreach ($lines as $line) {
            if (strpos($line, '#') === 0) {
                continue;
            }
            $strippedLines[] = $line;
        }

        return implode("\n", $strippedLines);
    }

    private function process($lines)
    {
        foreach ($lines as $line) {
            preg_match("/(.+):\s*(.*)/", $line, $matches);
            $this->processLine($matches[1], $matches[2]);
        }
    }

    private function processLine($key, $value)
    {
        switch ($this->state) {
            case RobotsTxt::PROCESS_STATE_USER_AGENT:
                if ((strtolower($key) == "user-agent" && $value == "*") ||
                    preg_match($this->robotUserAgentPattern, $value)) {

                    $this->state = RobotsTxt::PROCESS_STATE_USER_AGENT_MATCHED;
                }
                break;
            case RobotsTxt::PROCESS_STATE_USER_AGENT_MATCHED:
                if (strtolower($key) == "disallow") {
                    $this->state = RobotsTxt::PROCESS_STATE_DISALLOW;
                    $this->processLine($key, $value);
                } elseif (strtolower($key) == 'allow') {
                    $this->state = RobotsTxt::PROCESS_STATE_USER_AGENT;
                    $this->processLine($key, $value);
                }
                break;
            case RobotsTxt::PROCESS_STATE_DISALLOW:
                if (strtolower($key) == "disallow") {
                    // The robots.txt spec states that an empty Disallow entry
                    // should undo any previously matched rules
                    if (empty($value)) {
                        $this->disallowedPaths = array();
                    } else {
                        array_push($this->disallowedPaths, $value);
                    }
                } else {
                    $this->state = RobotsTxt::PROCESS_STATE_USER_AGENT;
                    $this->processLine($key, $value);
                }
                break;
        }
    }

    private function getContentFromUrl($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // @todo
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        if ($info['http_code'] != 200) {
            $content = '';
        }

        return $content;
    }
}
