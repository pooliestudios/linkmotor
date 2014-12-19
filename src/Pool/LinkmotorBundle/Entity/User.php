<?php

namespace Pool\LinkmotorBundle\Entity;

use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * User
 *
 * @ORM\Table(name="users")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\UserRepository")
 * @UniqueEntity(fields={"email"}, message="This E-Mail address is already taken!")
 */
class User implements AdvancedUserInterface
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
     * @ORM\Column(name="email", type="string", length=255)
     */
    protected $email;

    /**
     * @ORM\Column(name="password", type="string", length=88)
     */
    protected $password;

    /**
     * @ORM\Column(name="salt", type="string", length=40)
     */
    protected $salt;

    /**
     * @ORM\Column(name="is_admin", type="smallint")
     */
    protected $isAdmin;

    /**
     * @ORM\Column(name="items_per_page", type="integer")
     */
    protected $itemsPerPage;

    /**
     * @ORM\Column(name="options", type="text")
     */
    protected $options;

    /**
     * @ORM\Column(name="inactive", type="boolean")
     */
    protected $inactive;

    /**
     * @ORM\ManyToOne(targetEntity="Project")
     * @ORM\JoinColumn(name="last_used_project_id", referencedColumnName="id")
     */
    protected $lastUsedProject;

    /**
     * @ORM\OneToMany(targetEntity="Alert", mappedBy="user")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $alerts;

    /**
     * @ORM\OneToMany(targetEntity="Page", mappedBy="assignedTo")
     */
    protected $pages;

    /**
     * @ORM\OneToMany(targetEntity="Backlink", mappedBy="assignedTo")
     */
    protected $backlinks;

    /**
     * @ORM\OneToMany(targetEntity="Competitor", mappedBy="assignedTo")
     * @ORM\OrderBy({"createdAt" = "DESC"})
     */
    protected $competitors;

    /**
     * @ORM\OneToMany(targetEntity="Keyword", mappedBy="assignedTo")
     * @ORM\OrderBy({"keyword" = "ASC"})
     */
    protected $keywords;

    /**
     * @ORM\OneToMany(targetEntity="ActionStats", mappedBy="user")
     */
    protected $actionStats;

    /**
     * @ORM\OneToMany(targetEntity="ForgotPasswordToken", mappedBy="user")
     */
    protected $forgotPasswordTokens;

    /**
     * @ORM\OneToMany(targetEntity="Import", mappedBy="createdBy")
     */
    protected $imports;

    /**
     * @ORM\OneToMany(targetEntity="NotificationSetting", mappedBy="user")
     */
    protected $notificationSettings;

    public function __construct()
    {
        $this->setNewSalt();
        $this->email = '';
        $this->password = '';
        $this->name = '';
        $this->setInactive(false);
        $this->setAdmin(false);
        $this->itemsPerPage = 50;
        $this->setOptions(array());
        $this->alerts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->backlinks = new \Doctrine\Common\Collections\ArrayCollection();
        $this->competitors = new \Doctrine\Common\Collections\ArrayCollection();
        $this->keywords = new \Doctrine\Common\Collections\ArrayCollection();
        $this->pages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->actionStats = new \Doctrine\Common\Collections\ArrayCollection();
        $this->forgotPasswordTokens = new \Doctrine\Common\Collections\ArrayCollection();
        $this->imports = new \Doctrine\Common\Collections\ArrayCollection();
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
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        $roles = array('ROLE_USER');
        if ($this->isAdmin()) {
            $roles[] = 'ROLE_ADMIN';
        }

        return $roles;
    }

    /**
     * Returns the password used to authenticate the user.
     *
     * This should be the encoded password. On authentication, a plain-text
     * password will be salted, encoded, and then compared to this value.
     *
     * @return string The password
     */
    public function getPassword()
    {
        return $this->password;
    }

    public function setNewSalt()
    {
        $this->salt = sha1(uniqid('', true));
    }

    /**
     * Returns the salt that was originally used to encode the password.
     *
     * This can return null if the password was not encoded using a salt.
     *
     * @return string The salt
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Returns the username used to authenticate the user.
     *
     * @return string The username
     */
    public function getUsername()
    {
        return $this->getEmail();
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return Boolean true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return Boolean true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return true;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return Boolean true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Checks whether the user is enabled.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a DisabledException and prevent login.
     *
     * @return Boolean true if the user is enabled, false otherwise
     *
     * @see DisabledException
     */
    public function isEnabled()
    {
        return $this->isInactive() == false;
    }


    public function isSupportUser()
    {
        return strtolower($this->getEmail()) == 'support@linkmotor.de';
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     *
     * @return void
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

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
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Set isAdmin
     *
     * @param boolean $isAdmin
     * @return User
     */
    public function setAdmin($isAdmin)
    {
        $this->isAdmin = $isAdmin;

        return $this;
    }

    /**
     * Get isAdmin
     *
     * @return boolean
     */
    public function isAdmin()
    {
        return $this->isAdmin;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return User
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
     * @return string
     */
    public function getDisplayName()
    {
        $displayName = $this->getName();
        if (!$displayName) {
            $displayName = $this->getEmail();
        }

        return $displayName;
    }

    /**
     * @return array
     */
    public function getDisplayRoles()
    {
        $roles = array('User');
        if ($this->isAdmin) {
            $roles[] = 'Manager';
        }

        return $roles;
    }

    /**
     * Add alerts
     *
     * @param \Pool\LinkmotorBundle\Entity\Alert $alerts
     * @return User
     */
    public function addAlert(Alert $alerts)
    {
        $this->alerts[] = $alerts;
    
        return $this;
    }

    /**
     * Remove alerts
     *
     * @param \Pool\LinkmotorBundle\Entity\Alert $alerts
     */
    public function removeAlert(Alert $alerts)
    {
        $this->alerts->removeElement($alerts);
    }

    /**
     * Get alerts
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAlerts()
    {
        return $this->alerts;
    }

    /**
     * Set lastUsedProject
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $lastUsedProject
     * @return User
     */
    public function setLastUsedProject(Project $lastUsedProject = null)
    {
        $this->lastUsedProject = $lastUsedProject;
    
        return $this;
    }

    /**
     * Get lastUsedProject
     *
     * @return \Pool\LinkmotorBundle\Entity\Project
     */
    public function getLastUsedProject()
    {
        return $this->lastUsedProject;
    }

    /**
     * Set itemsPerPage
     *
     * @param integer $itemsPerPage
     * @return User
     */
    public function setItemsPerPage($itemsPerPage)
    {
        $this->itemsPerPage = $itemsPerPage;
    
        return $this;
    }

    /**
     * Get itemsPerPage
     *
     * @return integer 
     */
    public function getItemsPerPage()
    {
        return $this->itemsPerPage;
    }

    /**
     * Add pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     * @return User
     */
    public function addPage(Page $pages)
    {
        $this->pages[] = $pages;
    
        return $this;
    }

    /**
     * Remove pages
     *
     * @param \Pool\LinkmotorBundle\Entity\Page $pages
     */
    public function removePage(Page $pages)
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
     * Add backlinks
     *
     * @param \Pool\LinkmotorBundle\Entity\Backlink $backlinks
     * @return User
     */
    public function addBacklink(Backlink $backlinks)
    {
        $this->backlinks[] = $backlinks;
    
        return $this;
    }

    /**
     * Remove backlinks
     *
     * @param \Pool\LinkmotorBundle\Entity\Backlink $backlinks
     */
    public function removeBacklink(Backlink $backlinks)
    {
        $this->backlinks->removeElement($backlinks);
    }

    /**
     * Get backlinks
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getBacklinks()
    {
        return $this->backlinks;
    }

    /**
     * Set options
     *
     * @param string $options
     * @return User
     */
    public function setOptions($options)
    {
        if (!is_array($options)) {
            $options = array();
        }
        $this->options = json_encode($options);
    
        return $this;
    }

    /**
     * Get options
     *
     * @return string 
     */
    public function getOptions()
    {
        $options = json_decode($this->options, true);
        if (!is_array($options)) {
            $options = array();
        }

        return $options;
    }

    public function getOptionsDashboardType()
    {
        return $this->getOptionValue('dashboard_type', 'all');
    }

    public function setOptionsDashboardType($value)
    {
        return $this->setOptionValue('dashboard_type', $value);
    }

    public function getOptionsPagesType()
    {
        $value = $this->getOptionValue('pages_type', 'all');

        // 'my' is still in the user-data
        if ($value == 'my') {
            $value = 'my-new';
        }

        return $value;
    }

    public function setOptionsPagesType($value)
    {
        $this->setOptionValue('pages_type', $value);

        return $this;
    }

    public function getOptionsBacklinksType()
    {
        $value = $this->getOptionValue('backlinks_type', 'all');

        // 'my' is still in the user-data
        if ($value == 'my') {
            $value = 'my-alerts';
        }

        return $value;
    }

    public function setOptionsBacklinksType($value)
    {
        $this->setOptionValue('backlinks_type', $value);

        return $this;
    }

    public function getOptionsShowDashboardTour()
    {
        return $this->getOptionValue('show_dashboard_tour', true);
    }

    public function setOptionsShowDashboardTour($value)
    {
        $this->setOptionValue('show_dashboard_tour', $value);

        return $this;
    }

    private function getOptionValue($key, $default = '')
    {
        $options = $this->getOptions();
        if (!isset($options[$key])) {
            return $default;
        }

        return $options[$key];
    }

    private function setOptionValue($key, $value)
    {
        $options = $this->getOptions();
        $options[$key] = $value;
        $this->setOptions($options);

        return $this;
    }

    /**
     * Add competitors
     *
     * @param \Pool\LinkmotorBundle\Entity\Competitor $competitors
     * @return User
     */
    public function addCompetitor(Competitor $competitors)
    {
        $this->competitors[] = $competitors;
    
        return $this;
    }

    /**
     * Remove competitors
     *
     * @param \Pool\LinkmotorBundle\Entity\Competitor $competitors
     */
    public function removeCompetitor(Competitor $competitors)
    {
        $this->competitors->removeElement($competitors);
    }

    /**
     * Get competitors
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCompetitors()
    {
        return $this->competitors;
    }

    /**
     * Add keywords
     *
     * @param \Pool\LinkmotorBundle\Entity\Keyword $keywords
     * @return User
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

    /**
     * Add actionStats
     *
     * @param \Pool\LinkmotorBundle\Entity\ActionStats $actionStats
     * @return User
     */
    public function addActionStat(ActionStats $actionStats)
    {
        $this->actionStats[] = $actionStats;
    
        return $this;
    }

    /**
     * Remove actionStats
     *
     * @param \Pool\LinkmotorBundle\Entity\ActionStats $actionStats
     */
    public function removeActionStat(ActionStats $actionStats)
    {
        $this->actionStats->removeElement($actionStats);
    }

    /**
     * Get actionStats
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getActionStats()
    {
        return $this->actionStats;
    }

    /**
     * Add forgotPasswordTokens
     *
     * @param \Pool\LinkmotorBundle\Entity\ForgotPasswordToken $forgotPasswordTokens
     * @return User
     */
    public function addForgotPasswordToken(ForgotPasswordToken $forgotPasswordTokens)
    {
        $this->forgotPasswordTokens[] = $forgotPasswordTokens;
    
        return $this;
    }

    /**
     * Remove forgotPasswordTokens
     *
     * @param \Pool\LinkmotorBundle\Entity\ForgotPasswordToken $forgotPasswordTokens
     */
    public function removeForgotPasswordToken(ForgotPasswordToken $forgotPasswordTokens)
    {
        $this->forgotPasswordTokens->removeElement($forgotPasswordTokens);
    }

    /**
     * Get forgotPasswordTokens
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getForgotPasswordTokens()
    {
        return $this->forgotPasswordTokens;
    }

    /**
     * Add imports
     *
     * @param \Pool\LinkmotorBundle\Entity\Import $imports
     * @return User
     */
    public function addImport(\Pool\LinkmotorBundle\Entity\Import $imports)
    {
        $this->imports[] = $imports;
    
        return $this;
    }

    /**
     * Remove imports
     *
     * @param \Pool\LinkmotorBundle\Entity\Import $imports
     */
    public function removeImport(\Pool\LinkmotorBundle\Entity\Import $imports)
    {
        $this->imports->removeElement($imports);
    }

    /**
     * Get imports
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getImports()
    {
        return $this->imports;
    }

    /**
     * Set inactive
     *
     * @param boolean $inactive
     * @return User
     */
    public function setInactive($inactive)
    {
        $this->inactive = $inactive;
    
        return $this;
    }

    /**
     * Get inactive
     *
     * @return boolean 
     */
    public function isInactive()
    {
        return $this->inactive;
    }

    public function getLocale()
    {
        return $this->getOptionValue('locale', 'de');
    }

    public function setLocale($value)
    {
        return $this->setOptionValue('locale', $value);
    }

    public function setTableOptionsPages($value)
    {
        return $this->setOptionValue('table_options_pages', $value);
    }

    public function getTableOptionsPages()
    {
        return $this->getTableOptions('pages');
    }

    public function setTableOptionsBacklinks($value)
    {
        return $this->setOptionValue('table_options_backlinks', $value);
    }

    public function getTableOptionsBacklinks()
    {
        return $this->getTableOptions('backlinks');
    }

    private function getTableOptions($which)
    {
        $maxIndexes = array('pages' => 14, 'backlinks' => 16);
        $maxIndex = $maxIndexes[$which];

        $options = $this->getOptionValue("table_options_{$which}", array());

        if (count($options) != $maxIndex) {
            for ($i = 1; $i <= $maxIndex; $i++) {
                if (!isset($options[$i])) {
                    $options[$i] = array('id' => $i, 'class' => '');
                }
            }
        }

        return $options;
    }
}
