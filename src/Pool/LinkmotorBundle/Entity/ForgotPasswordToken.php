<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ForgotPasswordToken
 *
 * @ORM\Table(name="forgot_password_tokens")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\ForgotPasswordTokenRepository")
 */
class ForgotPasswordToken
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
     * @ORM\Column(name="hash", type="string", length=32)
     */
    protected $hash;

    /**
     * @ORM\Column(name="valid_until", type="datetime")
     */
    protected $validUntil;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="forgotPasswordTokens")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

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
     * @return string
     */
    public function generateValidationId()
    {
        return substr(md5(uniqid()), 0, 8);
    }

    /**
     * @param string $validationId
     * @param string $salt
     * @return string
     */
    public function generateHash($validationId, $salt)
    {
        return md5($salt . $validationId);
    }

    /**
     * @param string $validationId
     * @param string $salt
     * @return bool
     */
    public function verifyValidationId($validationId, $salt)
    {
        return $this->hash == $this->generateHash($validationId, $salt)
               && $this->getValidUntil()->format('Y-m-d H:i:s') > date('Y-m-d H:i:s');
    }

    /**
     * Set hash
     *
     * @param string $hash
     * @return ForgotPasswordToken
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    
        return $this;
    }

    /**
     * Get hash
     *
     * @return string 
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * Set validUntil
     *
     * @param \DateTime $validUntil
     * @return ForgotPasswordToken
     */
    public function setValidUntil($validUntil)
    {
        $this->validUntil = $validUntil;
    
        return $this;
    }

    /**
     * Get validUntil
     *
     * @return \DateTime 
     */
    public function getValidUntil()
    {
        return $this->validUntil;
    }

    /**
     * Set user
     *
     * @param \Pool\LinkmotorBundle\Entity\User $user
     * @return ForgotPasswordToken
     */
    public function setUser($user = null)
    {
        $this->user = $user;
    
        return $this;
    }

    /**
     * Get user
     *
     * @return \Pool\LinkmotorBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
}
