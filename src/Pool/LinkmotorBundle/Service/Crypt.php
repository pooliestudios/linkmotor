<?php

namespace Pool\LinkmotorBundle\Service;

class Crypt
{
    protected $logger;
    /**
     * @var Options
     */
    protected $options;
    private $key;
    private $encryptionKey;
    private $module;
    protected $cipher = 'blowfish';
    protected $cipherMode = 'ecb';
    protected $isInitialized;

    public function __construct($logger, Options $options)
    {
        $this->options = $options;
        $this->key = $options->get('account_secret_key');
        $this->logger = $logger;

        $this->isInitialized = false;
    }

    public function __destruct()
    {
        if ($this->isInitialized) {
            $this->closeDeInitModule();
        }
    }

    public function setKey($key)
    {
        $this->key = $key;
    }

    public function encrypt($data, $format = 'plain')
    {
        if (!$this->isInitialized) {
            $this->initModule();
        }
        // apply padding
        $block = mcrypt_get_block_size($this->cipher, $this->cipherMode);
        $pad = $block - (strlen($data) % $block);
        $data .= str_repeat(chr($pad), $pad);

        $encrypted = mcrypt_generic($this->module, $data);

        if ($format == 'base64') {
            $encrypted = str_replace('=', '', base64_encode($encrypted));
        }

        return $encrypted;
    }

    public function decrypt($encrypted)
    {
        if (!$this->isInitialized) {
            $this->initModule();
        }

        $decrypted = mdecrypt_generic($this->module, $encrypted);

        // resolve padding
        $len = strlen($decrypted);
        $pad = ord($decrypted[$len - 1]);
        $decrypted = substr($decrypted, 0, strlen($decrypted) - $pad);

        return $decrypted;
    }

    protected function initModule()
    {
        /* Open the cipher */
        $this->logMessage('Opening the cipher');
        $this->module = mcrypt_module_open($this->cipher, '', $this->cipherMode, '');

        $this->logMessage('Creating IV');
        $iv = mcrypt_create_iv(mcrypt_enc_get_iv_size($this->module), MCRYPT_RAND);
        $ks = mcrypt_enc_get_key_size($this->module);

        /* Create key */
        $this->encryptionKey = substr($this->key, 0, $ks);

        /* Intialize encryption */
        $this->logMessage('Initializing encryption');
        mcrypt_generic_init($this->module, $this->encryptionKey, $iv);

        $this->isInitialized = true;
    }

    protected function closeDeInitModule()
    {
        /* Terminate decryption handle and close module */
        $this->logMessage('Closing cipher module');
        mcrypt_generic_deinit($this->module);
        mcrypt_module_close($this->module);
    }

    protected function logMessage($message)
    {
        if ($this->logger) {
            $this->logger->debug('CRYPT: ' . $message);
        }
    }
}
