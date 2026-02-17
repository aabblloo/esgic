<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="sf3_matiere")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MatiereRepository")
 * @UniqueEntity(fields="code", message="Ce code existe déjà.")
 */
class Matiere
{

    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=20, unique=true)
     * @Assert\NotBlank()
     */
    private $code;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $nom;
    
    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ClasseMatiere", mappedBy="matiere")
     */
    private $classeMatieres;
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->classeMatieres = new \Doctrine\Common\Collections\ArrayCollection();
    }

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
     * Set code
     *
     * @param string $code
     *
     * @return Matiere
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Matiere
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Add classeMatiere
     *
     * @param \AppBundle\Entity\ClasseMatiere $classeMatiere
     *
     * @return Matiere
     */
    public function addClasseMatiere(\AppBundle\Entity\ClasseMatiere $classeMatiere)
    {
        $this->classeMatieres[] = $classeMatiere;

        return $this;
    }

    /**
     * Remove classeMatiere
     *
     * @param \AppBundle\Entity\ClasseMatiere $classeMatiere
     */
    public function removeClasseMatiere(\AppBundle\Entity\ClasseMatiere $classeMatiere)
    {
        $this->classeMatieres->removeElement($classeMatiere);
    }

    /**
     * Get classeMatieres
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getClasseMatieres()
    {
        return $this->classeMatieres;
    }
}
