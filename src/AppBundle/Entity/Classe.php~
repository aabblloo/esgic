<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Table(name="sf3_classe")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ClasseRepository")
 * @UniqueEntity(fields="code", message="Une classe existe déjà avec ce code.")
 */
class Classe
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
     * @ORM\Column(type="decimal", precision=6, scale=0, options={"default":0})
     * @Assert\NotBlank()
     * @Assert\Type(type="numeric")
     */
    private $taux;

    /**
     * @ORM\ManyToOne(targetEntity="Filiere")
     * @Assert\NotBlank()
     */
    private $filiere;

    public function getCodeNom()
    {
        return "{$this->code} - {$this->nom}";
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
     * @return Classe
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
     * @return Classe
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
     * Set taux
     *
     * @param string $taux
     *
     * @return Classe
     */
    public function setTaux($taux)
    {
        $this->taux = $taux;

        return $this;
    }

    /**
     * Get taux
     *
     * @return string
     */
    public function getTaux()
    {
        return $this->taux;
    }

    /**
     * Set filiere
     *
     * @param \AppBundle\Entity\Filiere $filiere
     *
     * @return Classe
     */
    public function setFiliere(\AppBundle\Entity\Filiere $filiere = null)
    {
        $this->filiere = $filiere;

        return $this;
    }

    /**
     * Get filiere
     *
     * @return \AppBundle\Entity\Filiere
     */
    public function getFiliere()
    {
        return $this->filiere;
    }
}
