<?php

namespace AppBundle\Utils;

use Symfony\Component\Validator\Constraints as Assert;

class ClasseAnnee {

    /**
     * @Assert\NotBlank()
     */
    private $classe;

    /**
     * @Assert\NotBlank()
     */
    private $annee;
    
    private $lettre;

    private $site;

    function getClasse() {
        return $this->classe;
    }

    function getAnnee() {
        return $this->annee;
    }

    function getLettre() {
        return $this->lettre;
    }

    function setClasse($classe) {
        $this->classe = $classe;
    }

    function setAnnee($annee) {
        $this->annee = $annee;
    }

    function setLettre($lettre) {
        $this->lettre = $lettre;
    }

    public function getSite()
    {
        return $this->site;
    }

    public function setSite($site)
    {
        $this->site = $site;
    }

}
