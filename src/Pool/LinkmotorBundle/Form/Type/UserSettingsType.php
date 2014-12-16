<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class UserSettingsType extends AbstractType
{
    protected $pathTypeChoices = array(
        'all' => 'All items',
        'my' => 'My new items'
    );

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'name',
            'text',
            array('required' => true, 'label' => 'Name', 'attr' => array('class' => 'uk-form-width-medium'))
        );

        $builder->add(
            'locale',
            'choice',
            array(
                'required' => true,
                'label' => 'Language',
                'choices' => array(
                    'de' => 'Deutsch',
                    'en' => 'English'
                ),
                'attr' => array('class' => 'uk-form-width-medium')
            )
        );

        $builder->add(
            'optionsDashboardType',
            'choice',
            array(
                'choices' => $this->pathTypeChoices,
                'required' => true,
                'label' => 'On dashboard'
            )
        );

        $builder->add(
            'optionsPagesType',
            'choice',
            array(
                'choices' => $this->pathTypeChoices,
                'required' => true,
                'label' => 'In pages'
            )
        );

        $builder->add(
            'optionsBacklinksType',
            'choice',
            array(
                'choices' => $this->pathTypeChoices,
                'required' => true,
                'label' => 'In backlinks'
            )
        );

        $builder->add(
            'itemsPerPage',
            'choice',
            array(
                'choices' => array(
                    15 => 15,
                    25 => 25,
                    50 => 50,
                    100 => 100
                ),
                'required' => true,
                'label' => 'Items per page'
            )
        );
    }

    public function getName()
    {
        return 'user_settings';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\User'));
    }
}
