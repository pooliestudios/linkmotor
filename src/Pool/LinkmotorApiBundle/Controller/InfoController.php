<?php

namespace Pool\LinkmotorApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class InfoController extends Controller
{
    /**
     * @Route("/systeminfo/")
     * @Method("GET")
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

        return $this->render('@PoolLinkmotorApi/default.plain.twig', array('data' => $data));
    }
}
