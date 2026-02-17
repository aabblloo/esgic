<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Logs;
use AppBundle\Entity\Cours;
use AppBundle\Entity\Site;
use AppBundle\Form\CoursType;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Classe;
use AppBundle\Entity\EmploiTps;
use AppBundle\Entity\Professeur;
use AppBundle\Form\EmploiTpsType;
use DateTime;
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
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\DependencyInjection\Loader\Configurator\ref;

/**
* Cour controller.
* @Route("emploidutemps")
*/
class EmploiDuTempsController extends Controller
{
    
    /**
    * Lists all cour entities.
    * @Route("/", name="emploi_du_temps_index")
    * @Method({"GET", "POST"})
    */
    public function indexAction(Request $request)
    {
        
        $classe = null;
        $jour = null;
        $prof = null;
        $site=null;
        $anSco=null;
        $semaine=null;
        $em = $this->getDoctrine()->getManager();
        $anEncour = $em->getRepository(AnScolaire::class)->getAnneeEnCours();
        $anSco=$anEncour;
        
        $form = $this->createFormBuilder()
        ->setMethod('GET')
        ->add('classe', EntityType::class, [
            'class' => Classe::class,
            'choice_value' => 'id',
            'choice_label' => 'CodeNom',
            'placeholder' => '',
            'required' => false,
            'attr' => [
                'class' => 'chosen-select',
                'data-placeholder' =>
                MyConfig::CHOSEN_TEXT,
            ],
            ])
            ->add('anSco', EntityType::class, [
                'class' => AnScolaire::class,
                'choice_value' => 'id',
                'choice_label' => 'nom',
                'placeholder' => '',
                'required' => false,
                'attr' => [
                    'class' => 'chosen-select',
                    'data-placeholder' =>
                    MyConfig::CHOSEN_TEXT,
                ],
                'data' => $anEncour,
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
                    ->add('semaine',
                    ChoiceType::class, [
                        'choices' => array_flip(EmploiTps::getSemainesDeLAnnee()),
                        'required' => false, // Optional placeholder
                        'attr' => [
                            'class' => 'chosen-select',  // If you're using the Chosen.js library for styling
                            'data-placeholder' => MyConfig::CHOSEN_TEXT,  // Custom placeholder from your config
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
                                $prof = $form->getData()['prof'];
                                $site = $form->getData()['site'];
                                $anSco = $form->getData()['anSco'];
                                $classe = $form->getData()['classe'];
                                $semaine= $form->getData()['semaine'];
                                
                            }
                            
                            
                            $emploiTps = [];
                            if (
                                $this->get('security.authorization_checker')->isGranted('ROLE_RESPONSABLE') or
                                $this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or
                                $this->get('security.authorization_checker')->isGranted('ROLE_DIRECTEUR') or
                                $this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')
                                ) {
                                    // $criteria = [];
                                    // if (!empty($semaine)) $criteria['semaine'] = $semaine;
                                    // if (!empty($site))    $criteria['site'] = $site;
                                    // if (!empty($classe))  $criteria['classe'] = $classe;
                                    // if (!empty($anSco))   $criteria['anScolaire'] = $anSco;
                                    // if (!empty($jour))   $criteria['jour'] = $jour;
                                    //    $emploiTps = $em->getRepository('AppBundle:EmploiTps')->findBy($criteria);
                                    $emploiTps = $em->getRepository('AppBundle:EmploiTps')->findListByClasse($classe, $jour, $prof, $anSco,$semaine, null, $site);
                                } else {
                                    $emploiTps = $em->getRepository('AppBundle:EmploiTps')->findListByClasse($classe, $jour, $prof,$anSco, $semaine, $this->getUser(), $site);
                                }
                                
                                
                                foreach ($emploiTps as $item) {
                                    //var_dump($item);
                                    $key = $item->getClasse()->getId() . '-' . $item->getSite()->getId() . '-'.$item->getAnScolaire()->getId().'-'.$item->getSemaine();
                                    
                                    // Add to unique array only if not already present
                                    if (!isset($uniqueEmploiTps[$key])) {
                                        $uniqueEmploiTps[$key] = $item;
                                    }
                                }
                                if(isset($uniqueEmploiTps)){
                                    
                                    $emploiTps = array_values($uniqueEmploiTps);
                                }
                                
                                
                                
                                return $this->render('emploi_du_temps/emploi_du_temps_index.html.twig', [
                                    'titre' => 'Emploi du temps',
                                    'emploi_tps' => $emploiTps,
                                    'form' => $form->createView(),
                                ]);
                            }
                            
                            /**
                            * Creates a new cour entity.
                            * @Route("/ajouter", name="emploi_du_temps_new")
                            * @Method({"GET", "POST"})
                            */
                            public function newAction(Request $request)
                            {
                                $em = $this->getDoctrine()->getManager();
                                $emploiTps = new EmploiTps();
                                $emploiTps->setUser($this->getUser());
                                $emploiTps->setSite($this->getUser()->getSite());
                                $anneeSco = $em->getRepository(AnScolaire::class)->getAnneeEnCours();
                                $emploiTps->setAnScolaire($anneeSco);
                                $emploiTps->setAnnee((int) date('Y'));
                                $form = $this->createForm(EmploiTpsType::class, $emploiTps);
                                $form->handleRequest($request);
                                
                                
                                if ($form->isSubmitted() && $form->isValid()) {

                                     // 🔎 Vérification conflit professeur
                                    $conflits = $em->getRepository(EmploiTps::class)
                                        ->findCoursConflitProf(
                                            $emploiTps->getProf(),
                                            $emploiTps->getSemaine(),
                                            $emploiTps->getJour(),
                                            $emploiTps->getDebut()->format('H:i'),
                                            $emploiTps->getFin()->format('H:i'),
                                            $anneeSco,
                                            null // création → pas d’ID à exclure
                                        );
                                    if (count($conflits) > 0) {

                                        $edt = $conflits[0];

                                        $this->addFlash(
                                            'danger',
                                            "<b>{$emploiTps->getProf()->getPrenomNom()}</b> est déjà programmé "
                                            . "avec la classe <b>{$edt->getClasse()->getCode()}</b> "
                                            . "entre <b>{$edt->getDebut()->format('H:i')}</b> et "
                                            . "<b>{$edt->getFin()->format('H:i')}</b> "
                                            . "sur le site <b>{$edt->getSite()->getNom()}</b> "
                                            . "pour le cour <b>{$edt->getMatiere()->getNom()}</b>. "
                                        );

                                        return $this->redirect($request->headers->get('referer'));
                                    }
                                    $nbreHeure = $emploiTps->getFin()->diff($emploiTps->getDebut());
                                    $emploiTps->setNbreHeure($nbreHeure->h);
                                    $em->persist($emploiTps);
                                    $em->flush();
                                    $logs = new Logs($this->getUser(), 'Insert', "Emploi du temps Id: {$emploiTps->getId()}");
                                    $em->persist($logs);
                                    $em->flush();
                                    $this->addFlash('success',
                                    "L'emploi du temps de la classe <b>{$emploiTps->getClasse()->getCode()}</b> "
                                    . "du <b>{$emploiTps->getJour()}</b> "
                                    . "a été enregistré avec succès.");
                                    return $this->redirect($request->headers->get('referer'));
                                    
                                    //return $this->redirectToRoute('emploi_du_temps_new');
                                }
                                
                                return $this->render('emploi_du_temps/emploi_du_temps_form.html.twig', [
                                    'titre' => 'Enregistrer un emploi du temps',
                                    'emploiTps' => $emploiTps,
                                    'form' => $form->createView(),
                                ]);
                            }
                            
                            
                            /**
                            * get all emplois.
                            * @Route("/list", name="list_emploi")
                            * @Method({"GET", "POST"})
                            */
                            public function listEmplois(Request $request){
                                
                                $formData = $request->request->get('appbundle_emploi_du_temps'); 
                                $site = $formData['site'];
                                $anSco = $formData['anScolaire'];
                                $classe=$formData['classe'];
                                $semaine=$formData['semaine'];
                                
                                $em = $this->getDoctrine()->getManager();
                                $listEmploi = null;
                                $listEmploi = $em->getRepository('AppBundle:EmploiTps')->findByClasse($classe, null, null, $anSco,$semaine, null, $site);
                                //$listEmploi = $em->getRepository('AppBundle:EmploiTps')->findAll();
                                
                                $data=[];
                                
                                foreach($listEmploi as $l){
                                    $data [] = [
                                        'id' => $l->getId(),
                                        'site' => $l->getSite()->getNom(),
                                        'classe' => $l->getClasse()->getCode(),
                                        'anSco' => $l->getAnScolaire()->getNom(),
                                        'prof' => $l->getProf()->getPrenom()." ".$l->getProf()->getNom(),
                                        'matiere' => $l->getMatiere()->getCode(),
                                        'jour' => $l->getJour(),
                                        'semaine'=>$l->getSemaine(),
                                        'exam'=>$l->getExam(),
                                        'heure' => $l->getDebut()->format('H:i')." - ".$l->getFin()->format('H:i'),
                                    ];
                                }
                                
                                // Return JSON response
                                return $this->json($data);
                            }
                            
                            /**
                            * Displays a form to edit an existing cour entity.
                            * @Route("/modifier/{site}/{anSco}/{classe}/{semaine}", name="emploi_du_temps_edit")
                            * @Method({"GET", "POST"})
                            */
                            public function editAction(Request $request, $site, $anSco, $classe, $semaine)
                            {
                                
                                $em = $this->getDoctrine()->getManager();
                                
                                // Récupérer l'entité existante (vous pouvez le faire avec l'ID passé)
                                $emploiId = $request->request->get('emploi_id');
                                if ($emploiId) {
                                    $emploiTpsNew = $em->getRepository(EmploiTps::class)->find($emploiId);
                                    if($emploiTpsNew->getExam()==1){
                                        $emploiTpsNew->setExam(true);
                                    }else{
                                        $emploiTpsNew->setExam(false);
                                    }

                                    if (!$emploiTpsNew) {
                                        throw $this->createNotFoundException('Emploi du temps introuvable');
                                    }
                                }
                                else{
                                     $emploiTpsNew = new EmploiTps();
                                }

                               

                                
                                
                                $emploiTpsExisting = $em->getRepository('AppBundle:EmploiTps')->findOneBySiteAnCLasse($classe, $anSco, $site, $semaine);
                                
                                // Créer une nouvelle instance d'EmploiTps pour l'ajout
                               
                                
                                // Pré-remplir les valeurs de l'entrée existante dans la nouvelle instance
                                if($emploiTpsExisting!=null){
                                    
                                    $emploiTpsNew->setClasse($emploiTpsExisting->getClasse());
                                    $emploiTpsNew->setSite($emploiTpsExisting->getSite());
                                    $emploiTpsNew->setAnScolaire($emploiTpsExisting->getAnScolaire());
                                    $emploiTpsNew->setSemaine($emploiTpsExisting->getSemaine());
                                }

                                $form = $this->createForm(EmploiTpsType::class, $emploiTpsNew);
                                $form->handleRequest($request);
                                
                                if ($form->isSubmitted() && $form->isValid()) {

                                      // 🔎 Vérification conflit professeur
                                    $conflits = $em->getRepository(EmploiTps::class)
                                        ->findCoursConflitProf(
                                            $emploiTpsNew->getProf(),
                                            $emploiTpsNew->getSemaine(),
                                            $emploiTpsNew->getJour(),
                                            $emploiTpsNew->getDebut()->format('H:i'),
                                            $emploiTpsNew->getFin()->format('H:i'),
                                            $emploiTpsNew->getAnScolaire(),
                                            $emploiId ?? null // création
                                        );
                                    if (count($conflits) > 0) {

                                        $edt = $conflits[0];

                                        $this->addFlash(
                                            'danger',
                                            "<b>{$edt->getProf()->getPrenomNom()}</b> est déjà programmé "
                                            . "avec la classe <b>{$edt->getClasse()->getCode()}</b> "
                                            . "entre <b>{$edt->getDebut()->format('H:i')}</b> et "
                                            . "<b>{$edt->getFin()->format('H:i')}</b> "
                                            . "sur le site <b>{$edt->getSite()->getNom()}</b> "
                                            . "pour le cour <b>{$edt->getMatiere()->getNom()}</b>. "
                                        );

                                        return $this->redirect($request->headers->get('referer'));
                                    }

                                    $em = $this->getDoctrine()->getManager();
                                    $nbreHeure = $emploiTpsNew->getFin()->diff($emploiTpsNew->getDebut());
                                    $emploiTpsNew->setNbreHeure($nbreHeure->h);
                                    $em->persist($emploiTpsNew);
                                    $em->flush();
                                    $logs = new Logs($this->getUser(), 'Update', "Emploi du temps Id: {$emploiTpsNew->getId()}");
                                    $em->persist($logs);
                                    $em->flush();
                                    $this->addFlash('success',
                                    "L'emploi du temps de la classe <b>{$emploiTpsNew->getClasse()->getCode()}</b> "
                                    . "du <b>{$emploiTpsNew->getJour()}</b> "
                                    . "a été modifié avec succès.");
                                    
                                    return $this->redirect($request->headers->get('referer'));
                                    
                                    //return $this->redirectToRoute('emploi_du_temps_edit', ['id' => $emploiTpsNew->getId()]);
                                }
                                
                                return $this->render('emploi_du_temps/emploi_du_temps_edit.html.twig', [
                                    'titre' => 'Modifier un emploi du temps',
                                    'emploiTps' => $emploiTpsNew,
                                    'form' => $form->createView(),
                                ]);
                            }
                            
                            /**
                            * Deletes a cour entity.
                            * @Route("/supprimer/{id}", name="emploi_du_temps_delete")
                            * @Method({"GET", "POST"})
                            */
                            public function deleteAction(Request $request, EmploiTps $emploiTps)
                            {
                                
                                $em = $this->getDoctrine()->getManager();
                                $em->remove($emploiTps);
                                $logs = new Logs($this->getUser(), 'Delete', "Emploi du temps Id: {$emploiTps->getId()}");
                                $em->persist($logs);
                                $em->flush();
                                $this->addFlash('success', "Le matiere  <b>{$emploiTps->getMatiere()->getCode()}</b> "
                                . "du <b>{$emploiTps->getJour()}</b> "
                                . "a été supprimé avec succès."
                            );
                            return $this->redirect($request->headers->get('referer'));
                        }
                        
                        /**
                        * Deletes a cour entity.
                        * @Route("/supprimer/{site}/{anSco}/{classe}/{semaine}", name="emploi_du_temps_delete_all")
                        * @Method({"GET", "POST"})
                        */
                        public function deleteAllAction(Request $request, $site, $anSco, Classe $classe, $semaine)
                        {
                            $em = $this->getDoctrine()->getManager();
                            $listEmploi = $em->getRepository('AppBundle:EmploiTps')->findAllBySiteAnCLasse($classe, $anSco, $site, $semaine);
                            foreach($listEmploi as $l){
                                $em->remove($l);
                                
                            }
                            
                            $logs = new Logs($this->getUser(), 'Delete', "Emploi du temps : {$classe->getCode()}");
                            $em->persist($logs);
                            $em->flush();
                            $this->addFlash('success', "L'emploi du temps de la classe  <b>{$classe->getCode()}</b> "
                            . "a été supprimé avec succès."
                        );
                        return $this->redirect($request->headers->get('referer'));
                    }
                    
                    /**
                    * @Route("/imprimer/{id}", name="imprimer_emploi")
                    * @Method({"GET", "POST"})
                    */
                    public function ImprimerEmploi(EmploiTps $emploiTps)
                    {   
                        $em = $this->getDoctrine()->getManager();
                        $listEmploi = $em->getRepository(EmploiTps::class)->findBy(array('site' => $emploiTps->getSite(),'anScolaire'=>$emploiTps->getAnScolaire(),'classe'=>$emploiTps->getClasse(), 'semaine'=>$emploiTps->getSemaine()),['debut'=>'ASC']);
                        
                        // Group emplois by heureDebut
                        $groupedEmploi = [];
                        foreach ($listEmploi as $emploi) {
                            $heureDebut = $emploi->getDebut()->format('H:i');
                            if (!isset($groupedEmploi[$heureDebut])) {
                                $groupedEmploi[$heureDebut] = [];
                            }
                            $groupedEmploi[$heureDebut][] = $emploi;
                        }
                        // Display the size of the array
                        //die();
                        
                        return $this->render('emploi_du_temps/emploi_du_temps_imprim.html.twig', [
                            'groupedEmploi' => $groupedEmploi,
                            'anScolaire'=> $emploiTps->getAnScolaire(),
                            'classe'=> $emploiTps->getClasse(),
                            'semaine'=>$emploiTps->getLibelleSemaine(),
                            
                        ]);
                        
                    }
                    
                    /**
                    * @Route("/reconduire/{id}", name="reconduire_emploi")
                    * @Method({"GET", "POST"})
                    */
                    public function reconduire(EmploiTps $emploiTps, Request $request){
                        $em = $this->getDoctrine()->getManager();
                        
                        // Récupère le nombre de semaines à reconduire depuis le formulaire ou la requête
                        $nbSemaines = (int) $request->query->get('nbSemaines', 1); // Par défaut 1
                        // Récupère "a partir de depuis" le formulaire ou la requête
                        $aPartirDe = $request->query->get('aPartirDe'); 
                        
                        if ($nbSemaines < 1) {
                            $this->addFlash('warning', 'Le nombre de semaines à reconduire doit être supérieur à 0.');
                            return $this->redirect($request->headers->get('referer'));
                        }
                        
                        $ancienneSemaine = $emploiTps->getSemaine();
                        $annee = $emploiTps->getAnnee() ?? (int) date('Y');
                        
                        // Récupération des emplois à dupliquer pour la semaine de référence
                        $emplois = $em->getRepository(EmploiTps::class)->findBy([
                            'semaine' => $ancienneSemaine,
                            'site' => $emploiTps->getSite(),
                            'classe' => $emploiTps->getClasse(),
                            'anScolaire' => $emploiTps->getAnScolaire(),
                        ]);
                        
                        if (empty($emplois)) {
                            $this->addFlash('info', "Aucun cours trouvé à reconduire pour la semaine $ancienneSemaine.");
                            return $this->redirect($request->headers->get('referer'));
                        }

                        if($aPartirDe!==null && $aPartirDe!==""){
                            $date = new \DateTime($aPartirDe);
                        }else{

                            // Point de départ : lundi de la semaine originale
                            $date = new \DateTime();
                            $date->setISODate($annee, $ancienneSemaine);
                            var_dump($date); die;
                        }
                        
                        
                        
                        // Boucle sur le nombre de semaines à reconduire
                        for ($i = 1; $i <= $nbSemaines; $i++) {
                            if ($i==1){
                                if ($aPartirDe !== null && $aPartirDe !== ""){
                                    $date->modify('monday this week');
                                }
                                else{

                                    $date->modify('+1 week');
                                }
                            }
                            
                            // Nouvelle semaine et année ISO
                            $nouvelleSemaine = (int) $date->format('W');
                            $nouvelleAnnee = (int) $date->format('o');
                            
                            foreach ($emplois as $emploi) {
                                $clone = clone $emploi;
                                $clone->setSemaine($nouvelleSemaine);
                                $clone->setAnnee($nouvelleAnnee);
                                $em->persist($clone);
                            }
                        }
                        
                        $em->flush();
                        
                        $this->addFlash('success', "Les cours de la semaine $ancienneSemaine ont été reconduits sur $nbSemaines semaine(s).");
                        
                        return $this->redirect($request->headers->get('referer'));
                        
                    }

                    /**
                    * @Route("/emploidutemps/getEmploi/{id}", name="emploi_du_temps_get")
                    * @Method({"GET", "POST"})
                    */
                    public function getEmplois(EmploiTps $emploi): JsonResponse
                    {
                        return $this->json([
                            'id' => $emploi->getId(),
                            'prof' => $emploi->getProf()->getId(),
                            'matiere' => $emploi->getMatiere()->getId(),
                            'jour' => $emploi->getJour(),
                            'debut' => $emploi->getDebut()->format('H:i'),
                            'fin' => $emploi->getFin()->format('H:i'),
                            'exam' => $emploi->getExam(),
                        ]);
                    }
                    
                    
                    /* public function reconduire(EmploiTps $emploiTps, Request $request){
                    $em = $this->getDoctrine()->getManager();
                    $ancienneSemaine = $emploiTps->getSemaine();
                    $nouvelleSemaine = $ancienneSemaine + 1;
                    
                    if ($nouvelleSemaine > 52) {
                    $this->addFlash('warning', 'Impossible de reconduire : semaine supérieure à 52.');
                    return $this->redirect($request->headers->get('referer'));
                    }
                    // Récupération des emplois du temps de la même semaine + site + année
                    $emplois = $em->getRepository(EmploiTps::class)->findBy([
                    'semaine' => $ancienneSemaine,
                    'site' => $emploiTps->getSite(),
                    'classe' => $emploiTps->getClasse(),
                    'anScolaire' => $emploiTps->getAnScolaire(),
                    ]);
                    
                    if (empty($emplois)) {
                    $this->addFlash('info', "Aucun cours trouvé à reconduire pour la semaine $ancienneSemaine.");
                    return $this->redirect($request->headers->get('referer'));
                    }
                    
                    
                    // Clonage de tous les cours
                    foreach ($emplois as $emploi) {
                    $clone = clone $emploi;
                    $clone->setSemaine($nouvelleSemaine);
                    $em->persist($clone);
                    }
                    
                    $em->flush();
                    
                    $this->addFlash('success', "Tous les cours de la semaine $ancienneSemaine ont été reconduits vers la semaine $nouvelleSemaine.");
                    
                    return $this->redirect($request->headers->get('referer'));
                    
                    } */
                    
                }
