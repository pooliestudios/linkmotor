<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ActionStats
 *
 * * @ORM\Table(name="action_stats",
 *     indexes={
 *         @ORM\Index(name="date_id", columns={"date"}),
 *         @ORM\Index(name="user_id_idx", columns={"user_id"}),
 *         @ORM\Index(name="project_id_idx", columns={"project_id"})
 *     })
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\ActionStatsRepository")
 */
class ActionStats
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
     * @ORM\Column(name="date", type="date")
     */
    protected $date;

    /**
     * @ORM\Column(name="num_backlinks_created", type="integer")
     */
    protected $numBacklinksCreated;

    /**
     * @ORM\Column(name="num_checked_pages", type="integer")
     */
    protected $numCheckedPages;

    /**
     * @ORM\Column(name="num_contacts_made", type="integer")
     */
    protected $numContactsMade;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="actionStats")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="actionStats")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    public function __construct()
    {
        $this->setDate(new \DateTime());
        $this->setNumBacklinksCreated(0);
        $this->setNumCheckedPages(0);
        $this->setNumContactsMade(0);
        $this->setUser(null);
        $this->setProject(null);
    }

    /**
     * @return bool
     */
    public function isEmpty()
    {
        return $this->getNumBacklinksCreated() == 0
               && $this->getNumCheckedPages() == 0
               && $this->getNumContactsMade() == 0;
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
     * Set date
     *
     * @param \DateTime $date
     * @return ActionStats
     */
    public function setDate($date)
    {
        $this->date = $date;
    
        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set numBacklinksCreated
     *
     * @param integer $numBacklinksCreated
     * @return ActionStats
     */
    public function setNumBacklinksCreated($numBacklinksCreated)
    {
        $this->numBacklinksCreated = $numBacklinksCreated;
    
        return $this;
    }

    /**
     * Get numBacklinksCreated
     *
     * @return integer 
     */
    public function getNumBacklinksCreated()
    {
        return $this->numBacklinksCreated;
    }

    /**
     * Set numCheckedPages
     *
     * @param integer $numCheckedPages
     * @return ActionStats
     */
    public function setNumCheckedPages($numCheckedPages)
    {
        $this->numCheckedPages = $numCheckedPages;
    
        return $this;
    }

    /**
     * Get numCheckedPages
     *
     * @return integer 
     */
    public function getNumCheckedPages()
    {
        return $this->numCheckedPages;
    }

    /**
     * Set numContactsMade
     *
     * @param integer $numContactsMade
     * @return ActionStats
     */
    public function setNumContactsMade($numContactsMade)
    {
        $this->numContactsMade = $numContactsMade;
    
        return $this;
    }

    /**
     * Get numContactsMade
     *
     * @return integer 
     */
    public function getNumContactsMade()
    {
        return $this->numContactsMade;
    }

    /**
     * Set user
     *
     * @param \Pool\LinkmotorBundle\Entity\User $user
     * @return ActionStats
     */
    public function setUser(User $user = null)
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

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return ActionStats
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
}
