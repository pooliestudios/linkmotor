<?php

namespace Pool\LinkmotorBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * NotificationSetting
 *
 * @ORM\Table(name="notification_settings")
 * @ORM\Entity(repositoryClass="Pool\LinkmotorBundle\Entity\NotificationSettingRepository")
 */
class NotificationSetting
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
     * @ORM\Column(name="warnings", type="boolean")
     */
    protected $warnings;

    /**
     * @ORM\Column(name="all_warnings", type="boolean")
     */
    protected $allWarnings;

    /**
     * @ORM\Column(name="warnings_when", type="integer")
     */
    protected $warningsWhen;

    /**
     * @ORM\Column(name="errors", type="boolean")
     */
    protected $errors;

    /**
     * @ORM\Column(name="all_errors", type="boolean")
     */
    protected $allErrors;

    /**
     * @ORM\Column(name="errors_when", type="integer")
     */
    protected $errorsWhen;

    /**
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="notificationSettings")
     * @ORM\JoinColumn(name="project_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="notificationSettings")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    protected $user;

    public function __construct()
    {
        $this->setErrors(true);
        $this->setWarnings(true);
        $this->setAllErrors(true);
        $this->setAllWarnings(true);
        $this->setWarningsWhen(1);
        $this->setErrorsWhen(1);
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

    public function toArray($id = 0)
    {
        $postfix = '';
        if ($id) {
            $postfix = "-{$id}";
        }

        $result = array();
        $result["warningNotificationOn{$postfix}"] = $this->getWarnings();
        $result["warningFor{$postfix}"] = $this->getAllWarnings() ? 1 : 0;
        $result["warningWhen{$postfix}"] = $this->getWarningsWhen();
        $result["errorNotificationOn{$postfix}"] = $this->getErrors();
        $result["errorFor{$postfix}"] = $this->getAllErrors() ? 1 : 0;
        $result["errorWhen{$postfix}"] = $this->getErrorsWhen();

        return $result;
    }

    /**
     * @param Alert $alert
     * @param int $when
     * @return bool
     */
    public function matchesAlert(Alert $alert, $when)
    {
        if ($alert->getProject() && $this->getProject() && !$alert->getProject()->equals($this->getProject())) {
            return false;
        }

        $matches = false;
        $isAdmin = $this->getUser() && $this->getUser()->isAdmin();
        $username = $this->getUser() ? $this->getUser()->getUsername() : '';
        $alertUsername = $alert->getUser() ? $alert->getUser()->getUsername() : '';
        if ($alert->getType() == 'w') {
            if ($this->getWarnings()) {
                if (!$this->getWarningsWhenMatches($when)) {
                    return false;
                }
                if (($this->getAllWarnings() && $isAdmin) || ($username == $alertUsername)) {
                    $matches = true;
                }
            }
        } else {
            if ($this->getErrors()) {
                if (!$this->getErrorsWhenMatches($when)) {
                    return false;
                }
                if (($this->getAllErrors() && $isAdmin) || ($username == $alertUsername)) {
                    $matches = true;
                }
            }
        }

        return $matches;
    }

    protected function getWarningsWhenMatches($when)
    {
        return $this->getWhenMatches($this->getWarningsWhen(), $when);
    }

    protected function getErrorsWhenMatches($when)
    {
        return $this->getWhenMatches($this->getErrorsWhen(), $when);
    }

    public function getWhenMatches($notificationWhen, $when)
    {
        if ($notificationWhen == $when) {
            return true;
        }

        if ($notificationWhen == 8 && in_array($when, array(1, 2, 3, 4, 5, 6, 7))) {
            return true;
        }

        return false;
    }

    /**
     * Set warnings
     *
     * @param boolean $warnings
     * @return NotificationSetting
     */
    public function setWarnings($warnings)
    {
        $this->warnings = $warnings;
    
        return $this;
    }

    /**
     * Get warnings
     *
     * @return boolean 
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * Set allWarnings
     *
     * @param boolean $allWarnings
     * @return NotificationSetting
     */
    public function setAllWarnings($allWarnings)
    {
        $this->allWarnings = $allWarnings;
    
        return $this;
    }

    /**
     * Get allWarnings
     *
     * @return boolean 
     */
    public function getAllWarnings()
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return false;
        }

        if (!$this->getWarnings()) {
            return false;
        }

        return $this->allWarnings;
    }

    /**
     * Set warningsWhen
     *
     * @param integer $warningsWhen
     * @return NotificationSetting
     */
    public function setWarningsWhen($warningsWhen)
    {
        $this->warningsWhen = $warningsWhen;
    
        return $this;
    }

    /**
     * Get warningsWhen
     *
     * @return integer 
     */
    public function getWarningsWhen()
    {
        return $this->warningsWhen;
    }

    /**
     * Set errors
     *
     * @param boolean $errors
     * @return NotificationSetting
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    
        return $this;
    }

    /**
     * Get errors
     *
     * @return boolean 
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Set allErrors
     *
     * @param boolean $allErrors
     * @return NotificationSetting
     */
    public function setAllErrors($allErrors)
    {
        $this->allErrors = $allErrors;
    
        return $this;
    }

    /**
     * Get allErrors
     *
     * @return boolean 
     */
    public function getAllErrors()
    {
        if (!$this->getUser() || !$this->getUser()->isAdmin()) {
            return false;
        }

        if (!$this->getErrors()) {
            return false;
        }

        return $this->allErrors;
    }

    /**
     * Set errorsWhen
     *
     * @param integer $errorsWhen
     * @return NotificationSetting
     */
    public function setErrorsWhen($errorsWhen)
    {
        $this->errorsWhen = $errorsWhen;
    
        return $this;
    }

    /**
     * Get errorsWhen
     *
     * @return integer 
     */
    public function getErrorsWhen()
    {
        return $this->errorsWhen;
    }

    /**
     * Set project
     *
     * @param \Pool\LinkmotorBundle\Entity\Project $project
     * @return NotificationSetting
     */
    public function setProject(\Pool\LinkmotorBundle\Entity\Project $project = null)
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
     * Set user
     *
     * @param \Pool\LinkmotorBundle\Entity\User $user
     * @return NotificationSetting
     */
    public function setUser(\Pool\LinkmotorBundle\Entity\User $user = null)
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
