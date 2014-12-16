<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Option
 *
 * @ORM\Table(name="options",
 *     indexes={
 *         @ORM\Index(name="name_idx", columns={"name"}),
 *     })
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\OptionRepository")
 */
class Option
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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="value", type="string", length=255)
     */
    protected $value;

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
     * @return Option
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
     * Set value
     *
     * @param string $value
     * @return Option
     */
    public function setValue($value)
    {
        if ($value == null) {
            $value = '';
        }
        $this->value = trim($value);
    
        return $this;
    }

    /**
     * Get value
     *
     * @return string 
     */
    public function getValue()
    {
        //@todo besseren Weg finden

        if (strpos($this->getName(), '_active') !== false) {
            $this->value = $this->value ? true : false;
        }
        return $this->value;
    }
}
