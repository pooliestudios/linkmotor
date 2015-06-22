<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class VendorEditType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'title',
            'choice',
            array(
                'choices' => array(
                    3 => 'Company',
                    1 => 'Mr.',
                    2 => 'Ms.'
                ),
                'expanded' => false,
                'required' => true,
                'label' => 'Title'
            )
        );
        $builder->add(
            'name',
            'text',
            array('required' => false, 'label' => 'Name', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add(
            'email',
            'email',
            array('required' => true,'label' => 'E-Mail', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add('company', 'text', array(
                'required' => false,
                'label' => 'Company',
                'attr' => array('class' => 'uk-form-width-medium')
        ));
        $builder->add('position', 'text', array(
            'required' => false,
            'label' => 'Position',
            'attr' => array('class' => 'uk-form-width-medium')
        ));
        $builder->add('phone', 'text', array('required' => false, 'label' => 'Phone'));
        $builder->add(
            'street',
            'text',
            array('required' => false, 'label' => 'Street', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add('zipcode', 'text', array('required' => false, 'label' => 'Zipcode'));
        $builder->add(
            'city',
            'text',
            array('required' => false, 'label' => 'City', 'attr' => array('class' => 'uk-form-width-medium'))
        );
        $builder->add('country', 'country', array('required' => false, 'label' => 'Country'));
        $builder->add(
            'comment',
            'textarea',
            array(
                'required' => false,
                'label' => 'Comment',
                'attr' => array('rows' => 10, 'class' => 'uk-form-width-large')
            )
        );
    }

    public function getName()
    {
        return 'vendor_edit';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\Vendor'));
    }
}
