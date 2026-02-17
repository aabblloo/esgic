<?php

namespace AppBundle\Entity;

use DateTime;
use DateInterval;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
* @ORM\Table(name="sf3_emploi_tps")
* @ORM\Entity(repositoryClass="AppBundle\Repository\EmploiTpsRepository")
* @UniqueEntity(fields={"classe", "semaine", "jour", "debut", "site"},
*      errorPath="classe",
*      message="Un cours existe déjà pour cette classe, date, site et heure de debut.")
* 
* @UniqueEntity(fields={"classe", "semaine", "jour", "fin", "site"},
*      errorPath="classe",
*      message="Un cours existe déjà pour cette classe, date, site et heure de fin.")
*/
class EmploiTps {
    
    /**
    * @var int
    * @ORM\Column(name="id", type="integer")
    * @ORM\Id
    * @ORM\GeneratedValue(strategy="AUTO")
    */
    private $id;
    
    /**
    * @var \DateTime
    * @ORM\Column(name="jour", type="string", nullable=true)
    * @Assert\NotBlank()
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
    * @var int
    * @ORM\Column(name="semaine", type="smallint", nullable=true)
    */
    private $semaine;


    /**
    * @var int
    * @ORM\Column(name="annee", type="integer", nullable=true)
    */
    private $annee;

    /**
    * @var int
    * @ORM\Column(name="exam", type="integer", nullable=true)
    */
    private $exam;
    
    
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

    
    
    public static function getJourSemaine() {
        return [
            'Lundi' => 'lun', 'Mardi' => 'mar', 'Mercredi' =>'mer', 'Jeudi' => 'jeu',
            'Vendredi' => 'ven', 'Samedi' => 'sam', 'Dimanche' => 'dim'
        ];
    }
    
    public static function getMoisAnnee() {
        return [
            '1' => 'Janvier', '2' => 'Fevrier', '3' =>'Mars', '4' => 'Avril',
            '5' => 'Mai', '6' => 'Juin', '7' => 'Juillet', '8' => 'Aout', '9' => 'Septembre', '10' => 'Octobre','11' => 'Novembre', '12' => 'Decembre'
        ];
    }
    
    
    public static function getSemainesDeLAnnee(): array
    {
        // Active la locale française pour strftime
        setlocale(LC_TIME, 'fr_FR.UTF-8', 'fr_FR', 'french');
        $year = (int) (new \DateTime())->format('Y');
        
        // Début au lundi de la première semaine ISO de l'année
        $startDate = (new \DateTime())->setISODate($year, 1);
        
        // Fin au dimanche de la dernière semaine ISO (52 ou 53)
        $endDate = (new \DateTime())->setISODate($year, 53)->modify('sunday this week');
        if ((int)$endDate->format('Y') > $year) {
            $endDate = (new \DateTime())->setISODate($year, 52)->modify('sunday this week');
        }
        
        $weeks = [];
        $weekNumber = 1;
        $interval = new \DateInterval('P7D');
        
        while ($startDate <= $endDate) {
            $weekStart = clone $startDate;
            $weekEnd = (clone $weekStart)->modify('+6 days');


            // Format en français : "du 01 janvier au 07 janvier"
            $label = 'du ' . strftime('%d %B', $weekStart->getTimestamp()) . ' au ' . strftime('%d %B', $weekEnd->getTimestamp());
            //$label = 'du ' . $weekStart->format('d M') . ' au ' . $weekEnd->format('d M');
            $weeks[$weekNumber] = $label;
            
            $startDate->add($interval);
            $weekNumber++;
        }
        
        return $weeks;
    }
    
    
    public function getLibelleSemaine(): ?string
    {
        if (!$this->semaine) {
            return null;
        }

        // Active la locale française
        setlocale(LC_TIME, 'fr_FR.UTF-8');
        
        // Tu peux adapter l’année ici si tu l’as dans ton entité aussi
        $anne = $this->annee;
        
        $startOfWeek = (new DateTime())->setISODate((int)$anne, (int)$this->semaine);
        $endOfWeek = (clone $startOfWeek)->add(new DateInterval('P6D'));

        // Libellé au format français : "du 01 janvier au 07 janvier"
        $libelle = 'du ' . strftime('%d %B', $startOfWeek->getTimestamp()) . ' au ' . strftime('%d %B', $endOfWeek->getTimestamp());
        
        return $libelle;
    }

