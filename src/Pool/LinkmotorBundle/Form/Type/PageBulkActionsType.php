<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageBulkActionsType extends AbstractType
{
    protected $isAdmin = false;
    protected $userList;

    public function setIsAdmin($newValue)
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
                'status-1' => 'New',
                'status-2' => 'Relevant',
                'status-3' => 'Not relevant',
                'status-4' => '1. Contact',
                'status-5' => '2. Contact',
                'status-8' => 'In progress'
            ),
            'Assign user' => $this->userList
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
        return 'page_bulk_actions';
    }
}
