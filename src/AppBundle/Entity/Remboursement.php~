<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Remboursement
 *
 * @ORM\Table(name="remboursement")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\RemboursementRepository")
 */
class Remboursement
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
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Pret", inversedBy="remboursements")
     * @ORM\JoinColumn(onDelete="CASCADE")
     * @Assert\NotBlank
     */
    private $pret;

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
     * @return Remboursement
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
     * @return Remboursement
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
     * Set pret
     *
     * @param \AppBundle\Entity\Pret $pret
     *
     * @return Remboursement
     */
    public function setPret(\AppBundle\Entity\Pret $pret = null)
    {
        $this->pret = $pret;

        return $this;
    }

    /**
     * Get pret
     *
     * @return \AppBundle\Entity\Pret
     */
    public function getPret()
    {
        return $this->pret;
    }
}
