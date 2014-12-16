<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BlacklistAddType extends AbstractType
{
    protected $domainName = '';

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getName() == 'blacklist_add') {
            $builder->add(
                'domain',
                'text',
                array(
                    'required' => true,
                    'mapped' => false,
                    'label' => 'Domain',
                    'attr' => array('class' => 'uk-form-width-large'),
                    'data' => $this->domainName
                )
            );
        }
        $builder->add(
            'note',
            'textarea',
            array(
                'required' => false,
                'label' => 'Note',
                'attr' => array('class' => 'uk-form-width-large', 'rows' => 5)
            )
        );
    }

    public function getName()
    {
        return 'blacklist_add';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\Blacklist'));
    }
}
