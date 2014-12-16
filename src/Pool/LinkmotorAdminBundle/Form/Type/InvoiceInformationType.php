<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

class InvoiceInformationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('invoice_company', 'text', array(
            'required' => false,
            'label' => 'Company',
            'constraints' => array(new NotBlank()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
        $builder->add('invoice_tax_id', 'text', array(
            'required' => false,
            'label' => 'Tax-ID',
            'constraints' => array(new NotBlank()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
        $builder->add('invoice_name', 'text', array(
            'required' => false,
            'label' => 'Name',
            'constraints' => array(new NotBlank()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
        $builder->add('invoice_address', 'text', array(
            'required' => false,
            'label' => 'Address',
            'constraints' => array(new NotBlank()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
        $builder->add('invoice_zipcode', 'text', array(
            'required' => false,
            'label' => 'Zipcode',
            'constraints' => array(new NotBlank()),
        ));
        $builder->add('invoice_city', 'text', array(
            'required' => false,
            'label' => 'City',
            'constraints' => array(new NotBlank()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
        $builder->add('invoice_country', 'text', array(
            'required' => false,
            'label' => 'Country',
            'constraints' => array(new NotBlank()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
        $builder->add('invoice_email', 'text', array(
            'required' => false,
            'label' => 'E-Mail',
            'constraints' => array(new NotBlank(), new Email()),
            'attr' => array('class' => 'uk-form-width-large')
        ));
    }

    public function getName()
    {
        return 'admin_invoice_information';
    }
}
