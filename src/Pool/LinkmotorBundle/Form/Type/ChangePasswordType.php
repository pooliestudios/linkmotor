<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'current',
            'password',
            array(
                'required' => true,
                'label' => 'Current password',
                'mapped' => false,
                'attr' => array('class' => 'uk-form-width-medium')
            )
        );

        $builder->add(
            'new',
            'password',
            array(
                'required' => true,
                'label' => 'New password',
                'mapped' => false,
                'attr' => array(
                    'help-inline' => '<small>At least 6 characters long</small>',
                    'class' => 'uk-form-width-medium'
                )
            )
        );

        $builder->add(
            'new2',
            'password',
            array(
                'required' => true,
                'label' => 'New password (repeated)',
                'mapped' => false,
                'attr' => array(
                    'help-inline' => '<small>At least 6 characters long</small>',
                    'class' => 'uk-form-width-medium'
                )
            )
        );
    }

    public function getName()
    {
        return 'change_password';
    }
}
