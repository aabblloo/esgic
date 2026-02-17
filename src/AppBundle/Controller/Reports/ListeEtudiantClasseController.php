<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\Classe;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\Matiere;
use AppBundle\Entity\Professeur;
use AppBundle\Entity\Site;
use AppBundle\Repository\ProfesseurRepository;
use DateInterval;
use DatePeriod;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class ListeEtudiantClasseController extends Controller
{

    /**
     * @Route("/etat/liste_etudiants_par_classe", name="lst_etd_classe")
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createFormBuilder()
            ->setMethod('GET')
            ->add(
                'classe', EntityType::class, [
                    'class' => Classe::class,
                    'choice_value' => 'id',
                    'choice_label' => 'codeNom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new NotBlank(), new Valid()],
                ]
            )
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
            ->add(
                'lettre', ChoiceType::class, [
                    'choices' => EtudiantClasse::getLettres(),
                    'choice_label' => function ($choice) {
                        return $choice;
                    },
                    'placeholder' => '',
                    'required' => false,
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new Valid()],
                ]
            )
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'placeholder' => '',
                'constraints' => [new Valid()], 'required' => false,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => 'Sélectionnez un site']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $classe = $form->getData()['classe'];
            $anScolaire = $form->getData()['anScolaire'];
            $lettre = $form->getData()['lettre'];
            $site = $form->getData()['site'];
            $etudiants = $em->getRepository(Etudiant::class)->listeParClasse(
                $classe, $anScolaire, $lettre, $site
            );

            $vue = $this->renderView(
                'etat/liste_etudiant_classe.html.twig', [
                    'titre' => 'Liste des étudiants par classe',
                    'etudiants' => $etudiants,
                    'classe' => $classe,
                    'anScolaire' => $anScolaire,
                    'lettre' => $lettre,
                    'site' => $site,
                    'asset' => MyConfig::asset(),
                ]
            );

            return new Response($vue);

            /*
              $file    = 'liste_etudiants_par_classe_'.date('Y_m_d_His').'.pdf';
              $options = MyConfig::printOption();

              return new Response(
              $this->get('knp_snappy.pdf')
              ->getOutputFromHtml($vue, $options, true), 200, [
              'Content-Type'        => 'application/pdf',
              'Content-Disposition' => "inline; filename=\"{$file}\"",
              ]
              );
              // */
        }

        return $this->render(
            'etat/liste_etudiant_classe_form.html.twig', [
                'titre' => 'Etat - Liste des étudiants par classe',
                'form' => $form->createView(),
            ]
        );
    }

     /**
     * @Route("/etat/liste_hebdomadaire", name="lst_hebdo")
     */
    public function listHebdoAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $debut = new \DateTime();
        $fin = new \DateTime();

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
            ->add(
                'classe', EntityType::class, [
                    'class' => Classe::class,
                    'choice_value' => 'id',
                    'choice_label' => 'codeNom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new NotBlank(), new Valid()],
                ]
            )
            
            ->add(
                'lettre', ChoiceType::class, [
                    'choices' => EtudiantClasse::getLettres(),
                    'choice_label' => function ($choice) {
                        return $choice;
                    },
                    'placeholder' => '',
                    'required' => false,
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new Valid()],
                ]
            )
            ->add('debut', DateType::class, [
            'widget' => 'single_text',
            'data' => $debut,
            'constraints' => [new NotBlank()],
            ])
            ->add('fin', DateType::class, [
                'widget' => 'single_text',
                'data' => $fin,
                'constraints' => [new NotBlank()],
            ])
            ->add('periode', ChoiceType::class, [
                'label' => 'Période',
                'choices' => [
                    'Jour' => 'jour',
                    'Soir' => 'soir',
                ],
                'placeholder' => 'Sélectionnez une période',
                'required' => true,
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Sélectionnez une période',
                ],
                'constraints' => [new NotBlank()],
            ])

            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $classe = $form->getData()['classe'];
            $anScolaire = $form->getData()['anScolaire'];
            $lettre = $form->getData()['lettre'];
            $site = $form->getData()['site'];
            $debut= $form->getData()['debut'];
            $fin= $form->getData()['fin'];
            $periode= $form->getData()['periode'];
            $etudiants = $em->getRepository(Etudiant::class)->listeParClasse(
                $classe, $anScolaire, $lettre, $site
            );

            $dates = [];
            if ($debut && $fin) {
                $period = new DatePeriod(
                    $debut,
                    new DateInterval('P1D'), // 1 jour d'intervalle
                    $fin->modify('+1 day') // inclure la date de fin
                );

                foreach ($period as $date) {
                    $dates[] = $date;
                }
            }

            $vue = $this->renderView(
                'etat/liste_hebdomadaire.html.twig', [
                    'titre' => 'Liste hebdomadaire',
                    'etudiants' => $etudiants,
                    'classe' => $classe,
                    'anScolaire' => $anScolaire,
                    'lettre' => $lettre,
                    'debut'=>$debut,
                    'fin'=>$fin,
                    'dates' => $dates,
                    'periode'=>$periode,
                    'site' => $site,
                    'asset' => MyConfig::asset(),
                ]
            );

            return new Response($vue);
        }

        return $this->render(
            'etat/liste_hebdomadaire_form.html.twig', [
                'titre' => 'Etat - Liste hebdomadaire',
                'form' => $form->createView(),
            ]
        );
    }

     /**
     * @Route("/etat/liste_releve_notes", name="lst_releve_notes")
     */
    public function listReleveNotesAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $debut = new \DateTime();
        $fin = new \DateTime();

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
            ->add(
                'classe', EntityType::class, [
                    'class' => Classe::class,
                    'choice_value' => 'id',
                    'choice_label' => 'codeNom',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new NotBlank(), new Valid()],
                ]
            )
            
            ->add(
                'lettre', ChoiceType::class, [
                    'choices' => EtudiantClasse::getLettres(),
                    'choice_label' => function ($choice) {
                        return $choice;
                    },
                    'placeholder' => '',
                    'required' => false,
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new Valid()],
                ]
            )
            ->add(
                'epreuve', EntityType::class, [
                    'class' => Matiere::class,
                    'choice_value' => 'id',
                    'choice_label' => 'code',
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new NotBlank(), new Valid()],
                ]
            )
            ->add(
                'enseignant', EntityType::class, [
                    'class' => Professeur::class,
                    'choice_value' => 'id',
                    'choice_label' => function (Professeur $professeur) {
                        return $professeur->getPrenomNom().' ('.$professeur->getSpecialite().')';
                    },
                    'query_builder' => function (ProfesseurRepository $repo) {
                        return $repo->createQueryBuilder('p')
                            ->orderBy('p.nom', 'ASC')
                            ->addOrderBy('p.prenom', 'ASC');
                    },
                    'placeholder' => '',
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' => MyConfig::CHOSEN_TEXT,
                    ],
                    'constraints' => [new NotBlank(), new Valid()],
                ]
            )
            ->add('semestre', ChoiceType::class, [
                'label' => 'Semestre',
                'choices' => [
                    'S1' => 'S1',
                    'S2' => 'S2',
                    'S3' => 'S3',
                    'S4' => 'S4',
                    'S5' => 'S5',
                    'S6' => 'S6'
                ],
                'placeholder' => 'Sélectionnez un semestre',
                'required' => true,
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Sélectionnez un semestre',
                ],
                'constraints' => [new NotBlank()],
            ])
            ->add('typeExamen', ChoiceType::class, [
                'label' => 'Type d\'examen',
                'choices' => [
                    'Examen de fin de module ' => 'exam_fin',
                    'Rattrapage' => 'rattrapage',
                ],
                'placeholder' => 'Sélectionnez un type d\'examen',
                'required' => true,
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' => 'Sélectionnez un type d\'examen',
                ],
                'constraints' => [new NotBlank()],
            ])

            

            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anScolaire = $form->getData()['anScolaire'];
            $site = $form->getData()['site'];
            $classe = $form->getData()['classe'];
            $lettre = $form->getData()['lettre'];
            $epreuve= $form->getData()['epreuve'];
            $enseignant= $form->getData()['enseignant'];
            $semestre= $form->getData()['semestre'];
            $typeExamen= $form->getData()['typeExamen'];
            $etudiants = $em->getRepository(Etudiant::class)->listeParClasse(
                $classe, $anScolaire, $lettre, $site
            );

            $vue = $this->renderView(
                'etat/liste_releve_notes.html.twig', [
                    'titre' => 'Relevé des notes',
                    'etudiants' => $etudiants,
                    'anScolaire' => $anScolaire,
                    'site' => $site,
                    'classe' => $classe,
                    'lettre' => $lettre,
                    'epreuve' => $epreuve,
                    'enseignant' => $enseignant,
                    'semestre' => $semestre,
                    'typeExamen' => $typeExamen,
                    'asset' => MyConfig::asset(),
                ]
            );

            return new Response($vue);
        }

        return $this->render(
            'etat/liste_releve_notes_form.html.twig', [
                'titre' => 'Etat - relevé des notes',
                'form' => $form->createView(),
            ]
        );
    }

}
