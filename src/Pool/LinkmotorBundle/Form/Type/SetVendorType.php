<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class SetVendorType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'vendor',
            'entity',
            array(
                'class' => 'PoolLinkmotorBundle:Vendor',
                'property' => 'displayNameWithEmail',
                'required' => false,
                'mapped' => false
            )
        );
        $builder->add(
            'newVendorEmail',
            'email',
            array('required' => false,'label' => 'E-Mail', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add(
            'newVendorName',
            'text',
            array('required' => false,'label' => 'Name', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add(
            'newVendorCompany',
            'text',
            array('required' => false,'label' => 'Company', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add(
            'newVendorPhone',
            'text',
            array('required' => false,'label' => 'Phone', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add(
            'newVendorStreet',
            'text',
            array('required' => false,'label' => 'Street', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add(
            'newVendorZipcode',
            'text',
            array('required' => false,'label' => 'Zipcode', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add(
            'newVendorCity',
            'text',
            array('required' => false,'label' => 'City', 'attr' => array('class' => 'uk-form-width-large'))
        );
        $builder->add('search', 'submit');
    }

    public function getName()
    {
        return 'set_vendor';
    }
}
