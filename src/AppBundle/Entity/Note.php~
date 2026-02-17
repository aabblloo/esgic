<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 * Note
 *
 * @ORM\Table(name="sf3_note", uniqueConstraints={
 *      @UniqueConstraint(name="unique_idx",
 *          columns={"evaluation_id", "etudiant_id"})
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\NoteRepository")
 * @ORM\HasLifecycleCallbacks
 * @UniqueEntity(fields={"evaluation","etudiant"},
 *      errorPath="etudiant",
 *      message="Les notes de cet étudiant existent déjà pour cette période.")
 */
class Note {

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="note_classe", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $noteClasse;

    /**
     * @var string
     *
     * @ORM\Column(name="note_compo", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $noteCompo;

    /**
     * @var int
     *
     * @ORM\Column(name="coeff", type="integer", nullable=true)
     */
    private $coeff;

    /**
     * @var string
     *
     * @ORM\Column(name="moyenne", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $moyenne;

    /**
     * @ORM\ManyToOne(targetEntity="Evaluation", inversedBy="notes")
     */
    private $evaluation;

    /**
     * @ORM\ManyToOne(targetEntity="Etudiant")
     */
    private $etudiant;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set noteClasse
     *
     * @param string $noteClasse
     *
     * @return Note
     */
    public function setNoteClasse($noteClasse) {
        $this->noteClasse = $noteClasse;

        return $this;
    }

    /**
     * Get noteClasse
     *
     * @return string
     */
    public function getNoteClasse() {
        return $this->noteClasse;
    }

    /**
     * Set noteCompo
     *
     * @param string $noteCompo
     *
     * @return Note
     */
    public function setNoteCompo($noteCompo) {
        $this->noteCompo = $noteCompo;

        return $this;
    }

    /**
     * Get noteCompo
     *
     * @return string
     */
    public function getNoteCompo() {
        return $this->noteCompo;
    }

    /**
     * Set coeff
     *
     * @param integer $coeff
     *
     * @return Note
     */
    public function setCoeff($coeff) {
        $this->coeff = $coeff;

        return $this;
    }

    /**
     * Get coeff
     *
     * @return integer
     */
    public function getCoeff() {
        return $this->coeff;
    }

    /**
     * Set moyenne
     *
     * @param string $moyenne
     *
     * @return Note
     */
    public function setMoyenne($moyenne) {
        $this->moyenne = $moyenne;

        return $this;
    }

    /**
     * Get moyenne
     *
     * @return string
     */
    public function getMoyenne() {
        return $this->moyenne;
    }

    /**
     * Set etudiant
     *
     * @param \AppBundle\Entity\Etudiant $etudiant
     *
     * @return Note
     */
    public function setEtudiant(\AppBundle\Entity\Etudiant $etudiant = null) {
        $this->etudiant = $etudiant;

        return $this;
    }

    /**
     * Get etudiant
     *
     * @return \AppBundle\Entity\Etudiant
     */
    public function getEtudiant() {
        return $this->etudiant;
    }

    /**
     * Set evaluation
     *
     * @param \AppBundle\Entity\Evaluation $evaluation
     *
     * @return Note
     */
    public function setEvaluation(\AppBundle\Entity\Evaluation $evaluation = null) {
        $this->evaluation = $evaluation;

        return $this;
    }

    /**
     * Get evaluation
     *
     * @return \AppBundle\Entity\Evaluation
     */
    public function getEvaluation() {
        return $this->evaluation;
    }

    /**
     * @ORM\PrePersist
     * @ORM\PreUpdate
     */
    public function calculerMoyenne() {
        if ($this->noteClasse && $this->noteCompo) {
            $this->moyenne = (($this->noteClasse + ($this->noteCompo * 2)) / 3) * $this->coeff;
        } else {
            $this->moyenne = null;
        }
    }

}
