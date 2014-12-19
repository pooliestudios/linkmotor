<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Market
 *
 * @ORM\Table(name="markets",
 *     indexes={
 *         @ORM\Index(name="name_en_idx", columns={"name_en"}),
 *         @ORM\Index(name="name_de_idx", columns={"name_de"})
 *     })
 * @ORM\Entity()
 */
class Market
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
     * @ORM\Column(name="name_en", type="string", length=128)
     */
    protected $nameEn;

    /**
     * @ORM\Column(name="name_de", type="string", length=128)
     */
    protected $nameDe;

    /**
     * @ORM\Column(name="iso_code", type="string", length=2)
     */
    protected $isoCode;

    /**
     * @ORM\OneToMany(targetEntity="Keyword", mappedBy="market")
     */
    protected $keywords;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->keywords = new \Doctrine\Common\Collections\ArrayCollection();
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
     * @param string $locale
     * @return string
     */
    public function getName($locale = 'en')
    {
        if ($locale == 'de') {
            return $this->getNameDe();
        }

        return $this->getNameEn();
    }

    /**
     * Set nameEn
     *
     * @param string $nameEn
     * @return Market
     */
    public function setNameEn($nameEn)
    {
        $this->nameEn = $nameEn;
    
        return $this;
    }

    /**
     * Get nameEn
     *
     * @return string 
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * Set nameDe
     *
     * @param string $nameDe
     * @return Market
     */
    public function setNameDe($nameDe)
    {
        $this->nameDe = $nameDe;
    
        return $this;
    }

    /**
     * Get nameDe
     *
     * @return string 
     */
    public function getNameDe()
    {
        return $this->nameDe;
    }

    /**
     * Set isoCode
     *
     * @param string $isoCode
     * @return Market
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    
        return $this;
    }

    /**
     * Get isoCode
     *
     * @return string 
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * Add keywords
     *
     * @param \Pool\LinkmotorBundle\Entity\Keyword $keywords
     * @return Market
     */
    public function addKeyword(Keyword $keywords)
    {
        $this->keywords[] = $keywords;
    
        return $this;
    }

    /**
     * Remove keywords
     *
     * @param \Pool\LinkmotorBundle\Entity\Keyword $keywords
     */
    public function removeKeyword(Keyword $keywords)
    {
        $this->keywords->removeElement($keywords);
    }

    /**
     * Get keywords
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getKeywords()
    {
        return $this->keywords;
    }
}
