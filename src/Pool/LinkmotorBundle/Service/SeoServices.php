<?php

namespace Pool\LinkmotorBundle\Service;

use Pool\LinkmotorBundle\Entity\Domain;
use Pool\LinkmotorBundle\Entity\Subdomain;

class SeoServices
{
    private $userAgent = 'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.2; Win64; x64; Trident/6.0; MASEJS)';
    private $accountId;

    /**
     * @var Options
     */
    private $options;

    /**
     * @var Crypt
     */
    private $crypt;
    private $seoServicesUrl;

    public function __construct(Options $options, Crypt $crypt, $seoServicesUrl)
    {
        $this->options = $options;
        $this->accountId = $options->get('account_id');
        $this->crypt = $crypt;
        $this->seoServicesUrl = $seoServicesUrl;
    }

    public function checkAccount()
    {
        $url = "{$this->seoServicesUrl}/accounts/check/?apiKey={$this->getApiKey()}";
        $result = $this->getContentFromUrl($url);
        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return false;
        }

        $data = json_decode($content, true);

        return $data['data'] == 'ok';
    }

    public function registerSelfHostedAccount()
    {
        $url = "{$this->seoServicesUrl}/accounts/register/self-hosted/";
        $result = $this->getContentFromUrl($url);
        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return false;
        }

        $data = json_decode($content, true);

        return $data['data'];
    }

    public function updateAccountType($accountType)
    {
        $url = "{$this->seoServicesUrl}/updates/account-type/";
        $postData = array(
            'apiKey' => $this->getApiKey(),
            'accountType' => $accountType,
            'invoiceInfo' => @json_encode($this->options->getInvoiceInfo())
        );
        $result = $this->getContentFromUrl($url, $postData);
        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return false;
        }

        $data = json_decode($content, true);

        return $data['data'];
    }

    /**
     * @param Domain $domain
     * @param int $page
     *
     * @return array|bool
     */
    public function getUrlsByDomain(Domain $domain, $page = 1)
    {
        $apiKey = $this->getApiKey();
        $domain = urlencode($domain->getName());
        $url = "{$this->seoServicesUrl}/prospects/find-by/?apiKey={$apiKey}&domain={$domain}&page={$page}";
        $result = $this->getContentFromUrl($url);

        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return false;
        }

        return json_decode($content, true);
    }

    /**
     * @param Domain $domain
     *
     * @return array
     */
    public function getContactInfoForDomain(Domain $domain)
    {
        $apiKey = $this->getApiKey();
        $domain = urlencode($domain->getName());
        $url = "{$this->seoServicesUrl}/urls/contact-info/?apiKey={$apiKey}&domain={$domain}";
        $result = $this->getContentFromUrl($url);

        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return array();
        }

        return json_decode($content, true);
    }

    /**
     * @param Subdomain $subdomain
     *
     * @return array
     */
    public function getContactInfoForSubdomain(Subdomain $subdomain)
    {
        $apiKey = $this->getApiKey();
        $subdomain = urlencode($subdomain->getFull());
        $url = "{$this->seoServicesUrl}/urls/contact-info/?apiKey={$apiKey}&domain={$subdomain}";
        $result = $this->getContentFromUrl($url);

        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return array();
        }

        return json_decode($content, true);
    }

    /**
     * @param string $url
     *
     * @return array
     */
    public function getSocialMediaInfoForUrl($url)
    {
        $apiKey = $this->getApiKey();
        $url = urlencode($url);
        $apiCall = "{$this->seoServicesUrl}/urls/socialmedia-info/?apiKey={$apiKey}&url={$url}";
        $result = $this->getContentFromUrl($apiCall);

        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return array();
        }

        return json_decode($content, true);
    }

    /**
     * @param string $keyword
     * @param string $market
     * @param int $page
     * @return bool|mixed
     */
    public function getUrlsByKeyword($keyword, $market, $page = 1)
    {
        $apiKey = $this->getApiKey();
        $keyword = urlencode($keyword);
        $url = "{$this->seoServicesUrl}/prospects/find-by/"
            . "?apiKey={$apiKey}&keyword={$keyword}&market={$market}&page={$page}";
        $result = $this->getContentFromUrl($url);

        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return false;
        }

        return json_decode($content, true);
    }

    /**
     * @param $domain
     * @return mixed
     */
    public function getDomainAuthority($domain)
    {
        $value = $this->getRemoteValue('domains/authority', array('domain' => $domain));

        return isset($value['domainAuthority']) ? $value['domainAuthority'] : false;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function getPageAuthority($url)
    {
        $value = $this->getRemoteValue('urls/authority', array('url' => $url));

        return isset($value['pageAuthority']) ? $value['pageAuthority'] : false;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function getPagePopValues($url)
    {
        return $this->getRemoteValue('urls/popvalues', array('url' => $url));
    }

    /**
     * @param $domain
     * @return mixed
     */
    public function getDomainPopValues($domain)
    {
        return $this->getRemoteValue('domains/popvalues', array('domain' => $domain));
    }

    /**
     * @param string $domain Domain or Subdomain
     * @return integer
     */
    public function getSistrixSichtbarkeitsIndex($domain)
    {
        return $this->getRemoteValue('domains/sichtbarkeitsindex', array(
            'sistrix_api_key' => $this->options->get('sistrix_api_key'),
            'domain' => $domain
        ));
    }

    /**
     * @param string $domain Domain or Subdomain
     * @return integer
     */
    public function getOvi($domain)
    {
        return $this->getRemoteValue('domains/ovi', array(
            'xovi_api_key' => $this->options->get('xovi_api_key'),
            'domain' => $domain
        ));
    }

    /**
     * @param Domain $domain
     * @return \DateTime|null
     */
    public function getArchiveOrgFirstDay(Domain $domain)
    {
        $data = $this->getRemoteValue('domains/firstDay', array(
            'domain' => $domain->getName()
        ));

        if ($data) {
            return new \DateTime($data);
        }

        return null;
    }

    /**
     * @param string $action
     * @param array $params
     * @return mixed
     */
    private function getRemoteValue($action, $params)
    {
        $apiKey = $this->getApiKey();

        $url = "{$this->seoServicesUrl}/{$action}/?apiKey={$apiKey}";
        foreach ($params as $name => $value) {
            $url .= "&{$name}=" . urlencode($value);
        }
        $result = $this->getContentFromUrl($url);

        $info = $result['info'];
        $httpStatusCode = isset($info['http_code']) ? $info['http_code'] : 0;
        $content = $result['content'];
        if ($httpStatusCode != 200 || !$content) {
            return false;
        }

        $data = json_decode($content, true);

        return $data['data'];
    }

    private function getApiKey()
    {
        $encryptedAccountId = $this->crypt->encrypt($this->accountId, 'base64');
        $apiKey = "{$this->accountId}:{$encryptedAccountId}";

        return str_replace('=', '', base64_encode($apiKey));
    }

    private function getContentFromUrl($url, $postData = null)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->userAgent);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // @todo
        if ($postData !== null) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        }
        $content = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return array(
            'content' => $content,
            'info' => $info
        );
    }
}
