<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'backlinkIgnorePosition',
            'choice',
            array(
                'required' => true,
                'label' => 'Ignore position for new backlinks?',
                'choices' => array(
                    1 => 'Yes',
                    0 => 'No'
                )
            )
        );
    }

    public function getName()
    {
        return 'projectSettings';
    }
}
