<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class PageNote extends Note
{
    /**
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="notes")
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $page;

    /**
     * Set page
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $page
     * @return PageNote
     */
    public function setPage(Page $page = null)
    {
        $this->page = $page;
    
        return $this;
    }

    /**
     * Get page
     *
     * @return \Pool\LinkmotorBundle\Entity\Page
     */
    public function getPage()
    {
        return $this->page;
    }
}
