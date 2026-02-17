<?php

namespace AppBundle\Controller\Reports;

use AppBundle\Entity\AnScolaire;
use AppBundle\Entity\Etudiant;
use AppBundle\Entity\EtudiantClasse;
use AppBundle\Entity\MyConfig;
use AppBundle\Entity\Paiement;
use AppBundle\Entity\Site;
use AppBundle\Form\MyType\AnneeType;
use AppBundle\Form\MyType\ClasseType;
use AppBundle\Form\MyType\LettreType;
use AppBundle\Repository\ClasseRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Valid;

class ListeSoutenanceController extends Controller
{

    /**
     * @Route("/etat/liste_paie_soutenance", name="lst_paie_soutenance")
     */
    public function indexAction(Request $request)
    {
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
            ->add('classe', ClasseType::class, [
                'required' => false,
                'query_builder' => function (ClasseRepository $er) {
                return $er->createQueryBuilder('c')
                    ->where('c.code LIKE :m2')
                    ->orWhere('c.code LIKE :l3')
                    ->setParameter('m2', '%M2%')
                    ->setParameter('l3', '%L3%');
                },])
            ->add('site', EntityType::class, [
                'class' => Site::class,
                'required' => false,
                'attr' => ['class' => 'chosen-select']
            ])
            ->getForm();
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $search = $form->getData();
            $param = [
                'anScolaire' => $search['anScolaire'],
                'nature' => 'Frais de soutenance'
            ];
            $query = $em->createQueryBuilder();
            $query->select("p.montant,e.matricule, e.nom, e.prenom, ec.id, c.code as classe, p.modeOperation, p.date, e.sexe ")
                ->from(Paiement::class, 'p')
                ->leftJoin( 'p.etudiant','e')
                ->leftJoin('p.etudiantClasse', 'ec')
                ->leftJoin('ec.classe', 'c')
                ->andWhere('p.anScolaire = :anScolaire')
                ->andWhere('p.nature = :nature')
                ->orderBy('e.prenom', 'asc')
                ->orderBy('e.nom', 'asc');

                if ($search['site']){
                    $query->andWhere('p.site = :site');
                    $param['site'] = $search['site'];
                }

                if ($search['classe']){
                    $query->andWhere('ec.classe = :classe');
                    $param['classe'] = $search['classe'];
                }


            $query->setParameters($param);


/*             $query->groupBy('p.id, e.id')
                ->having('payer = e.montant'); */

            $paiement = $query->getQuery()->getResult();

            ///var_dump($paiement);

            //die;

            $vue = $this->renderView('etat/liste_paiement_soutenance.html.twig', [
                'search' => $search,
                'paiements' => $paiement,
                //                'etudiants' => $etudiants,
                'asset' => MyConfig::asset(),
            ]);

            return new Response($vue);
            /*
              $file = 'liste_etudiants_par_classe_100_' . date('Y_m_d_His') . '.pdf';
              $options = MyConfig::printOption();

              return new PdfResponse(
              $this->get('knp_snappy.pdf')->getOutputFromHtml($vue),
              $file
              );

              // */
        }

        return $this->render('etat/liste_paiement_soutenance_form.html.twig', [
            'titre' => 'Etat - Liste des paiements des frais de soutenance',
            'form' => $form->createView(),
        ]);
    }

}
