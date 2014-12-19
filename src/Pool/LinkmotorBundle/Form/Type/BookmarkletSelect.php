<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class BookmarkletSelect extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'project',
            'entity',
            array(
                'class' => 'PoolLinkmotorBundle:Project',
                'property' => 'name',
                'required' => true
            )
        );

        $builder->add(
            'url',
            'url',
            array(
                'required' => true,
                'label' => 'URL',
                'attr' => array('class' => 'uk-form-width-large')
            )
        );

        $builder->add(
            'whatToDo',
            'choice',
            array(
                'choices' => array(1 => 'Import as page', 2 => 'Search for backlinks'),
                'required' => true,
                'label' => 'What should be done?',
                'expanded' => true
            )
        );

    }

    public function getName()
    {
        return 'bookmarklet_select';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('validation_groups' => false));
    }
}
