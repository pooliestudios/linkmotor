<?php

namespace Pool\LinkmotorBundle\Twig;

use Pool\LinkmotorBundle\Entity\Backlink;

class ChangelogExtension extends \Twig_Extension
{
    protected $doctrine;

    public function __construct($doctrine)
    {
        $this->doctrine = $doctrine;
    }

    public function getName()
    {
        return 'changelog';
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('changelogValue', array($this, 'getChangelogValue')),
            new \Twig_SimpleFilter('changelogUser', array($this, 'getChangelogUser')),
        );
    }

    public function getChangelogValue($value, $field)
    {
        if ($field == 'title') {
            if ($value == 1) {
                return 'Mr.';
            } elseif ($value == 2) {
                return 'Ms.';
            } elseif ($value == 3) {
                return 'Company';
            }
        }
        if (is_array($value)) {
            if ($field == 'assignedTo') {
                $user = $this->getObject('User', $value['id']);
                return $user->getDisplayName();
            }
            if ($field == 'status') {
                $status = $this->getObject('Status', $value['id']);
                return $status->getName();
            }
            return '@todo.field: ' . $field;
        }

        if ($field == 'costType') {
            return 'Costs: ' . isset(Backlink::$costTypes[$value]) ? Backlink::$costTypes[$value] : $value;
        }
        if ($value instanceof \DateTime) {
            return $value->format('Y-m-d H:i');
        }
        if (is_object($value)) {
            return '@todo.object: ' . get_class($value);
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        return $value;
    }

    public function getChangelogUser($value)
    {
        $id = explode(':', $value);
        if (count($id) == 2) {
            $user = $this->getObject('User', $id[1]);
            if ($user) {
                return $user->getDisplayName();
            }
        }

        return $value;
    }

    public function getObject($entityName, $id)
    {
        return $this->doctrine->getRepository("PoolLinkmotorBundle:{$entityName}")->find($id);
    }
}
