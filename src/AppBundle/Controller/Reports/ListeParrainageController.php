<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\Classe;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\Site;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\Valid;
use Symfony\Component\Validator\Constraints\NotBlank;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SearchType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class ListeParrainageController extends Controller
{

    /**
     * @Route("/etat/liste_parrainage", name="lst_parrain")
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
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $anScolaire = $form->getData()['anScolaire'];
            $site = $form->getData()['site'];
            $etudiants = $em->getRepository(Etudiant::class)->listeParrainnage(
                $anScolaire, $site
            );

            $etudiantsParProfesseur = [];

            foreach ($etudiants as $etudiant) {
                $professeur = $etudiant->getProfesseur(); // Supposons qu'il y a une méthode getProfesseur()
                if (!isset($etudiantsParProfesseur[$professeur->getId()])) {
                    $etudiantsParProfesseur[$professeur->getId()] = [
                        'professeur' => $professeur,
                        'etudiants' => []
                    ];
                }
                $etudiantsParProfesseur[$professeur->getId()]['etudiants'][] = $etudiant;
            }

            $vue = $this->renderView(
                'etat/liste_parrainage.html.twig', [
                    'titre' => 'Liste des parrainage',
                    'etudiantsParProfesseur' => $etudiantsParProfesseur,
                    'anScolaire' => $anScolaire,
                    'site' => $site,
                    'asset' => MyConfig::asset(),
                ]
            );

            return new Response($vue);

        }

        return $this->render(
            'etat/liste_parrainage_form.html.twig', [
                'titre' => 'Etat - Liste des parrainage',
                'form' => $form->createView(),
            ]
        );
    }

}
