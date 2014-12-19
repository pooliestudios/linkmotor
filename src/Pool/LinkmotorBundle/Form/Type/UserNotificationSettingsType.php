<?php
namespace Pool\LinkmotorBundle\Form\Type;

use Pool\LinkmotorBundle\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class UserNotificationSettingsType extends AbstractType
{
    protected $projects;

    protected $isAdmin;

    protected $adminChoice = array(
        1 => 'All',
        0 => 'Only mine'
    );

    protected $when = array(
        0 => 'At once',
        8 => 'Each morning',
        1 => 'Monday morning',
        2 => 'Tuesday morning',
        3 => 'Wednesday morning',
        4 => 'Thursday morning',
        5 => 'Friday morning',
        6 => 'Saturday morning',
        7 => 'Sunday morning'
    );

    public function setProjects($projects)
    {
        $this->projects = $projects;

        return $this;
    }

    public function setAdmin($value)
    {
        $this->isAdmin = $value;

        return $this;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->generateOne($builder);
        foreach ($this->projects as $project) {
            $this->generateOne($builder, $project);
        }
    }

    /**
     * @param FormBuilderInterface $builder
     * @param Project $project
     * @return FormBuilderInterface
     */
    protected function generateOne(FormBuilderInterface $builder, Project $project = null)
    {
        $projectId = $project ? $project->getId() : 0;
        $postfix = $project ? '-' . $projectId : '';

        if ($project) {
            $builder->add(
                'customSettings' . $postfix,
                'checkbox',
                array(
                    'required' => false,
                    'label' => 'customSettings',
                    'attr' => array(
                        'class' => 'pool-notification-settings-check',
                        'data-pool-project-id' => $projectId,
                        'data-pool-project-which' => 'custom'
                    )
                )
            );
        }

        $builder->add(
            'warningNotificationOn' . $postfix,
            'checkbox',
            array(
                'required' => false,
                'label' => 'warningNotificationOn',
                'attr' => array(
                    'class' => 'pool-notification-settings-check',
                    'data-pool-project-id' => $projectId,
                    'data-pool-project-which' => 'warning'
                )
            )
        );

        if ($this->isAdmin) {
            $builder->add(
                'warningFor' . $postfix,
                'choice',
                array(
                    'choices' => $this->adminChoice,
                    'required' => false, 'label' => 'warningFor',
                    'empty_value' => false
                )
            );
        }

        $builder->add(
            'warningWhen' . $postfix,
            'choice',
            array(
                'choices' => $this->when,
                'required' => false, 'label' => 'warningWhen',
                'empty_value' => false
            )
        );

        $builder->add(
            'errorNotificationOn' . $postfix,
            'checkbox',
            array(
                'required' => false,
                'label' => 'errorNotificationOn',
                'attr' => array(
                    'class' => 'pool-notification-settings-check',
                    'data-pool-project-id' => $projectId,
                    'data-pool-project-which' => 'error'
                )
            )
        );
        if ($this->isAdmin) {
            $builder->add(
                'errorFor' . $postfix,
                'choice',
                array(
                    'choices' => $this->adminChoice,
                    'required' => false, 'label' => 'errorFor',
                    'empty_value' => false
                )
            );
        }

        $builder->add(
            'errorWhen' . $postfix,
            'choice',
            array(
                'choices' => $this->when,
                'required' => false, 'label' => 'errorWhen',
                'empty_value' => false
            )
        );

        return $builder;
    }

    public function getName()
    {
        return 'user_notification_settings';
    }
}
