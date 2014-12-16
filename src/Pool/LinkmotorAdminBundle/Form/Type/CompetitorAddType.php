<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class CompetitorAddType extends AbstractType
{
    protected $domainName = '';

    public function setDomainName($domainName)
    {
        $this->domainName = $domainName;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getName() == 'competitor_add') {
            $builder->add(
                'domain',
                'text',
                array(
                    'required' => true,
                    'mapped' => false,
                    'label' => 'Domain',
                    'attr' => array('class' => 'uk-form-width-large', 'placeholder' => 'domain.tld'),
                    'data' => $this->domainName
                )
            );
        }
        $builder->add(
            'importLimit',
            'choice',
            array(
                'required' => true,
                'label' => 'Number of pages to import each time',
                'choices' => array(25 => 25, 50 => 50, 100 => 100, 200 => 200)
            )
        );
        $builder->add(
            'importInterval',
            'choice',
            array(
                'required' => true,
                'label' => 'Time between scheduled imports',
                'choices' => array(
                    '0' => 'No scheduled import',
                    '1' => '24 hours',
                    '7' => '1 week',
                    '30' => '1 month',
                    '90' => '3 months',
                    '180' => '6 months'
                )
            )
        );
        $builder->add(
            'assignedTo',
            'entity',
            array(
                'label' => 'User to assign pages from import (empty for spread on all users)',
                'class' => 'PoolLinkmotorBundle:User',
                'property' => 'displayName',
                'empty_value' => 'All',
                'required' => false,
                'query_builder' => function (EntityRepository $er) {
                    return $er->getAllActiveNonSupportUsersQueryBuilder();
                }
            )
        );
    }

    public function getName()
    {
        return 'competitor_add';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\Competitor'));
    }
}
