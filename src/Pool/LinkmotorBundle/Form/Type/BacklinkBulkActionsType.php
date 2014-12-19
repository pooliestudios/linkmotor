<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class BacklinkBulkActionsType extends AbstractType
{
    protected $isAdmin = false;
    protected $userList;

    public function setAdmin($newValue)
    {
        $this->isAdmin = $newValue;
    }

    public function setUsers($users)
    {
        $this->userList = array();
        foreach ($users as $user) {
            $this->userList['user-' . $user->getId()] = $user->getDisplayName();
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $choices = array(
            'Set status' => array(
                'status-1' => 'Online',
                'status-2' => 'Offline'
            ),
            'Assign user' => $this->userList,
            'Hide until' => array(
                'hideuntil-1' => '1 week',
                'hideuntil-2' => '1 month',
                'hideuntil-3' => '3 months'
            )
        );

        if ($this->isAdmin) {
            $choices['Other actions'] = array(
                'delete-delete' => 'Delete'
            );
        }

        $builder->add('bulkAction', 'choice',
            array(
                'required' => false,
                'label' => 'Bulk actions',
                'mapped' => false,
                'empty_value' => 'Select bulk action...',
                'choices' => $choices
            )
        );
        $builder->add('bulkItems', 'hidden');
        $builder->add('bulkReturnToType', 'hidden');
    }

    public function getName()
    {
        return 'backlink_bulk_actions';
    }
}
