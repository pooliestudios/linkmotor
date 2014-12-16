<?php

namespace Pool\LinkmotorBundle\Twig;

use Pool\LinkmotorBundle\Service\Options;
use Symfony\Component\Intl\Intl;

class HelperExtension extends \Twig_Extension
{
    protected $doctrine;

    /**
     * @var \Pool\LinkmotorBundle\Service\Options
     */
    protected $options;

    protected $translator;

    /**
     * @var \Pool\LinkmotorBundle\Service\Limits
     */
    protected $limits;

    public function __construct(Options $options = null, $doctrine = null, $translator = null, $limits = null)
    {
        $this->options = $options;
        $this->doctrine = $doctrine;
        $this->translator = $translator;
        $this->limits = $limits;
    }

    public function getName()
    {
        return 'helper';
    }

    public function getFunctions()
    {
        return array(
            'valueOrSpinner' => new \Twig_Function_Function(
                array($this, 'valueOrSpinnerFunction'),
                array('is_safe' => array('html')
                )
            ),
            'valueOrSpinnerOrInfo' => new \Twig_Function_Function(
                array($this, 'valueOrSpinnerOrInfoFunction'),
                array('is_safe' => array('html')
                )
            ),
            'featureActive' => new \Twig_Function_Function(
                array($this, 'featureActiveFunction')
            ),
            'accountLimitsReached' => new \Twig_Function_Function(
                array($this, 'accountLimitsReachedFunction')
            ),
            'accountLimitsOverstepped' => new \Twig_Function_Function(
                array($this, 'accountLimitsOversteppedFunction')
            ),
            'accountIncludes' => new \Twig_Function_Function(
                array($this, 'accountIncludesFunction')
            ),
            'accountOptions' => new \Twig_Function_Function(
                array($this, 'accountOptionsFunction')
            ),
            'proAccountActiveUntil' => new \Twig_Function_Function(
                array($this, 'proAccountActiveUntilFunction')
            ),
        );
    }

    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter('localeNumber', array($this, 'localeNumberFilter')),
            new \Twig_SimpleFilter('localeDate', array($this, 'localeDateFilter')),
            new \Twig_SimpleFilter('filterCsv', array($this, 'getFilterForCsv'))
        );
    }

    public function valueOrSpinnerFunction($value, $shouldNotBe = null)
    {
        if ($value === $shouldNotBe) {
            return '<i class="uk-icon-spinner uk-icon-spin"></i>';
        } else {
            return $value;
        }
    }

    public function valueOrSpinnerOrInfoFunction($which, $value, $shouldNotBe = null)
    {
        $usageAllowed =  $this->options->get("{$which}_active") && $this->options->get("{$which}_api_key");
        if ($usageAllowed) {
            return $this->valueOrSpinnerFunction($value, $shouldNotBe);
        }

        if ($value === $shouldNotBe) {
            if ($usageAllowed) {
                return '<i class="uk-icon-spinner uk-icon-spin"></i>';
            } else {
                $tooltip = 'This feature needs to be activated in the settings by your admin';
                $tooltip = $this->translator->trans($tooltip);

                return '<span data-uk-tooltip title="' . $tooltip . '"><i class="uk-icon-ban"></i></span>';
            }
        }

        return $value;
    }

    /**
     * @param string $feature
     * @return bool
     */
    public function featureActiveFunction($feature)
    {
        switch ($feature) {
            case 'sistrix':
                return $this->options->get("sistrix_active") && $this->options->get("sistrix_api_key");
            case 'xovi':
                return $this->options->get("xovi_active") && $this->options->get("xovi_api_key");
        }

        return false;
    }

    /**
     * @param string $option
     * @return mixed
     */
    public function accountOptionsFunction($option)
    {
        $value = $this->options->get($option);

        if ($option == 'pro_account_until' && $value < date('Y-m-d')) {
            return '';
        }

        return $value;
    }

    /**
     * @return \DateTime|null
     */
    public function proAccountActiveUntilFunction()
    {
        if ($this->options->get('pro_account_until')) {
            $proAccountUntil = $this->options->get('pro_account_until');
            if ($proAccountUntil >= date('Y-m-d')) {
                return new \DateTime($proAccountUntil);
            }
        }

        return new \DateTime('+30 days');
    }

    /**
     * @param string $type
     * @return bool
     */
    public function accountLimitsReachedFunction($type = null)
    {
        switch ($type) {
            case 'projects':
                $result = $this->limits->projectsLimitReached();
                break;
            case 'users':
                $result = $this->limits->usersLimitReached();
                break;
            case 'prospects':
                $result = $this->limits->prospectsLimitReached();
                break;
            case 'backlinks':
                $result = $this->limits->backlinksLimitReached();
                break;
            default:
                $result = $this->limits->limitReached();
        }

        return $result;
    }

    /**
     * @param string $type
     * @return bool
     */
    public function accountLimitsOversteppedFunction($type = null)
    {
        switch ($type) {
            case 'projects':
                $result = $this->limits->projectsLimitOverstepped();
                break;
            case 'users':
                $result = $this->limits->usersLimitOverstepped();
                break;
            case 'prospects':
                $result = $this->limits->prospectsLimitOverstepped();
                break;
            case 'backlinks':
                $result = $this->limits->backlinksLimitOverstepped();
                break;
            default:
                $result = $this->limits->limitOverstepped();
        }

        return $result;
    }

    public function accountIncludesFunction($feature)
    {
        return $this->limits->isAvailable($feature);
    }

    public function localeNumberFilter($value, $decimals = 0)
    {
        if ($value === null) {
            return null;
        }

        switch (\Locale::getDefault()) {
            case 'de':
                return number_format($value, $decimals, ',', '.');
            default:
                return number_format($value, $decimals, '.', ',');
        }
    }

    public function localeDateFilter(\DateTime $dateTime, $what = 'd')
    {
        $format = array();
        if (stripos($what, 'd') !== false) {
            switch (\Locale::getDefault()) {
                case 'de':
                    $format[] = 'd.m.Y';
                    break;
                default:
                    $format[] = 'Y-m-d';
            }
        }
        if (stripos($what, 't') !== false) {
            $format[] = 'H:i';
        }
        $format = implode(' ', $format);

        return $dateTime->format($format);
    }

    public function getFilterForCsv($filter, $which)
    {
        $output = array();
        if (isset($filter['keyword']) && $filter['keyword']) {
            $output[] = "Keyword={$filter['keyword']}";
        }
        if ($which == 'page') {
            if (isset($filter['status']) && $filter['status']) {
                $output[] = "Status={$filter['status']}";
            }
        } elseif ($which == 'backlink') {
            if (isset($filter['offline']) && $filter['offline'] !== null) {
                if ($filter['offline'] == '-') {
                    $label = 'All';
                } elseif ($filter['offline'] == 0) {
                    $label = 'Online';
                } elseif ($filter['offline'] == 1) {
                    $label = 'Offline';
                }
                $output[] = "Status={$label}";
            } elseif ($filter['backlinkStatus'] == 'alerts') {
                $label = 'Alerts';
                $output[] = "Status={$label}";
            }
        }
        if (isset($filter['domain']) && $filter['domain']) {
            $domain = $this->getObject('Domain', $filter['domain']);
            $output[] = "Domain={$domain->getName()}";
        }
        if (isset($filter['vendor']) && $filter['vendor']) {
            $vendor = $this->getObject('Vendor', $filter['vendor']);
            $output[] = "Vendor={$vendor->getEmail()}";
        }
        if (isset($filter['user']) && $filter['user']) {
            $user = $this->getObject('User', $filter['user']);
            $output[] = "User={$user->getUsername()}";
        }

        if (!$output) {
            return 'Filter: none';
        }

        return 'Filter: ' . implode(', ', $output);
    }

    public function getObject($entityName, $id)
    {
        return $this->doctrine->getRepository("PoolLinkmotorBundle:{$entityName}")->find($id);
    }
}
