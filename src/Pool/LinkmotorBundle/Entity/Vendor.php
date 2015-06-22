<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Vendor
 *
 * @ORM\Table(name="vendors")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\VendorRepository")
 * @Gedmo\Loggable
 */
class Vendor
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
     * @ORM\Column(name="name", type="string", length=128, nullable=true)
     * @Gedmo\Versioned
     */
    protected $name;

    /**
     * @ORM\Column(name="email", type="string", length=128)
     * @Assert\NotBlank()
     * @Assert\Email()
     * @Gedmo\Versioned
     */
    protected $email;

    /**
     * @ORM\Column(name="phone", type="string", length=20, nullable=true)
     * @Gedmo\Versioned
     */
    protected $phone;

    /**
     * @ORM\Column(name="title", type="smallint", nullable=true)
     * @Gedmo\Versioned
     */
    protected $title;

    /**
     * @ORM\Column(name="position", type="string", length=128, nullable=true)
     * @Gedmo\Versioned
     */
    protected $position;

    /**
     * @ORM\Column(name="company", type="string", length=64, nullable=true)
     * @Gedmo\Versioned
     */
    protected $company;

    /**
     * @ORM\Column(name="street", type="string", length=128, nullable=true)
     * @Gedmo\Versioned
     */
    protected $street;

    /**
     * @ORM\Column(name="zipcode", type="string", length=10, nullable=true)
     * @Gedmo\Versioned
     */
    protected $zipcode;

    /**
     * @ORM\Column(name="city", type="string", length=64, nullable=true)
     * @Gedmo\Versioned
     */
    protected $city;

    /**
     * @ORM\Column(name="country", type="string", length=2, nullable=true)
     * @Gedmo\Versioned
     */
    protected $country;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     * @Gedmo\Versioned
     */
    protected $comment;

    /**
     * @ORM\OneToMany(targetEntity="Domain", mappedBy="vendor")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $domains;

    /**
     * @ORM\OneToMany(targetEntity="Subdomain", mappedBy="vendor")
     * @ORM\OrderBy({"name" = "ASC"})
     */
    protected $subdomains;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->domains = new \Doctrine\Common\Collections\ArrayCollection();
        $this->subdomains = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Set email
     *
     * @param string $email
     * @return Vendor
     */
    public function setEmail($email)
    {
        $this->email = strtolower($email);
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }
    
    /**
     * Add domains
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $domains
     * @return Vendor
     */
    public function addDomain(\Pool\LinkmotorBundle\Entity\Domain $domains)
    {
        $this->domains[] = $domains;
    
        return $this;
    }

    /**
     * Remove domains
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $domains
     */
    public function removeDomain(\Pool\LinkmotorBundle\Entity\Domain $domains)
    {
        $this->domains->removeElement($domains);
    }

    /**
     * Get domains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * Set phone
     *
     * @param string $phone
     * @return Vendor
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
    
        return $this;
    }

    /**
     * Get phone
     *
     * @return string 
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set title
     *
     * @param string $title
     * @return Vendor
     */
    public function setTitle($title)
    {
        $this->title = $title;
    
        return $this;
    }

    /**
     * Get title
     *
     * @return string 
     */
    public function getTitle()
    {
        return $this->title;
    }

    public function getDisplayTitle()
    {
        if ($this->getTitle() == 3) {
            return 'Company';
        } elseif ($this->getTitle() == 1) {
            return 'Mr.';
        } elseif ($this->getTitle() == 2) {
            return 'Ms.';
        } else {
            return '-';
        }
    }

    /**
     * Set company
     *
     * @param string $company
     * @return Vendor
     */
    public function setCompany($company)
    {
        $this->company = $company;
    
        return $this;
    }

    /**
     * Get company
     *
     * @return string 
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set street
     *
     * @param string $street
     * @return Vendor
     */
    public function setStreet($street)
    {
        $this->street = $street;
    
        return $this;
    }

    /**
     * Get street
     *
     * @return string 
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * Set zipcode
     *
     * @param string $zipcode
     * @return Vendor
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;
    
        return $this;
    }

    /**
     * Get zipcode
     *
     * @return string 
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * Set city
     *
     * @param string $city
     * @return Vendor
     */
    public function setCity($city)
    {
        $this->city = $city;
    
        return $this;
    }

    /**
     * Get city
     *
     * @return string 
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     * @return Vendor
     */
    public function setCountry($country)
    {
        $this->country = $country;
    
        return $this;
    }

    /**
     * Get country
     *
     * @return string 
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Vendor
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

    public function getDisplayName()
    {
        $displayName = $this->getName();
        if (!$displayName) {
            $displayName = $this->getEmail();
        }

        return $displayName;
    }

    public function getDisplayNameWithEmail()
    {
        $displayName = $this->getDisplayName();

        if ($displayName != $this->getEmail()) {
            $displayName .= ' (' . $this->getEmail() . ')';
        }

        return $displayName;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return Vendor
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    
        return $this;
    }

    /**
     * Get comment
     *
     * @return string 
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Add subdomains
     *
     * @param \Pool\LinkmotorBundle\Entity\Subdomain $subdomains
     * @return Vendor
     */
    public function addSubdomain(\Pool\LinkmotorBundle\Entity\Subdomain $subdomains)
    {
        $this->subdomains[] = $subdomains;
    
        return $this;
    }

    /**
     * Remove subdomains
     *
     * @param \Pool\LinkmotorBundle\Entity\Subdomain $subdomains
     */
    public function removeSubdomain(\Pool\LinkmotorBundle\Entity\Subdomain $subdomains)
    {
        $this->subdomains->removeElement($subdomains);
    }

    /**
     * Get subdomains
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getSubdomains()
    {
        return $this->subdomains;
    }

    /**
     * Set position
     *
     * @param string $position
     * @return Vendor
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position
     *
     * @return string 
     */
    public function getPosition()
    {
        return $this->position;
    }
}
