<?php

// src/OC/PlatformBundle/Controller/AdvertController.php

namespace OC\PlatformBundle\Controller;

use OC\PlatformBundle\Entity\Advert;
use OC\PlatformBundle\Entity\Image;
use OC\PlatformBundle\Entity\Application;
use OC\PlatformBundle\Entity\AdvertSkill;
use OC\PlatformBundle\Form\AdvertType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class AdvertController extends Controller
{
    public function indexAction($page) {
        
        if ($page < 1) {
            throw new NotFoundHttpException('Page "' . $page . '" inexistante.');
        }
        
        $em = $this->getDoctrine()->getManager();
        
        // On récupère le repository
        $repository = $em->getRepository('OCPlatformBundle:Advert');
        
        $nbAdvertsPerPage = 3;

        $advertList = $repository->getAdverts($page, $nbAdvertsPerPage);

        $outdatedAdverts = $repository->getOutdatedAdverts(5);

        $nbPages = ceil(count($advertList)/$nbAdvertsPerPage);

        if($page > $nbPages){
            throw new NotFoundHttpException('Page "' . $page . '" inexistante.');
        }
        
        // Mais pour l'instant, on ne fait qu'appeler le template
        return $this->render('OCPlatformBundle:Advert:index.html.twig', array(
          'currentPage' => $page, 
          'advertList' => $advertList,
          'outdatedAdverts' => $outdatedAdverts,
          'nbPages' => $nbPages));
    }
    
    public function viewAction($id) {
        $em = $this->getDoctrine()->getManager();
        
        // On récupère le repository
        $repository = $em->getRepository('OCPlatformBundle:Advert');
        
        // On récupère l'entité correspondante à l'id $id
        $advert = $repository->find($id);
        
        if (null === $advert) {
            throw new NotFoundHttpException("L'annonce d'id " . $id . " n'existe pas.");
        }
        
        $listApplications = $em->getRepository('OCPlatformBundle:Application')->findBy(array('advert' => $advert));
        
        $listAdvertSkills = $em->getRepository('OCPlatformBundle:AdvertSkill')->findBy(array('advert' => $advert));
        
        return $this->render('OCPlatformBundle:Advert:view.html.twig', array('advert' => $advert, 'listApplications' => $listApplications, 'listAdvertSkills' => $listAdvertSkills));
    }
    
    public function addAction(Request $request) {
        
        $advert = new Advert();

        $form = $this->get('form.factory')->create(new AdvertType, $advert);

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($advert);
            $em->flush();

            $request->getSession()->getFlashBag()->add('notice', 'Annonce bien enregistrée.');

            return $this->redirect($this->generateUrl('oc_platform_view', array('id' => $advert->getId())));
        }

        return $this->render('OCPlatformBundle:Advert:add.html.twig', array(
          'form' => $form->createView(),
        ));
    }
    
    public function editAction($id) {
        
        $em = $this->getDoctrine()->getManager();
        $advert = $em->getRepository('OCPlatformBundle:Advert')->find($id);
        
        if ($advert == null) {
            throw $this->createNotFoundException("L'annonce d'id " . $id . " n'existe pas.");
        }
        
        // TODO : gestion du formulaire + update
        
        return $this->render('OCPlatformBundle:Advert:edit.html.twig', array('advert' => $advert));
    }
    
    public function deleteAction($id, Request $request) {
        
        $em = $this->getDoctrine()->getManager();
        $advertRepository = $em->getRepository('OCPlatformBundle:Advert');
        $advert = $advertRepository->find($id);
        
        if ($advert == null) {
            throw $this->createNotFoundException("L'annonce d'id " . $id . " n'existe pas.");
        }
        
        if ($request->isMethod('POST')) {
            
            $advertRepository->remove($advert);

            $request->getSession()->getFlashBag()->add('info', 'Annonce bien supprimée.');
            
            return $this->redirect($this->generateUrl('oc_platform_home'));
        }
        
        // Si la requête est en GET, on affiche une page de confirmation avant de delete
        return $this->render('OCPlatformBundle:Advert:delete.html.twig', array(
        'advert' => $advert));
    }

    public function purgeAction(){
      $nbDays = 5;
      $purger = $this->get('oc_platform.advert_purger');
      $purger->purge($nbDays);

      return $this->redirect($this->generateUrl('oc_platform_home'));
    }
}
