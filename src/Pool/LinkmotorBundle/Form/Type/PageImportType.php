<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class PageImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('delimiter', 'text', array('required' => true, 'label' => 'Delimiter', 'data' => ';'));
        $builder->add('enclosure', 'text', array('required' => false, 'label' => 'Enclosure', 'data' => '"'));
        $builder->add('file', 'file', array('required' => true, 'label' => 'CSV-File'));
    }

    public function getName()
    {
        return 'page_import';
    }
}
