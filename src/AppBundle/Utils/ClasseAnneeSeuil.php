<?php

namespace AppBundle\Utils;

use Symfony\Component\Validator\Constraints as Assert;

class ClasseAnneeSeuil
{

    public $classe;

     /** @var string */
    public $typeSeuil = 'pourcentage'; // 'pourcentage' | 'montant'

     /** @var float|null */
    public $montantSeuil; // NOUVEAU

    /**
     * @Assert\NotBlank
     */
    public $annee;
    public $lettre;

    public $filiere;

    /**
     * @Assert\Choice(callback="getSeuils")
     */
    public $seuil;

    public $site;

    public static function getSeuils()
    {
        return [100, 80, 70, 60, 50];
    }

}
