<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Table(name="sf3_etudiant_classe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EtudiantClasseRepository")
 * @UniqueEntity(fields={"etudiant","classe","anScolaire"},
 *     message="Cet étudiant, classe et année colaire sont déjà associés.")
 * @UniqueEntity(fields={"etudiant","anScolaire"},
 *     message="Cet étudiant et année scolaire sont déjà associés.")
 */
class EtudiantClasse
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0)
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $montant;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=0, options={"default":0})
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $fraisInscription;

    /**
     * @ORM\Column(type="string", length=1, nullable=true)
     * @Assert\NotBlank()
     * @Assert\Choice(callback="getLettres")
     */
    private $lettre;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Etudiant", inversedBy="etudiantClasses")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $etudiant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\AnScolaire")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $anScolaire;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Classe")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $classe;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Paiement", mappedBy="etudiantClasse")
     */
    private $paiements;

    public static function getLettres()
    {
        return ['A', 'B', 'C', 'D', 'E', 'F'];
    }

    // /**
    //  * @Assert\Callback
    //  */
    // public function validate(ExecutionContextInterface $context, $payload)
    // {
    //     if ($this->isTenue and $this->refTenue == '') {
    //         $context->buildViolation('La référence est obligatoire.')
    //             ->atPath('refTenue')
    //             ->addViolation();
    //     }

    //     if (!$this->isTenue and $this->refTenue) {
    //         $context->buildViolation('Si la référence est saisie, vous devez cochez Tenue payée')
    //             ->atPath('isTenue')
    //             ->addViolation();
    //     }
    // }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set montant
     *
     * @param string $montant
     *
     * @return EtudiantClasse
     */
    public function setMontant($montant)
    {
        $this->montant = $montant;

        return $this;
    }

    /**
     * Get montant
     *
     * @return string
     */
    public function getMontant()
    {
        return $this->montant;
    }

    /**
     * Set etudiant
     *
     * @param \AppBundle\Entity\Etudiant $etudiant
     *
     * @return EtudiantClasse
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
     * @return EtudiantClasse
     */
    public function setAnScolaire(\AppBundle\Entity\AnScolaire $anScolaire)
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
     * @return EtudiantClasse
     */
    public function setClasse(\AppBundle\Entity\Classe $classe)
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

    /**
     * Set lettre
     *
     * @param string $lettre
     *
     * @return EtudiantClasse
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

    public function getPaiementsTotal()
    {
        $total = 0;

        foreach ($this->paiements as $p) {
            if ($p->getNature() == Paiement::getNatures()[0]){
                $total += $p->getMontant();
            }
        }

        return $total;
    }

    public function isSolder()
    {
        if ($this->getPaiementsTotal() == $this->montant) {
            return true;
        }

        return false;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->paiements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add paiement
     *
     * @param \AppBundle\Entity\Paiement $paiement
     *
     * @return EtudiantClasse
     */
    public function addPaiement(\AppBundle\Entity\Paiement $paiement)
    {
        $this->paiements[] = $paiement;

        return $this;
    }

    /**
     * Remove paiement
     *
     * @param \AppBundle\Entity\Paiement $paiement
     */
    public function removePaiement(\AppBundle\Entity\Paiement $paiement)
    {
        $this->paiements->removeElement($paiement);
    }

    /**
     * Get paiements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPaiements()
    {
        return $this->paiements;
    }

    /**
     * Set fraisInscription
     *
     * @param string $fraisInscription
     *
     * @return EtudiantClasse
     */
    public function setFraisInscription($fraisInscription)
    {
        $this->fraisInscription = $fraisInscription;

        return $this;
    }

    /**
     * Get fraisInscription
     *
     * @return string
     */
    public function getFraisInscription()
    {
        return $this->fraisInscription;
    }
}
