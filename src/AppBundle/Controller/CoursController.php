<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Cours;
use AppBundle\Entity\Site;
use AppBundle\Form\CoursType;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Professeur;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Validator\Constraints\EqualTo;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;



use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

/**
* Cour controller.
* @Route("cours")
*/
class CoursController extends Controller
{
    
    /**
    * Lists all cour entities.
    * @Route("/", name="cours_index")
    * @Method({"GET", "POST"})
    */
    public function indexAction(Request $request)
    {
        //$debut = new \DateTime(date('01-m-Y'));
        $debut = new \DateTime();
        $fin = new \DateTime();
        $prof = null;
        $site=null;
        
        $form = $this->createFormBuilder()
        ->setMethod('GET')
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
                ->add('prof', EntityType::class, [
                    'class' => Professeur::class,
                    'choice_value' => 'id',
                    'choice_label' => 'prenomNom',
                    'placeholder' => '',
                    'required' => false,
                    'attr' => [
                        'class' => 'chosen-select',
                        'data-placeholder' =>
                        MyConfig::CHOSEN_TEXT,
                    ],
                    ])
                    ->add('site', EntityType::class, [
                        'label' => 'Site',
                        'class' => Site::class,
                        'placeholder' => '',
                        'required' => false,
                        'attr' => ['class' => 'chosen-select', 'data-placeholder' => MyConfig::CHOSEN_TEXT,]
                        ])
                        ->getForm();
                        $form->handleRequest($request);
                        
                        if ($form->isSubmitted() && $form->isValid()) {
                            $debut = $form->getData()['debut'];
                            $fin = $form->getData()['fin'];
                            $prof = $form->getData()['prof'];
                            $site = $form->getData()['site'];
                        }
                        
                        $em = $this->getDoctrine()->getManager();
                        $cours = [];
                        if (
                            $this->get('security.authorization_checker')->isGranted('ROLE_RESPONSABLE') or
                            $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or
                            $this->get('security.authorization_checker')->isGranted('ROLE_DIRECTEUR') or
                            $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
                            ) {
                                $cours = $em->getRepository('AppBundle:Cours')->findByPeriode($debut, $fin, $prof, null, $site);
                            } else {
                                $cours = $em->getRepository('AppBundle:Cours')->findByPeriode($debut, $fin, $prof, $this->getUser(), $site);
                            }
                            
                            $nbre_heure = 0;
                            foreach ($cours as $cour) {
                                $nbre_heure += $cour->getNbreHeure();
                            }
                            
                            return $this->render('cours/cours_index.html.twig', [
                                'titre' => 'Liste des cours',
                                'cours' => $cours,
                                'nbre_heure' => $nbre_heure,
                                'form' => $form->createView(),
                            ]);
                        }
                        
                        /**
                        * Creates a new cour entity.
                        * @Route("/ajouter", name="cours_new")
                        * @Method({"GET", "POST"})
                        */
                        public function newAction(Request $request)
                        {
                            $em = $this->getDoctrine()->getManager();
                            $cours = new Cours();
                            $cours->setUser($this->getUser());
                            $cours->setSite($this->getUser()->getSite());
                            $annee = $em->getRepository(AnScolaire::class)->getAnneeEnCours();
                            $cours->setAnScolaire($annee);
                            $form = $this->createForm(CoursType::class, $cours);
                            $form->handleRequest($request);
                            
                              if ($form->isSubmitted() && $form->isValid()) {
                                //die('okokokok');
                                $classe = $form->getData()->getClasse();
                                $matiere = $form->getData()->getMatiere();
                                $anScolaire = $form->getData()->getAnScolaire();
                                $site = $form->getData()->getSite();
                                $nbreHeure = $cours->getFin()->diff($cours->getDebut());
                                $totalNbHeure = $em->getRepository('AppBundle:Cours')->getNbHeureTotal($classe,$matiere, $anScolaire, $site)+$nbreHeure->h;
                                $nbHeurePrevue=  $em->getRepository('AppBundle:ClasseMatiere')->getNbHeurePrevue($classe,$matiere);
                                if($nbHeurePrevue==0 || $nbHeurePrevue>=$totalNbHeure){
                                    $cours->setNbreHeure($nbreHeure->h);
                                    $cours->setTaux($cours->getClasse()->getTaux());
                                    $em->persist($cours);
                                    $em->flush();
                                    $logs = new Logs($this->getUser(), 'Insert', "Cours Id: {$cours->getId()}");
                                    $em->persist($logs);
                                    $em->flush();
                                    $this->addFlash('success',
                                    "Le cours <b>{$cours->getMatiere()->getCode()}</b> "
                                    . "du <b>{$cours->getDate()->format('d-m-Y')}</b> "
                                    . "a été enregistré avec succès.");
                                    return $this->redirectToRoute('cours_new');
                                }
                                else{
                                    $this->addFlash('danger',
                                    "Le nombre d'heure de cours prevus pour  <b>{$cours->getMatiere()->getCode()}</b> "
                                    . "de la classe <b>{$cours->getClasse()->getCode()}</b> "
                                    . "est atteint.");
                                    return $this->redirectToRoute('cours_new');
                                }
                                
                                
                                
                            } 
                            
                            return $this->render('cours/cours_form.html.twig', [
                                'titre' => 'Enregistrer un cours',
                                'cours' => $cours,
                                'form' => $form->createView(),
                            ]);
                        }
                        
