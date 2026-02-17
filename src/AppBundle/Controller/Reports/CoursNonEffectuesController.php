<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\EmploiTps;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\Site;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class CoursNonEffectuesController extends Controller {
    
    /**
    * @Route("/etat/cours_non_effectues", name="cours_non_effectues")
    */
    public function indexAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        
        $form = $this->createFormBuilder()
        ->add('anScolaire', EntityType::class, [
            'label' => 'Année scolaire',
            'class' => AnScolaire::class,
            'choice_value' => 'id',
            'choice_label' => 'nom',
            'placeholder' => '',
            'required' => true,
            'attr' => [
                'class' => 'chosen-select',
                'data-placeholder' => MyConfig::CHOSEN_TEXT,
            ],
            'constraints' => [new NotBlank(), new Valid()],
            ])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'required' => false,
                'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT,]
                ])
                ->getForm();
                $form->handleRequest($request);
                $coursNonEffectues = [];
                
                if ($form->isSubmitted() && $form->isValid()) {
                    $anScolaire = $form->getData()['anScolaire'];
                    $site = $form->getData()['site'];
                    
                    // Appel au repository
                    $coursNonEffectues = $em->getRepository(EmploiTps::class)
                    ->findCoursNonEffectues($site, $anScolaire);
                    
                    $vue = $this->renderView('etat/cours_non_effectues.html.twig', [
                        'titre' => 'Liste des cours non effectués',
                        'coursNonEffectues' => $coursNonEffectues,
                        'anScolaire' => $anScolaire,
                        'site' => $site,
                        'total' => 20,
                        'asset' => MyConfig::asset(),
                    ]);
                    
                    return new Response($vue);
                    
                }
                
                return $this->render('etat/cours_non_effectues_form.html.twig', [
                    'titre'=>"Etat - Liste des cours non effectués",
                    'form' => $form->createView(),
                ]);
            }
            
        }