    public static function getSemainesDuMois(int $mois): array
    {
        setlocale(LC_TIME, 'fr_FR.UTF-8');

        $anne = (int) (new \DateTime())->format('Y');

        // Premier jour du mois
        $firstDay = new \DateTimeImmutable("$anne-$mois-01");
        // Lundi de la semaine du premier jour
        $startDate = $firstDay->modify('monday this week');

        // Dernier jour du mois
        $lastDay = $firstDay->modify('last day of this month');
        // Dimanche de la semaine du dernier jour
        $endDate = $lastDay->modify('sunday this week');

        $semaines = [];

        for ($date = $startDate; $date <= $endDate; $date = $date->modify('+1 day')) {
            $numSemaine = (int) $date->format('W'); // Numéro ISO
            $semaines[$numSemaine] = true;
        }

        return array_keys($semaines);
    }

    public static function getPeriodeMois(int $mois): string
    {
        $anne = (int)date('Y');
        $moisNoms = self::getMoisAnnee();

        // Premier jour du mois
        $premierJour = new \DateTime("$anne-$mois-01");

        // Borne basse : lundi de la semaine du premier jour du mois
        $dateDebut = (clone $premierJour)->modify('monday this week');

        // Borne haute : dimanche de la 4ème semaine à partir de la borne basse
        $dateFin = (clone $dateDebut)->modify('+27 days'); // 4 semaines = 28 jours, donc 27 jours à ajouter
        // Fonction format date JJ MMM
        $formatDate = function(\DateTime $d) use ($moisNoms) {
            $jour = $d->format('d');
            $moisNum = (int)$d->format('m');
            $moisTxt = $moisNoms[$moisNum] ?? $d->format('M');
            return $jour . ' ' . $moisTxt;
        };

        return "du " . $formatDate($dateDebut) . " au " . $formatDate($dateFin);
    }
    
    /**
    * Get id
    * @return integer
    */
    public function getId() {
        return $this->id;
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
    * @return EmploiTps
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
    * @return EmploiTps
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
    * @return EmploiTps
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
    * Set semaine
    *
    * @param integer $semaine
    *
    * @return EmploiTps
    */
    public function setSemaine($semaine) {
        $this->semaine = $semaine;
        
        return $this;
    }
    
    /**
    * Get semaine
    *
    * @return integer
    */
    public function getSemaine() {
        return $this->semaine;
    }


    /**
    * Set annee
    *
    * @param integer $annee
    *
    * @return EmploiTps
    */
    public function setAnnee($annee) {
        $this->annee = $annee;
        
        return $this;
    }
    
    /**
    * Get annee
    *
    * @return integer
    */
    public function getAnnee() {
        return $this->annee;
    }


    /**
    * Set exam
    *
    * @param integer $exam
    *
    * @return EmploiTps
    */
    public function setExam($exam) {
        $this->exam = $exam;
        
        return $this;
    }
    
    /**
    * Get exam
    *
    * @return integer
    */
    public function getExam() {
        return $this->exam;
    }
    
    /**
    * Set prof
    *
    * @param \AppBundle\Entity\Professeur $prof
    *
    * @return EmploiTps
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
    * @return EmploiTps
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
    * @return EmploiTps
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
    * @return EmploiTps
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
    * Set user
    *
    * @param \AppBundle\Entity\User $user
    *
    * @return EmploiTps
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
    * @return EmploiTps
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


    public function validateDisponibiliteProf(ExecutionContextInterface $context)
    {

        $em = $this->$this->getDoctrine()->getManager();
        // On récupère le repository
        $repository = $em->getRepository('AppBundle:EmploiTps');

        // Recherche d'un cours existant pour le même prof, même semaine et jour,
        // et dont la période chevauche la période du cours courant
        $coursExistant = $repository->findCoursConflitProf(
            $this->getProf(),
            $this->getSemaine(),
            $this->getJour(),
            $this->getDebut(),
            $this->getFin(),
            $this->getId() // à exclure si édition
        );

        if ($coursExistant) {
            $context->buildViolation(sprintf(
                '%s est déjà programmé sur le site %s (%s %s-%s)',
                $this->getProf()->getNom(),
                $coursExistant->getSite()->getNom(),
                $this->getJour(),
                $this->getDebut()->format('H:i'),
                $this->getFin()->format('H:i')
            ))
            ->atPath('prof')
            ->addViolation();
        }
    }
}