                        /**
                        * Displays a form to edit an existing cour entity.
                        * @Route("/modifier/{id}", name="cours_edit")
                        * @Method({"GET", "POST"})
                        */
                        public function editAction(Request $request, Cours $cours)
                        {
                            $form = $this->createForm(CoursType::class, $cours);
                            $form->handleRequest($request);
                            
                            if ($form->isSubmitted() && $form->isValid()) {
                                $em = $this->getDoctrine()->getManager();
                                $nbreHeure = $cours->getFin()->diff($cours->getDebut());
                                $cours->setNbreHeure($nbreHeure->h);
                                $cours->setTaux($cours->getClasse()->getTaux());
                                if (!$cours->getUser()) {
                                    $cours->setUser($this->getUser());
                                }
                                $logs = new Logs($this->getUser(), 'Update', "Cours Id: {$cours->getId()}");
                                $em->persist($logs);
                                $em->flush();
                                $this->addFlash('success', "Le cours <b>{$cours->getMatiere()->getCode()}</b> "
                                . "du <b>{$cours->getDate()->format('d-m-Y')}</b> "
                                . "a été modifié avec succès."
                            );
                            
                            return $this->redirectToRoute('cours_index');
                        }
                        
                        return $this->render('cours/cours_form.html.twig', [
                            'titre' => 'Modifier le cours',
                            'cour' => $cours,
                            'form' => $form->createView(),
                        ]);
                    }
                    
                    /**
                    * Deletes a cour entity.
                    * @Route("/supprimer{id}", name="cours_delete")
                    * @Method({"GET", "POST"})
                    */
                    public function deleteAction(Request $request, Cours $cours)
                    {
                        $form = $this->createFormBuilder()
                        ->add('id', HiddenType::class, [
                            'attr' => ['data' => $cours->getId()],
                            'constraints' => new EqualTo($cours->getId()),
                            ])
                            ->getForm();
                            $form->handleRequest($request);
                            
                            if ($form->isSubmitted() and $form->isValid()) {
                                $em = $this->getDoctrine()->getManager();
                                $em->remove($cours);
                                $logs = new Logs($this->getUser(), 'Delete', "Cours Id: {$cours->getId()}");
                                $em->persist($logs);
                                $em->flush();
                                $this->addFlash('success', "Le cours <b>{$cours->getMatiere()->getCode()}</b> "
                                . "du <b>{$cours->getDate()->format('d-m-Y')}</b> "
                                . "a été supprimé avec succès."
                            );
                            
                            return $this->redirectToRoute('cours_index');
                        }
                        
                        return $this->render('cours/cours_delete.html.twig', [
                            'titre' => 'Supprimer le cours',
                            'cours' => $cours,
                            'form' => $form->createView(),
                        ]);
                    }
                    
                    /**
                    * @Route("/export", name="export")
                    * @Method({"GET", "POST"})
                    */
                    public function exportToExcel(Request $request)
                    {
                        $data = json_decode($request->request->get('data'), true);
                        
                        $spreadsheet = new Spreadsheet();
                        $sheet = $spreadsheet->getActiveSheet();
                        // Populate the sheet with data
                        foreach ($data as $rowIndex => $row) {
                            foreach ($row as $columnIndex => $value) {
                                // Convert column index to letter (A, B, C, etc.)
                                $columnLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($columnIndex + 1);
                                $sheet->setCellValue($columnLetter . ($rowIndex + 1), $value);
                            }
                        }
                        $writer = new Xlsx($spreadsheet);
                        
                        // Create a temporary file
                        $tempFile = tempnam(sys_get_temp_dir(), 'phpexcel') . '.xlsx';
                        $writer->save($tempFile);
                        
                        // Send the file to the browser
                        return $this->file($tempFile, 'cours.xlsx')->deleteFileAfterSend(true);
                    }
                    
                }
