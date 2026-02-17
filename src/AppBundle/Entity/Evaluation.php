<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Note
 *
 * @ORM\Table(name="sf3_evaluation", uniqueConstraints={
 *      @UniqueConstraint(name="unique_idx",
 *          columns={"an_scolaire_id", "periode_id", "classe_id", "matiere_id"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EvaluationRepository")
 * @UniqueEntity(fields={"anScolaire", "periode", "classe", "matiere"},
 *      errorPath="periode",
 *      message="Cette évaluation existe déjà.")
 */
class Evaluation
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
     * @ORM\ManyToOne(targetEntity="AnScolaire")
     * @Assert\NotBlank()
     */
    private $anScolaire;
    
    /**
     * @ORM\ManyToOne(targetEntity="Periode")
     * @Assert\NotBlank()
     */
    private $periode;
    
    /**
     * @ORM\ManyToOne(targetEntity="Classe")
     * @Assert\NotBlank()
     */
    private $classe;

    /**
     * @ORM\ManyToOne(targetEntity="Matiere")
     * @Assert\NotBlank()
     */
    private $matiere;
    
    /**
     * @ORM\OneToMany(targetEntity="Note", mappedBy="evaluation", cascade={"all"})
     */
    private $notes;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Site")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Assert\NotBlank()
     */
    private $site;
    
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
     * Set anScolaire
     *
     * @param AnScolaire $anScolaire
     *
     * @return Evaluation
     */
    public function setAnScolaire(AnScolaire $anScolaire = null)
    {
        $this->anScolaire = $anScolaire;

        return $this;
    }

    /**
     * Get anScolaire
     *
     * @return AnScolaire
     */
    public function getAnScolaire()
    {
        return $this->anScolaire;
    }

    /**
     * Set periode
     *
     * @param Periode $periode
     *
     * @return Evaluation
     */
    public function setPeriode(Periode $periode = null)
    {
        $this->periode = $periode;

        return $this;
    }

    /**
     * Get periode
     *
     * @return Periode
     */
    public function getPeriode()
    {
        return $this->periode;
    }

    /**
     * Set classe
     *
     * @param Classe $classe
     *
     * @return Evaluation
     */
    public function setClasse(Classe $classe = null)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return Classe
     */
    public function getClasse()
    {
        return $this->classe;
    }

    /**
     * Set matiere
     *
     * @param Matiere $matiere
     *
     * @return Evaluation
     */
    public function setMatiere(Matiere $matiere = null)
    {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return Matiere
     */
    public function getMatiere()
    {
        return $this->matiere;
    }
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->notes = new ArrayCollection();
    }

    /**
     * Add note
     *
     * @param Note $note
     *
     * @return Evaluation
     */
    public function addNote(Note $note)
    {
        $this->notes[] = $note;

        return $this;
    }

    /**
     * Remove note
     *
     * @param Note $note
     */
    public function removeNote(Note $note)
    {
        $this->notes->removeElement($note);
    }

    /**
     * Get notes
     *
     * @return Collection
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set site
     *
     * @param \AppBundle\Entity\Site $site
     *
     * @return Evaluation
     */
    public function setSite(\AppBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \AppBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }
}
