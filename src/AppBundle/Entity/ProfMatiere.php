<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="sf3_prof_matiere")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ProfMatiereRepository")
 * @UniqueEntity(fields={"prof","matiere"},
 *     errorPath="matiere", message="Cette matière existe déjà pour ce professeur.")
 */
class ProfMatiere
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     * @Assert\Regex(
     *      pattern =   "#^[1-9]\d{3}$#",
     *      message =   "Le format n'est pas correcte."
     * )
     */
    private $debut;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Assert\Type(type="numeric")
     * @Assert\Regex(
     *      pattern =   "#^[1-9]\d{3}$#",
     *      message =   "Le format n'est pas correcte."
     * )
     */
    private $fin;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Professeur")
     * @ORM\JoinColumn(nullable=false)
     */
    private $prof;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Matiere")
     * @ORM\JoinColumn(nullable=false)
     * @Assert\NotBlank()
     */
    private $matiere;

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
     * Set debut
     *
     * @param integer $debut
     *
     * @return ProfMatiere
     */
    public function setDebut($debut)
    {
        $this->debut = $debut;

        return $this;
    }

    /**
     * Get debut
     *
     * @return integer
     */
    public function getDebut()
    {
        return $this->debut;
    }

    /**
     * Set fin
     *
     * @param integer $fin
     *
     * @return ProfMatiere
     */
    public function setFin($fin)
    {
        $this->fin = $fin;

        return $this;
    }

    /**
     * Get fin
     *
     * @return integer
     */
    public function getFin()
    {
        return $this->fin;
    }

    /**
     * Set prof
     *
     * @param \AppBundle\Entity\Professeur $prof
     *
     * @return ProfMatiere
     */
    public function setProf(\AppBundle\Entity\Professeur $prof)
    {
        $this->prof = $prof;

        return $this;
    }

    /**
     * Get prof
     *
     * @return \AppBundle\Entity\Professeur
     */
    public function getProf()
    {
        return $this->prof;
    }

    /**
     * Set matiere
     *
     * @param \AppBundle\Entity\Matiere $matiere
     *
     * @return ProfMatiere
     */
    public function setMatiere(\AppBundle\Entity\Matiere $matiere)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \AppBundle\Entity\Matiere
     */
    public function getMatiere()
    {
        return $this->matiere;
    }
}
