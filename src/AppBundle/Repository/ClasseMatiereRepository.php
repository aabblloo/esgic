<?php

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class ClasseMatiereRepository extends EntityRepository
{
    
    public function getNbHeurePrevue($classe, $matiere){
        //die($anScolaire->getNom(). $site->getNom());
        
        $query = $this->_em->createQueryBuilder('c')
        ->select('c.nbre_heure_prevue')
        ->from('AppBundle:ClasseMatiere', 'c') 
        ->where('c.classe = :classe')
        ->andWhere('c.matiere = :matiere')
        ->setParameter('classe', $classe)
        ->setParameter('matiere', $matiere)
        ->getQuery();
        try {
            return $query->getSingleScalarResult();
        } catch (\Doctrine\ORM\NoResultException $e) {
            return 0; // Handle the no result case, e.g., return 0 or a default value.
        }
    }
}
