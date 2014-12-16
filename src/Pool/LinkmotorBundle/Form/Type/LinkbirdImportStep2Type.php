<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkbirdImportStep2Type extends AbstractType
{
    private $users;
    private $projects;

    public function __construct($users, $projects)
    {
        $this->users = $users;
        $this->projects = $projects;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('project', 'choice', array(
            'required' => true,
            'label' => 'Linkbird project',
            'choices' => $this->projects,
            'mapped' => false
        ));

        $builder->add('userMappingEmpty', 'entity', array(
            'required' => true,
            'label' => 'Empty Linkbird user',
            'class' => 'PoolLinkmotorBundle:User',
            'property' => 'displayName',
            'mapped' => false
        ));

        foreach ($this->users as $idx => $user) {
            $builder->add('userMapping' . $idx, 'entity', array(
                'required' => true,
                'label' => 'Linkbird user: ' . $user,
                'class' => 'PoolLinkmotorBundle:User',
                'property' => 'displayName',
                'mapped' => false
            ));
        }
    }

    public function getName()
    {
        return 'linkbird_import_step2';
    }
}
