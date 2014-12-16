<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array('required' => true, 'label' => 'Name', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add(
            'email',
            'email',
            array('required' => true,'label' => 'E-Mail', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add(
            'password',
            'text',
            array('required' => true, 'label' => 'Password', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add(
            'isAdmin',
            'choice',
            array(
                'choices' => array(
                    0 => 'No',
                    1 => 'Yes'
                ),
                'expanded' => false,
                'required' => true,
                'label' => 'Is Admin'
            )
        );
        $builder->add(
            'isInactive',
            'choice',
            array(
                'choices' => array(
                    0 => 'No',
                    1 => 'Yes'
                ),
                'expanded' => false,
                'required' => true,
                'label' => 'Is inactive'
            )
        );

    }

    public function getName()
    {
        return 'user_add';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\User'));
    }
}
