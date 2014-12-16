<?php
namespace Pool\LinkmotorAdminBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;

class AccountType extends InvoiceInformationType
{
    private $withInvoiceInformation = true;
    private $isSelfHosted = false;
    private $basicLabel;

    public function setWithInvoiceInformation($value)
    {
        $this->withInvoiceInformation = $value;
    }

    public function setIsSelfHosted($value)
    {
        $this->isSelfHosted = $value;
        if ($this->isSelfHosted) {
            $this->basicLabel = 'Basic - Unlimited Users and Projects; No Explorer';
        } else {
            $this->basicLabel = 'Basic - 1 User, 1 Project; No Explorer';
        }
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->setIsSelfHosted($this->isSelfHosted);
        $builder->add('account_type', 'choice', array(
            'required' => true,
            'label' => 'Account Type',
            'choices' => array(
                0 => $this->basicLabel,
                1 => 'Pro - Unlimited Users, Unlimited Projects; Full access to Explorer'
            ),
            'expanded' => true
        ));

        if ($this->withInvoiceInformation) {
            parent::buildForm($builder, $options);
        }
    }

    public function getName()
    {
        return 'admin_account';
    }
}
