<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class KeywordAddType extends AbstractType
{
    private $nameField = 'nameEn';

    public function setLocale($locale)
    {
        if (strtolower($locale) == 'de') {
            $this->nameField = 'nameDe';
        } else {
            $this->nameField = 'nameEn';
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getName() == 'keyword_add') {
            $builder->add(
                'keyword',
                'text',
                array(
                    'required' => true,
                    'label' => 'Keyword',
                    'attr' => array('class' => 'uk-form-width-large'),
                )
            );
        }
        $nameField = $this->nameField;
        $builder->add(
            'market',
            'entity',
            array(
                'label' => 'Market',
                'class' => 'PoolLinkmotorBundle:Market',
                'property' => $this->nameField,
                'required' => true,
                'multiple' => false,
                'expanded' => false,
                'query_builder' => function (EntityRepository $er) use ($nameField) {
                    return $er->createQueryBuilder('m')
                        ->orderBy('m.' . $nameField, 'ASC');
                }
            )
        );
        $builder->add(
            'importLimit',
            'choice',
            array(
                'required' => true,
                'label' => 'SERP Position',
                'choices' => array(
                    5 => 'Top 5',
                    10 => 'Top 10',
                    25 => 'Top 25',
                    50 => 'Top 50',
                    100 => 'Top 100'
                )
            )
        );
        $builder->add(
            'importInterval',
            'choice',
            array(
                'required' => true,
                'label' => 'Scheduled imports',
                'choices' => array(
                    '0' => 'No scheduled import',
                    '28' => 'Every 4 weeks',
                    '90' => 'Every 3 months',
                    '180' => 'Every 6 months'
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
                'required' => false,
                'empty_value' => 'All',
                'query_builder' => function (EntityRepository $er) {
                    return $er->getAllActiveNonSupportUsersQueryBuilder();
                }
            )
        );
    }

    public function getName()
    {
        return 'keyword_add';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\Keyword'));
    }
}
