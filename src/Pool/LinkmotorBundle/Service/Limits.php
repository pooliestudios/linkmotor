<?php

namespace Pool\LinkmotorBundle\Service;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Pool\LinkmotorBundle\Entity\User;

class Limits
{
    /**
     * @var Options
     */
    private $options;

    /**
     * @var Registry
     */
    private $doctrine;
    private $em;

    private $selfHosted;
    private $accountType;
    private $proAccountUntil;

    public function __construct(Registry $doctrine, Options $options)
    {
        $this->doctrine = $doctrine;
        $this->em = $this->doctrine->getManager();

        $this->options = $options;

        $this->selfHosted = $this->options->get('self_hosted');
        $this->accountType = $this->options->get('account_type');
        $this->proAccountUntil = $this->options->get('pro_account_until');
    }

    public function isAvailable($feature)
    {
        switch ($feature) {
            case 'keyword-explorer':
            case 'competitor-explorer':
                return $this->isProAccount();
        }

        return true;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function userMayLogIn(User $user)
    {
        if (!$this->usersLimitOverstepped()) {
            return true;
        }

        $allowedUsers = $this->getSelectableUsers();
        foreach ($allowedUsers as $allowedUser) {
            if ($allowedUser->getId() == $user->getId()) {
                return true;
            }
        }

        return false;
    }

    public function limitReached()
    {
        if ($this->projectsLimitReached() || $this->usersLimitReached() || $this->prospectsLimitReached()) {
            return true;
        }

        return false;
    }

    public function limitOverstepped()
    {
        if ($this->projectsLimitOverstepped() || $this->usersLimitOverstepped() || $this->prospectsLimitOverstepped()) {
            return true;
        }

        return false;
    }

    public function projectsLimitReached()
    {
        return $this->tryLimit('project', 'reached');
    }

    public function projectsLimitOverstepped()
    {
        return $this->tryLimit('project', 'overstepped');
    }

    public function usersLimitReached()
    {
        return $this->tryLimit('user', 'reached');
    }

    public function usersLimitOverstepped()
    {
        return $this->tryLimit('user', 'overstepped');
    }

    public function prospectsLimitReached()
    {
        return $this->tryLimit('page', 'reached');
    }

    public function prospectsLimitOverstepped()
    {
        return $this->tryLimit('page', 'overstepped');
    }

    public function backlinksLimitReached()
    {
        return $this->tryLimit('backlink', 'reached');
    }

    public function backlinksLimitOverstepped()
    {
        return $this->tryLimit('backlink', 'overstepped');
    }

    public function getSelectableProjects()
    {
        return $this->doctrine
            ->getRepository('PoolLinkmotorBundle:Project')
            ->getSelectableProjects($this->options->get('limit_projects'));
    }

    public function getSelectableUsers()
    {
        return $this->doctrine
            ->getRepository('PoolLinkmotorBundle:User')
            ->getSelectableUsers($this->options->get('limit_users'));
    }

    private function tryLimit($which, $what)
    {
        if ($this->isProAccount() || $this->isSelfHosted()) {
            return false;
        }

        $repository = '';
        $option = '';

        switch ($which) {
            case 'project':
                $repository = 'PoolLinkmotorBundle:Project';
                $option = 'limit_projects';
                break;
            case 'user':
                $repository = 'PoolLinkmotorBundle:User';
                $option = 'limit_users';
                break;
            case 'page':
                $repository = 'PoolLinkmotorBundle:Page';
                $option = 'limit_prospects';
                break;
            case 'backlink':
                $repository = 'PoolLinkmotorBundle:Backlink';
                $option = 'limit_backlinks';
                break;
        }

        $result = false;
        if ($repository && $option) {
            $totalCount = $this->em->getRepository($repository)->getTotalCount();
            $limit = $this->options->get($option);
            if ($what == 'reached') {
                $result = $totalCount >= $limit;
            } else {
                $result = $totalCount > $limit;
            }
        }

        return $result;
    }

    public function isProAccount()
    {
        if ($this->accountType == 1) {
            return true;
        }

        if ($this->proAccountUntil >= date('Y-m-d')) {
            return true;
        }

        return false;
    }

    public function isSelfHosted()
    {
        return $this->selfHosted == 1;
    }
}
