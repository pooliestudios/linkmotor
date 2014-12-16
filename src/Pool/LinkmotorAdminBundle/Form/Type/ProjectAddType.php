<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class ProjectAddType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'domainOrSubdomain',
            'text',
            array(
                'required' => true,
                'label' => 'Domain or Subdomain',
                'attr' => array('class' => 'uk-form-width-large')
            )
        );
        $builder->add(
            'competitorDomain',
            'text',
            array(
                'required' => false,
                'label' => 'Domain',
                'attr' => array('class' => 'uk-form-width-large')
            )
        );
        $builder->add(
            'keyword1',
            'text',
            array(
                'required' => false,
                'label' => '1. Keyword',
                'attr' => array('class' => 'uk-form-width-medium')
            )
        );
        $builder->add(
            'keyword2',
            'text',
            array(
                'required' => false,
                'label' => '2. Keyword',
                'attr' => array('class' => 'uk-form-width-medium')
            )
        );
        $builder->add(
            'keyword3',
            'text',
            array(
                'required' => false,
                'label' => '3. Keyword',
                'attr' => array('class' => 'uk-form-width-medium')
            )
        );
    }

    public function getName()
    {
        return 'projectAdd';
    }
}
