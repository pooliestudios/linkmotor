<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class VendorNote extends Note
{
    /**
     * @ORM\ManyToOne(targetEntity="Vendor")
     * @ORM\JoinColumn(name="vendor_id", referencedColumnName="id")
     */
    protected $vendor;

    /**
     * Set vendor
     *
     * @param \Pool\LinkmotorBundle\Entity\Vendor $vendor
     * @return VendorNote
     */
    public function setVendor(Vendor $vendor = null)
    {
        $this->vendor = $vendor;
    
        return $this;
    }

    /**
     * Get vendor
     *
     * @return \Pool\LinkmotorBundle\Entity\Vendor
     */
    public function getVendor()
    {
        return $this->vendor;
    }
}
