<?php

namespace Pool\LinkmotorApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class InfoController extends Controller
{
    /**
     * @Route("/systeminfo/")
     * @Template("PoolLinkmotorApi::default.plain.twig")
     */
    public function indexAction()
    {
        $data = array(
            'numProjects' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Project')->getTotalCount(),
            'numUsers' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:User')->getTotalCount(),
            'numPages' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Page')->getTotalCount(),
            'numBacklinks' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Backlink')->getTotalCount(),
            'numKeywords' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Keyword')->getTotalCount(),
            'numCompetitors' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Competitor')->getTotalCount(),
            'numBlacklistItems' => $this->getDoctrine()->getRepository('PoolLinkmotorBundle:Blacklist')->getTotalCount()
        );

        $data = json_encode($data);
        $data = $this->get('crypt')->encrypt($data, 'base64');

        return array('data' => $data);
    }
}
