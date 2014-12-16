<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ForgotPasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'email',
            'email',
            array(
                'required' => true,
                'label' => 'E-Mail',
                'attr' => array('class' => 'uk-form-width-medium')
            )
        );
    }

    public function getName()
    {
        return 'forgot_password';
    }
}
