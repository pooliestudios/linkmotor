<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserEditType extends AbstractType
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
            'newPassword',
            'text',
            array(
                'required' => false,
                'label' => 'New password',
                'mapped' => false,
                'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add(
            'admin',
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
            'inactive',
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
        return 'user_edit';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\User'));
    }
}
