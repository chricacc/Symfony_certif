<?php
// src/OC/PlatformBundle/Purge/OldAdvertsPurge.php
namespace OC\PlatformBundle\Purge;
use Doctrine\ORM\EntityManager;
class OldAdvertsPurge
{
    /* l'entity manager injecté. */
    private $em;

    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
    * Supprime les annonces qui n'ont reçu aucune candidature et qui ont été modifiées il y a plus de nbDays jours.
    *
    * @param int $nbDays
    */
    public function purge($nbDays){
        // On teste d'abord que l'entity manager a bien été injecté. (dans l'absolu, il faudrait lever une exception sinon...)
        if($this->em != null){
            $advertsToDelete = $this->em->getRepository('OCPlatformBundle:Advert')->getOutdatedAdverts($nbDays);

            foreach ($advertsToDelete as $advert) {
                $this->em->remove($advert);
            }

            $this->em->flush();
        }
    
    }
}