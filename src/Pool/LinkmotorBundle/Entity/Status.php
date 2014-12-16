<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Status
 *
 * @ORM\Table(name="status",
 *     indexes={
 *         @ORM\Index(name="sort_order_idx", columns={"sort_order"}),
 *     })
 * @ORM\Entity()
 */
class Status
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="name", type="string", length=128)
     */
    protected $name;

    /**
     * @ORM\Column(name="sort_order", type="integer")
     */
    protected $sortOrder;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="status")
     */
    protected $pages;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param $id
     * @return Status $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Status
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     * @return Status
     */
    public function addPage(\Pool\LinkmotorBundle\Entity\Page $pages)
    {
        $this->pages[] = $pages;
    
        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     */
    public function removePage(\Pool\LinkmotorBundle\Entity\Page $pages)
    {
        $this->pages->removeElement($pages);
    }

    /**
     * Get pages
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Set sortOrder
     *
     * @param integer $sortOrder
     * @return Status
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;
    
        return $this;
    }

    /**
     * Get sortOrder
     *
     * @return integer 
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    public static function getValueForGroup($value)
    {
        $mapping = array(
            'relevant' => array(2),
            'new' => array(1),
            'contacted' => array(4, 5),
            'contact1' => array(4),
            'contact2' => array(5),
            'in-progress' => array(8),
            'linked' => array(6),
            'offline' => array(7),
            'not-relevant' => array(3)
        );

        return $mapping[$value];
    }

    public function isNotRelevant()
    {
        return $this->getId() == 3;
    }
}
