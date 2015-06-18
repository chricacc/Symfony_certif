<?php

namespace OC\CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    public function indexAction()
    {
    	$em = $this->getDoctrine()->getManager();
	    // On récupère le repository
	    $repository = $em->getRepository('OCPlatformBundle:Advert');

	    $advertList = $repository->getLastNAdverts(3);
        return $this->render('OCCoreBundle:Default:index.html.twig', array(
        	'name' => 'Visiteur',
        	'advertList' => $advertList));
    }

    public function contactAction(Request $request){
        $request->getSession()->getFlashBag()->add('errors', 'La page contact n\'existe pas encore...');
        return $this->redirect($this->generateUrl('oc_core_homepage'));
    }
}
