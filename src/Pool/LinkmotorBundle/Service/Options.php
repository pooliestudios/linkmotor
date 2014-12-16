<?php

namespace Pool\LinkmotorBundle\Service;

use Pool\LinkmotorBundle\Entity\Option;

class Options
{
    /**
     * @var \Doctrine\Bundle\DoctrineBundle\Registry
     */
    private $doctrine;

    private $isBatchSet;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
        $this->isBatchSet = false;
    }

    /**
     * @param string $name
     * @param string $default
     *
     * @return string
     */
    public function get($name, $default = '')
    {
        $option = $this->doctrine->getRepository('PoolLinkmotorBundle:Option')->findByName($name);
        if (!$option) {
            return $default;
        }

        return $option[0]->getValue();
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function set($name, $value)
    {
        $option = $this->doctrine->getRepository('PoolLinkmotorBundle:Option')->findByName($name);
        if (!$option) {
            $option = new Option();
            $option->setName($name);
        } else {
            $option = $option[0];
        }
        $option->setValue((string)$value);

        $em = $this->doctrine->getManager();
        $em->persist($option);

        if (!$this->isBatchSet) {
            $em->flush();
        }
    }

    /**
     * @return array
     */
    public function getAll($startingWith = '')
    {
        $defaultOptions = array(
            'account_id' => '', 'account_secret_key' => '',
            'invoice_company' => '', 'invoice_tax_id' => '', 'invoice_name' => '', 'invoice_address' => '',
            'invoice_zipcode' => '', 'invoice_city' => '', 'invoice_country' => '', 'invoice_email' => ''
        );

        $data = $this->doctrine->getRepository('PoolLinkmotorBundle:Option')->getStartingWith($startingWith);
        $options = array();
        foreach ($data as $item) {
            $options[$item->getName()] = $item->getValue();
        }

        foreach ($defaultOptions as $key => $value) {
            if (!isset($options[$key]) && strpos($key, $startingWith) === 0) {
                $options[$key] = $value;
            }
        }

        return $options;
    }

    /**
     * @param array $options
     */
    public function setAll($options)
    {
        $this->isBatchSet = true;

        foreach ($options as $name => $value) {
            $this->set($name, $value);
        }

        $this->isBatchSet = false;
        $this->doctrine->getManager()->flush();
    }

    public function getInvoiceInfo()
    {
        return $this->getAll('invoice_');
    }
}
