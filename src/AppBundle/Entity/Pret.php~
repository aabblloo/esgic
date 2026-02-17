<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Pret
 *
 * @ORM\Table(name="pret")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PretRepository")
 */
class Pret
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     * @Assert\NotBlank
     * @Assert\Date()
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="montant", type="decimal", precision=10, scale=2, options={"default":0.00})
     * @Assert\NotBlank
     */
    private $montant;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Professeur")
     * @Assert\NotBlank
     */
    private $professeur;

    /**
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\Remboursement", mappedBy="pret")
     */
    private $remboursements;

    public function getTotalRemboursement()
    {
        $total = 0;

        foreach ($this->remboursements as $remboursement) {
            $total += $remboursement->getMontant();
        }

        return $total;
    }

    public function isSolder()
    {
        if ($this->montant == $this->getTotalRemboursement()) {
            return true;
        }
        return false;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Pret
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set montant
     *
     * @param string $montant
     *
     * @return Pret
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
     * Set professeur
     *
     * @param \AppBundle\Entity\Professeur $professeur
     *
     * @return Pret
     */
    public function setProfesseur(\AppBundle\Entity\Professeur $professeur = null)
    {
        $this->professeur = $professeur;

        return $this;
    }

    /**
     * Get professeur
     *
     * @return \AppBundle\Entity\Professeur
     */
    public function getProfesseur()
    {
        return $this->professeur;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->remboursements = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add remboursement
     *
     * @param \AppBundle\Entity\Remboursement $remboursement
     *
     * @return Pret
     */
    public function addRemboursement(\AppBundle\Entity\Remboursement $remboursement)
    {
        $this->remboursements[] = $remboursement;

        return $this;
    }

    /**
     * Remove remboursement
     *
     * @param \AppBundle\Entity\Remboursement $remboursement
     */
    public function removeRemboursement(\AppBundle\Entity\Remboursement $remboursement)
    {
        $this->remboursements->removeElement($remboursement);
    }

    /**
     * Get remboursements
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRemboursements()
    {
        return $this->remboursements;
    }
}
