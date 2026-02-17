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

class ListeEtudiantClassePasswordController extends Controller
{

    /**
     * @Route("/etat/liste_etudiants_par_classe_password", name="lst_etd_classe_password")
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
                'etat/liste_etudiant_classe_password.html.twig', [
                    'titre' => 'Liste des étudiants par classe avec mot de passe',
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
            'etat/liste_etudiant_classe_password_form.html.twig', [
                'titre' => 'Etat - Liste des étudiants par classe avec mot de passe',
                'form' => $form->createView(),
            ]
        );
    }

}
