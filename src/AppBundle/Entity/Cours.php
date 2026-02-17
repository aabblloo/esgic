<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="sf3_cours")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\CoursRepository")
 * @UniqueEntity(fields={"prof", "date", "debut"}, 
 *      errorPath="prof",
 *      message="Un cours existe déjà pour ce professeur à la même date et heure de début.")
 * 
 * @UniqueEntity(fields={"prof", "date", "fin"}, 
 *      errorPath="prof",
 *      message="Un cours existe déjà pour ce professeur à la même date et heure de fin.")
 * 
 * @UniqueEntity(fields={"classe", "date", "debut", "site"},
 *      errorPath="classe",
 *      message="Un cours existe déjà pour cette classe, date, site et heure de debut.")
 * 
 * @UniqueEntity(fields={"classe", "date", "fin", "site"},
 *      errorPath="classe",
 *      message="Un cours existe déjà pour cette classe, date, site et heure de fin.")
 * 
 * @ORM\HasLifecycleCallbacks
 * 
 */

class Cours {

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var \DateTime
     * @ORM\Column(name="date", type="date", nullable=true)
     * @Assert\NotBlank()
     */
    private $date;


    /**
    * @var \string
    * @ORM\Column(name="jour", type="string", nullable=true)
    */
    private $jour;

    /**
     * @var \DateTime
     * @ORM\Column(name="debut", type="time", nullable=true)
     * @Assert\NotBlank()
     */
    private $debut;

    /**
     * @var \DateTime
     * @ORM\Column(name="fin", type="time", nullable=true)
     * @Assert\NotBlank()
     */
    private $fin;

    /**
     * @var int
     * @ORM\Column(name="nbre_heure", type="smallint", options={"default":0}, nullable=true)
     */
    private $nbreHeure;

    /**
     * @ORM\Column(type="decimal", precision=6, scale=0, options={"default":0})
     */
    private $taux;

    /**
     * @ORM\ManyToOne(targetEntity="Professeur")
     * @Assert\NotBlank()
     */
    private $prof;

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
     * @ORM\ManyToOne(targetEntity="AnScolaire")
     * @Assert\NotBlank()
     */
    private $anScolaire;

    /**
     * @ORM\ManyToOne(targetEntity="User")
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Site")
     * @ORM\JoinColumn(onDelete="SET NULL")
     * @Assert\NotBlank()
     */
    private $site;

    public static function getMois() {
        return [
            'Janvier' => 1, 'Février' => 2, 'Mars' => 3, 'Avril' => 4,
            'Mai' => 5, 'Juin' => 6, 'Juillet' => 7, 'Août' => 8,
            'Septembre' => 9, 'Octobre' => 10, 'Novembre' => 11,
            'Décembre' => 12,
        ];
    }

    /**
     * Get id
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Cours
     */
    public function setDate($date) {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate() {
        return $this->date;
    }

    /**
    * Set jour
    *
    * @param \DateTime $jour
    *
    * @return EmploiTps
    */
    public function setJour($jour) {
        $this->jour = $jour;
            
        return $this;
    }
        
    /**
    * Get jour
    *
    * @return string
    */
    public function getJour() {
        return $this->jour;
    }

    /**
     * Set debut
     *
     * @param \DateTime $debut
     *
     * @return Cours
     */
    public function setDebut($debut) {
        $this->debut = $debut;

        return $this;
    }

    /**
     * Get debut
     *
     * @return \DateTime
     */
    public function getDebut() {
        return $this->debut;
    }

    /**
     * Set fin
     *
     * @param \DateTime $fin
     *
     * @return Cours
     */
    public function setFin($fin) {
        $this->fin = $fin;

        return $this;
    }

    /**
     * Get fin
     *
     * @return \DateTime
     */
    public function getFin() {
        return $this->fin;
    }

    /**
     * Set nbreHeure
     *
     * @param integer $nbreHeure
     *
     * @return Cours
     */
    public function setNbreHeure($nbreHeure) {
        $this->nbreHeure = $nbreHeure;

        return $this;
    }

    /**
     * Get nbreHeure
     *
     * @return integer
     */
    public function getNbreHeure() {
        return $this->nbreHeure;
    }

    /**
     * Set prof
     *
     * @param \AppBundle\Entity\Professeur $prof
     *
     * @return Cours
     */
    public function setProf(\AppBundle\Entity\Professeur $prof = null) {
        $this->prof = $prof;

        return $this;
    }

    /**
     * Get prof
     *
     * @return \AppBundle\Entity\Professeur
     */
    public function getProf() {
        return $this->prof;
    }

    /**
     * Set classe
     *
     * @param \AppBundle\Entity\Classe $classe
     *
     * @return Cours
     */
    public function setClasse(\AppBundle\Entity\Classe $classe = null) {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Get classe
     *
     * @return \AppBundle\Entity\Classe
     */
    public function getClasse() {
        return $this->classe;
    }

    /**
     * Set matiere
     *
     * @param \AppBundle\Entity\Matiere $matiere
     *
     * @return Cours
     */
    public function setMatiere(\AppBundle\Entity\Matiere $matiere = null) {
        $this->matiere = $matiere;

        return $this;
    }

    /**
     * Get matiere
     *
     * @return \AppBundle\Entity\Matiere
     */
    public function getMatiere() {
        return $this->matiere;
    }

    /**
     * Set anScolaire
     *
     * @param \AppBundle\Entity\AnScolaire $anScolaire
     *
     * @return Cours
     */
    public function setAnScolaire(\AppBundle\Entity\AnScolaire $anScolaire = null
    ) {
        $this->anScolaire = $anScolaire;

        return $this;
    }

    /**
     * Get anScolaire
     *
     * @return \AppBundle\Entity\AnScolaire
     */
    public function getAnScolaire() {
        return $this->anScolaire;
    }

    /**
     * Set taux
     *
     * @param string $taux
     *
     * @return Cours
     */
    public function setTaux($taux) {
        $this->taux = $taux;

        return $this;
    }

    /**
     * Get taux
     *
     * @return string
     */
    public function getTaux() {
        return $this->taux;
    }


    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Cours
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set site
     *
     * @param \AppBundle\Entity\Site $site
     *
     * @return Cours
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


    /**
     * @ORM\PrePersist
     */
    public function setJourFromDate()
    {
        $jours = ['Mon' => 'lun', 'Tue' => 'mar', 'Wed' => 'mer', 'Thu' => 'jeu', 'Fri' => 'ven', 'Sat' => 'sam', 'Sun' => 'dim'];
        $this->jour = $jours[$this->date->format('D')];
    }
}
