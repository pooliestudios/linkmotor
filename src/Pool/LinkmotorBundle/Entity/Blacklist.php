<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Blacklist
 *
 * @ORM\Table(name="blacklist")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\BlacklistRepository")
 */
class Blacklist
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
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    protected $note;

    /**
     * @ORM\ManyToOne(targetEntity="Domain", inversedBy="blacklists")
     * @ORM\JoinColumn(name="domain_id", referencedColumnName="id")
     */
    protected $domain;


    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="blacklist")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

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
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return Blacklist
     */
    public function setProject(Project $project = null)
    {
        $this->project = $project;
    
        return $this;
    }

    /**
     * Get project
     *
     * @return \Pool\LinkmotorBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set note
     *
     * @param string $note
     * @return Blacklist
     */
    public function setNote($note)
    {
        $this->note = $note;
    
        return $this;
    }

    /**
     * Get note
     *
     * @return string 
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set domain
     *
     * @param \Pool\LinkmotorBundle\Entity\Domain $domain
     * @return Blacklist
     */
    public function setDomain(Domain $domain = null)
    {
        $this->domain = $domain;
    
        return $this;
    }

    /**
     * Get domain
     *
     * @return \Pool\LinkmotorBundle\Entity\Domain
     */
    public function getDomain()
    {
        return $this->domain;
    }
}
