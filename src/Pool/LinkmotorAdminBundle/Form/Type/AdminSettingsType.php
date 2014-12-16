<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class AdminSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('account_id', 'text', array(
                'required' => true,
                'label' => 'SEO-Services Account-ID',
                'attr' => array('class' => 'uk-form-width-large')
            ));
        $builder->add('account_secret_key', 'text', array(
                'required' => true,
                'label' => 'SEO-Services API-Key',
                'attr' => array('class' => 'uk-form-width-large')
            ));
        $builder->add('sistrix_active', 'checkbox', array(
            'required' => false,
            'label' => 'Use',
            'attr' => array(
                'class' => 'pool-toggle',
                'x-data-pool-toggle' => 'toggle-target-sistrix'
            )
        ));
        $builder->add('sistrix_api_key', 'text', array(
            'required' => false,
            'label' => 'API-Key',
            'attr' => array('class' => 'uk-form-width-large')
        ));

        $builder->add('xovi_active', 'checkbox', array(
            'required' => false,
            'label' => 'Use',
            'attr' => array(
                'class' => 'pool-toggle',
                'x-data-pool-toggle' => 'toggle-target-xovi'
            )
        ));
        $builder->add('xovi_api_key', 'text', array(
            'required' => false,
            'label' => 'API-Key',
            'attr' => array('class' => 'uk-form-width-large')
        ));
    }

    public function getName()
    {
        return 'admin_settings';
    }
}
