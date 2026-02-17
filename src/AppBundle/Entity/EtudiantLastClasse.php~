<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EtudiantLastClasse
 *
 * @ORM\Table(name="sf3_etudiant_last_classe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EtudiantLastClasseRepository")
 */
class EtudiantLastClasse
{
    // /**
    //  * @var int
    //  *
    //  * @ORM\Column(name="id", type="integer")
    //  * @ORM\Id
    //  * @ORM\GeneratedValue(strategy="AUTO")
    //  */
    // private $id;

    /**
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Etudiant")
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AnScolaire")
     */
    private $anScolaire;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Classe")
     */
    private $classe;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     */
    private $lettre;

    /**
     * Set lettre
     *
     * @param string $lettre
     *
     * @return EtudiantLastClasse
     */
    public function setLettre($lettre)
    {
        $this->lettre = $lettre;

        return $this;
    }

    /**
     * Get lettre
     *
     * @return string
     */
    public function getLettre()
    {
        return $this->lettre;
    }

    /**
     * Set etudiant
     *
     * @param \AppBundle\Entity\Etudiant $etudiant
     *
     * @return EtudiantLastClasse
     */
    public function setEtudiant(\AppBundle\Entity\Etudiant $etudiant)
    {
        $this->etudiant = $etudiant;

        return $this;
    }

    /**
     * Get etudiant
     *
     * @return \AppBundle\Entity\Etudiant
     */
    public function getEtudiant()
    {
        return $this->etudiant;
    }

    /**
     * Set anScolaire
     *
     * @param \AppBundle\Entity\AnScolaire $anScolaire
     *
     * @return EtudiantLastClasse
     */
    public function setAnScolaire(\AppBundle\Entity\AnScolaire $anScolaire = null)
    {
        $this->anScolaire = $anScolaire;

        return $this;
    }

    /**
     * Get anScolaire
     *
     * @return \AppBundle\Entity\AnScolaire
     */
    public function getAnScolaire()
    {
        return $this->anScolaire;
    }

    /**
     * Set classe
     *
     * @param \AppBundle\Entity\Classe $classe
     *
     * @return EtudiantLastClasse
     */
    public function setClasse(\AppBundle\Entity\Classe $classe = null)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return \AppBundle\Entity\Classe
     */
    public function getClasse()
    {
        return $this->classe;
    }
}
