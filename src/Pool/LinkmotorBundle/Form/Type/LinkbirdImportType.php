<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class LinkbirdImportType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('file', 'file', array('required' => true, 'label' => '.XLSX-ExportFile from Linkbird'));
    }

    public function getName()
    {
        return 'linkbird_import';
    }
}
