<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Pool\LinkmotorBundle\Entity\Backlink;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\ORM\EntityRepository;

class BacklinkAddType extends AbstractType
{
    /**
     * @var bool
     */
    protected $loggedInUserMayEdit = false;

    /**
     * @param bool $value
     */
    public function setLoggedInUserMayEdit($value)
    {
        $this->loggedInUserMayEdit = $value;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->getName() == 'backlink_add') {
            $this->loggedInUserMayEdit = true;
            $builder->add(
                'pageUrl',
                'url',
                array(
                    'required' => true,
                    'label' => 'Page-URL',
                    'mapped' => false,
                    'attr' => array('class' => 'uk-form-width-large')
                )
            );
        }
        if ($this->loggedInUserMayEdit) {
            $builder->add(
                'url',
                'text',
                array('required' => true, 'label' => 'Target-URL', 'attr' => array('class' => 'uk-form-width-large'))
            );
        }

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

        if ($this->loggedInUserMayEdit) {
            $builder->add(
                'offline',
                'choice',
                array(
                    'choices' => array(true => 'Offline', false => 'Online'),
                    'required' => true,
                    'label' => 'Status'
                )
            );
            $builder->add(
                'crawlType',
                'choice',
                array(
                    'choices' => array('dom' => 'DOM', 'text' => 'Textmatching'),
                    'required' => true,
                    'label' => 'Crawl type',
                    'attr' => array('class' => 'crawltype-switcher')
                )
            );
            $builder->add(
                'anchor',
                'text',
                array('required' => false, 'label' => 'Anchor', 'attr' => array('class' => 'uk-form-width-large'))
            );
            $builder->add(
                'type',
                'choice',
                array(
                    'choices' => array('t' => 'Text', 'i' => 'Image'),
                    'required' => true,
                    'label' => 'Type'
                )
            );
            $builder->add(
                'follow',
                'choice',
                array(
                    'choices' => array(true => 'Yes', false => 'No'),
                    'required' => true,
                    'label' => 'Follow'
                )
            );
            if ($this->getName() != 'backlink_add') {
                $builder->add(
                    'statusCode',
                    'text',
                    array('required' => true, 'label' => 'Statuscode')
                );
                $builder->add(
                    'metaIndex',
                    'choice',
                    array(
                        'choices' => array(true => 'index', false => 'noindex'),
                        'required' => true,
                        'label' => 'Meta-Index'
                    )
                );
                $builder->add(
                    'metaFollow',
                    'choice',
                    array(
                        'choices' => array(true => 'follow', false => 'no-follow'),
                        'required' => true,
                        'label' => 'Meta-Follow'
                    )
                );
                $builder->add(
                    'xRobotsIndex',
                    'choice',
                    array(
                        'choices' => array(true => 'index', false => 'noindex'),
                        'required' => true,
                        'label' => 'X-Robots-Index'
                    )
                );
                $builder->add(
                    'xRobotsFollow',
                    'choice',
                    array(
                        'choices' => array(true => 'follow', false => 'no-follow'),
                        'required' => true,
                        'label' => 'X-Robots-Follow'
                    )
                );
                $builder->add(
                    'robotsGoogle',
                    'choice',
                    array(
                        'choices' => array(true => 'allow', false => 'disallow'),
                        'required' => true,
                        'label' => 'robots.txt (Google)'
                    )
                );
            }
            $builder->add(
                'ignorePosition',
                'choice',
                array(
                    'choices' => array(true => 'Yes', false => 'No'),
                    'required' => true,
                    'label' => 'Ignore position'
                )
            );
            $builder->add(
                'costType',
                'choice',
                array(
                    'choices' => Backlink::$costTypes,
                    'required' => true,
                    'label' => 'Costs',
                    'attr' => array('class' => 'backlink-cost-type')
                )
            );
            $builder->add('price', 'number', array('required' => false, 'label' => 'Price'));
            $builder->add(
                'costNote',
                'textarea',
                array(
                    'required' => false,
                    'label' => 'Return service',
                    'attr' => array('rows' => 10, 'class' => 'uk-form-width-large')
                )
            );
            $builder->add('extra1', 'text', array('required' => false, 'label' => 'Extra1'));
            $builder->add('extra2', 'text', array('required' => false, 'label' => 'Extra2'));
            $builder->add('extra3', 'text', array('required' => false, 'label' => 'Extra3'));
            $builder->add('extra4', 'text', array('required' => false, 'label' => 'Extra4'));
            $builder->add('extra5', 'text', array('required' => false, 'label' => 'Extra5'));
        }
    }

    public function getName()
    {
        return 'backlink_add';
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(
            array(
                'validation_groups' => array(
                    'Pool\LinkmotorBundle\Entity\Backlink',
                    'determineValidationGroups',
                ),
                'data_class' => 'Pool\LinkmotorBundle\Entity\Backlink'
            )
        );
    }
}
