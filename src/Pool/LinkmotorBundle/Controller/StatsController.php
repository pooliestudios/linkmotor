<?php

namespace Pool\LinkmotorBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class StatsController extends BaseController
{
    /**
     * @Template()
     *
     * @param $chartId
     * @param $width
     * @param $height
     * @param null $project
     *
     * @return array
     */
    public function backlinksFollowAction($chartId, $width, $height, $project = null)
    {
        if (!$project) {
            $project = $this->getSelectedProject();
        }

        if (!$project) {
            return array();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $backlinkRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Backlink');

        $numBacklinksFollow = $backlinkRepository->getNumFollow($project);
        $numBacklinksNofollow = $backlinkRepository->getNumNofollow($project);

        return compact('chartId', 'width', 'height', 'numBacklinksFollow', 'numBacklinksNofollow');
    }

    /**
     * @Template()
     *
     * @param $chartId
     * @param $width
     * @param $height
     * @param null $project
     *
     * @return array
     */
    public function backlinksTypeAction($chartId, $width, $height, $project = null)
    {
        if (!$project) {
            $project = $this->getSelectedProject();
        }

        if (!$project) {
            return array();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $backlinkRepository = $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Backlink');

        $numBacklinksTypeText = $backlinkRepository->getNumTypeText($project);
        $numBacklinksTypeImage = $backlinkRepository->getNumTypeImage($project);

        return compact('chartId', 'width', 'height', 'numBacklinksTypeText', 'numBacklinksTypeImage');
    }

    /**
     * @Template()
     *
     * @param $chartId
     * @param $width
     * @param $height
     * @param null $project
     *
     * @return array
     */
    public function backlinksAuthorityAction($chartId, $width, $height, $project = null)
    {
        if (!$project) {
            $project = $this->getSelectedProject();
        }

        if (!$project) {
            return array();
        }

        if ($this->isLimitedProject($project)) {
            return $this->redirect($this->generateUrl('pool_linkmotor_limits_reached'));
        }

        $authoritySpread = $this->getDoctrine()
            ->getRepository('PoolLinkmotorBundle:Backlink')
            ->getAuthoritySpread($project);

        return compact('chartId', 'width', 'height', 'authoritySpread');
    }
}
