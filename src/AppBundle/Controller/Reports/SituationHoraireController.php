<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\Site;
use AppBundle\Entity\Cours;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\EmploiTps;
use AppBundle\Entity\AnScolaire;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;


class SituationHoraireController extends Controller
{

    /**
     * @Route("/etat/situation_horaire", name="sit_horaire")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add(
                'anScolaire', EntityType::class, [
                    'label' => 'Année scolaire',
                    'class' => AnScolaire::class,
                    'choice_value' => 'id',
                    'choice_label' => 'nom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new NotBlank(), new Valid()],
                ]
            )
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'placeholder' => '',
                'constraints' => [new Valid()], 'required' => false,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => 'Sélectionnez un site']
            ])
            ->add('mois', ChoiceType::class, [
                'choices' => array_flip(EmploiTps::getMoisAnnee()),
                'placeholder' => '',
                'constraints' => [new Valid()], 'required' => true,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => 'Sélectionnez un mois']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anScolaire = $form->getData()['anScolaire'];
            $site = $form->getData()['site'];
            $mois= $form->getData()['mois'];
            $annee = (int)(new \DateTime())->format('Y');
            $mois = (int) $mois;  // venant du formulaire

            // Dates du mois
            $firstDay = new \DateTimeImmutable("$annee-$mois-01");
            $lastDay = $firstDay->modify('last day of this month');

            // Étendre au lundi de la première semaine et dimanche de la dernière semaine (pour couvrir toutes les semaines complètes)
            $startDate = $firstDay->modify('monday this week');
            $endDate = $lastDay->modify('sunday this week');

            $semaines = EmploiTps::getSemainesDuMois($mois);

            $emplois = $this->getDoctrine()
                ->getRepository(EmploiTps::class)
                ->createQueryBuilder('e')
                ->Where('e.anScolaire = :anScolaire')
                ->andWhere('e.semaine IN (:semaines)')
                ->setParameter('anScolaire', $anScolaire)
                ->setParameter('semaines', $semaines);

            if ($site !== null) {
                $emplois->andWhere('e.site = :site')
                        ->setParameter('site', $site);
            }    
            
            $emplois = $emplois->getQuery()->getResult();

             // 2. Récupérer les cours exécutés (dans la table Cours)
            $coursRaw = $em->getRepository(Cours::class)->createQueryBuilder('c')
                ->Where('c.anScolaire = :anScolaire')
                ->andWhere('c.date BETWEEN :start AND :end')
                ->setParameters([
                    'anScolaire' => $anScolaire,
                    'start' => $startDate->format('Y-m-d'),
                    'end' => $endDate->format('Y-m-d'),
                ]);

                if ($site !== null) {
                    $coursRaw->andWhere('c.site = :site')
                            ->setParameter('site', $site);
                }

            $coursRaw = $coursRaw->getQuery()->getResult();



            // Cycles fixes
            $cycles = ['DUT', 'LICENCE', 'MASTER'];

            // Initialisation tableau des heures prévues par cycle et semaine (1..4)
            $prevues = [];
            foreach ($cycles as $cycle) {
                for ($i = 1; $i <= 4; $i++) {
                    $prevues[$cycle][$i] = 0;
                }
            }

            // Calcul des heures
            foreach ($emplois as $emploi) {
                $classeNom = $emploi->getClasse()->getCode();
                $cycle = $this->getCycleFromClasseNom($classeNom);

                if (!in_array($cycle, $cycles)) {
                    continue; // ignore si hors cycles voulus
                }

                $numSemaineMois = array_search($emploi->getSemaine(), $semaines);
                if ($numSemaineMois === false) {
                    continue; // semaine pas dans le mois
                }
                $numSemaineMois += 1; // pour avoir 1..4

                if ($numSemaineMois >= 1 && $numSemaineMois <= 4) {
                    $prevues[$cycle][$numSemaineMois] += $emploi->getNbreHeure();
                }
            }


            // Initialisation heures exécutées
            $executes = [];
            foreach ($cycles as $cycle) {
                for ($i = 1; $i <= 4; $i++) {
                    $executes[$cycle][$i] = 0;
                }
            }

            // Remplir heures exécutées
            foreach ($coursRaw as $cours) {
                $jour = $cours->getDate();
                $semaineNum = (int) $jour->format('W');

                $classeNom = $cours->getClasse()->getCode();

                $cycle = $this->getCycleFromClasseNom($classeNom);
                

                if (!in_array($cycle, $cycles)) {
                    continue;
                }

                $indexSemaine = array_search($semaineNum, $semaines);
                if ($indexSemaine === false) {
                    continue;
                }
                $indexSemaine++;
                if ($indexSemaine > 4) {
                    continue; // éviter erreurs
                }

                $executes[$cycle][$indexSemaine] += $cours->getNbreHeure();
            }

            $moisNoms = EmploiTps::getMoisAnnee();
            $numeroMois = (string)$mois; // par ex. '1', '2', etc.

            $moisTexte = $moisNoms[$numeroMois] ?? 'Mois inconnu';

            $periode= EmploiTps::getPeriodeMois($mois);


            $vue = $this->renderView(
                'etat/situation_horaire_list.html.twig', [
                    'titre' => 'Liste des parrainage',
                    'prevues' => $prevues,
                    'executes' => $executes,
                    'anScolaire' => $anScolaire,
                    'site' => $site,
                    'mois'=>$moisTexte,
                    'periode'=>$periode,
                    'asset' => MyConfig::asset(),
                ]
            );

            return new Response($vue);

        }

        return $this->render(
            'etat/situation_horaire_form.html.twig', [
                'titre' => 'Etat - Situation des Heures executées',
                'form' => $form->createView(),
            ]
        );
    }

    private function getCycleFromClasseNom(string $classeNom): string
    {
        $prefix = strtoupper(substr(trim($classeNom), 0, 1)); // récupère la première lettre

            switch ($prefix) {
            case 'M':
                return 'MASTER';
            case 'L':
                return 'LICENCE';
            case 'D':
                return 'DUT';
            default:
                return 'AUTRE';
        }
    }

}
