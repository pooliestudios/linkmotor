<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Pool\LinkmotorBundle\Entity\Page;
use Pool\LinkmotorBundle\Entity\Status;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class PageEditType extends AbstractType
{
    /**
     * @var Status
     */
    protected $currentStatus = null;

    /**
     * @var bool
     */
    protected $loggedInUserMayEdit = false;

    /**
     * @var Page
     */
    protected $page = null;

    private $statusToNoEdit = array(6, 7);

    public function setPage(Page $page)
    {
        $this->page = $page;
    }

    public function setCurrentStatus(Status $status)
    {
        $this->currentStatus = $status;
    }

    /**
     * @param bool $value
     */
    public function setLoggedInUserMayEdit($value)
    {
        $this->loggedInUserMayEdit = $value;
    }

    /**
     * 1) Wenn eine Page Linked ist, darf ihr Status nicht bearbeitet werden.
     * 2) Wenn eine Page Offline ist, darf ihr Status nur bearbeitet werden, wenn sie keinen Backlink hat.
     * 3) Linked darf nicht zur Auswahl stehen.
     * 3) Offline darf nur in Fall (2) zur Auswahl stehen.
     *
     * @return bool
     */
    private function statusShouldBeEditable()
    {
        if ($this->page->getBacklinks()->count() == 0) {
            $this->statusToNoEdit = array(6); // Linked
        } else {
            $this->statusToNoEdit = array(6, 7); // Linked & Offline
        }

        return !in_array($this->currentStatus->getId(), $this->statusToNoEdit)
               && $this->loggedInUserMayEdit;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            'assignedTo',
            'entity',
            array(
                'class' => 'PoolLinkmotorBundle:User',
                'property' => 'displayName',
                'required' => true,
                'query_builder' => function (EntityRepository $er) {
                    return $er->getAllActiveNonSupportUsersQueryBuilder();
                }
            )
        );
        if ($this->statusShouldBeEditable()) {
            if ($this->page->getStatus()->getId() == 7) {
                $statusNotToDisplay = array(6);
            } else {
                $statusNotToDisplay = array(6, 7);
            }
            $builder->add(
                'status',
                'entity',
                array(
                    'class' => 'PoolLinkmotorBundle:Status',
                    'property' => 'name',
                    'required' => true,
                    'query_builder' => function (EntityRepository $er) use ($statusNotToDisplay) {
                        return $er->createQueryBuilder('s')
                            ->where('s.id NOT IN (' . implode(',', $statusNotToDisplay) . ')')
                            ->orderBy('s.sortOrder', 'ASC');
                    }
                )
            );
        }
    }

    public function getName()
    {
        return 'page_edit';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array('data_class' => 'Pool\LinkmotorBundle\Entity\Page'));
    }
}
