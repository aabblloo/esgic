<?php

namespace AppBundle\Service;

use AppBundle\Entity\Paiement;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
//use Doctrine\ORM\EntityManager;
//use Symfony\Component\HttpFoundation\RequestStack;

class MyServices
{

    protected $requestStack;
    protected $em;
    protected $user;

    function __construct()
    {
        //$this->requestStack = $requestStack;
        //$this->em = $em;
    }

    public static function TestDepassementPaiement($em, Paiement $paiement, $oldMontant = 0)
    {
        $paiementsTotal = 0;

        $query = $em->createQuery('select sum(p.montant) as total from AppBundle\Entity\Paiement p '
            . 'where p.anScolaire = :annee and p.etudiant = :etudiant and p.nature = :nature ')
            ->setParameters([
                'annee' => $paiement->getAnScolaire(),
                'etudiant' => $paiement->getEtudiant(),
                'nature' => Paiement::getNatures()[0],
            ]);
        $res = $query->getOneOrNullResult();

        if ($res) $paiementsTotal = $res['total'];

        $ec = $paiement->getEtudiantClasse();
        $total = (int) $paiementsTotal + $paiement->getMontant() - $oldMontant;

        if ($ec->getMontant() < $total) {
            return true;
        }

        return false;
    }
}


