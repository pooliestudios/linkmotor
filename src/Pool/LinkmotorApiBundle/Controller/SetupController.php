<?php

namespace Pool\LinkmotorApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;

class SetupController extends Controller
{
    /**
     * @Route("/update-setup/")
     * @Template("PoolLinkmotorAdminApi::default.plain.twig")
     */
    public function updateAction(Request $request)
    {
        $data = @json_decode($this->get('crypt')->decrypt($request->getContent()), true);

        $response = 'failed';
        if (is_array($data)) {
            $textFields = array(
                'account_type', 'limit_projects', 'limit_users', 'limit_prospects',
                'limit_backlinks', 'paid_credits', 'unpaid_credits',
                'credit_instant_purchase_limit', 'free_monthly_credits'
            );
            $dateFields = array('pro_account_until');
            $allowedFields = array_merge($textFields, $dateFields);

            $setupData = array();
            foreach ($allowedFields as $allowedField) {
                if (array_key_exists($allowedField, $data)) {
                    if (in_array($allowedField, $dateFields)) {
                        $setupData[$allowedField] = $data[$allowedField];
                    } else {
                        $setupData[$allowedField] = (int)$data[$allowedField];
                    }
                }
            }

            if ($setupData) {
                $this->get('linkmotor.options')->setAll($setupData);
                $response = 'ok';
            }
        }

        return array('data' => $response);
    }
}
